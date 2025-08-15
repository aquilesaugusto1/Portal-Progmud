<?php

namespace App\Http\Controllers;

use App\Mail\AgendaMail;
use App\Models\Agenda;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use LogicException;

class EmailController extends Controller
{
    public function create(): View
    {
        $user = Auth::user();
        if (! $user) {
            throw new LogicException('User not authenticated.');
        }

        $query = User::where('funcao', 'consultor')->where('status', 'Ativo');

        if ($user->isTechLead()) {
            $consultoresLideradosIds = $user->consultoresLiderados()->pluck('id');
            $query->whereIn('id', $consultoresLideradosIds);
        }

        $consultores = $query->orderBy('nome')->get();

        return view('emails.enviar-agendas', compact('consultores'));
    }

    public function send(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if (! $user) {
            throw new LogicException('User not authenticated.');
        }

        $validated = $request->validate([
            'consultor_id' => [
                'required',
                'integer',
                Rule::exists('usuarios', 'id')->where(function ($query) {
                    $query->where('funcao', 'consultor');
                }),
            ],
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date|after_or_equal:data_inicio',
            'recado' => 'nullable|string|max:2000',
        ]);

        $consultor = User::findOrFail($validated['consultor_id']);

        if ($user->isTechLead() && ! $user->consultoresLiderados()->where('usuarios.id', $consultor->id)->exists()) {
            return back()->withErrors(['consultor_id' => 'Você não tem permissão para enviar agendas para este consultor.'])->withInput();
        }

        $agendas = Agenda::with('contrato.empresaParceira')
            ->where('consultor_id', $consultor->id)
            ->whereBetween('data_hora', [$validated['data_inicio'], $validated['data_fim']])
            ->orderBy('data_hora')
            ->get();

        if ($agendas->isEmpty()) {
            return back()->with('error', 'Nenhuma agenda encontrada para este consultor no período selecionado.')->withInput();
        }

        $recado = $validated['recado'] ?? 'Segue a sua agenda para o período selecionado.';

        try {
            Mail::to($consultor->email)->send(new AgendaMail($agendas, $recado, $user));
        } catch (\Exception $e) {
            report($e);

            return back()->with('error', 'Ocorreu um erro ao tentar enviar o e-mail. Verifique se as credenciais de e-mail estão corretas e tente novamente.')->withInput();
        }

        return redirect()->route('email.agendas.create')->with('success', 'E-mail com as agendas enviado com sucesso para '.$consultor->nome.'!');
    }
}
