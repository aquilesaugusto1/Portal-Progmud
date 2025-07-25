<?php

namespace App\Http\Controllers;

use App\Models\Agenda;
use App\Models\Consultor;
use App\Mail\AgendaMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class EmailController extends Controller
{
    public function create()
    {
        $user = Auth::user();

        if ($user->funcao === 'admin') {
            $consultores = Consultor::where('status', 'Ativo')->orderBy('nome')->get();
        } else { // techlead
            $consultores = $user->consultoresLiderados()->where('status', 'Ativo')->orderBy('nome')->get();
        }

        return view('emails.enviar-agendas', compact('consultores'));
    }

    public function send(Request $request)
    {
        $validated = $request->validate([
            'consultor_id' => 'required|exists:consultores,id',
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date|after_or_equal:data_inicio',
            'recado' => 'nullable|string|max:2000',
        ]);

        $user = Auth::user();
        if ($user->funcao === 'techlead' && !$user->consultoresLiderados()->where('consultores.id', $validated['consultor_id'])->exists()) {
            return back()->withErrors(['consultor_id' => 'Você não tem permissão para enviar agendas para este consultor.'])->withInput();
        }

        $consultor = Consultor::findOrFail($validated['consultor_id']);
        
        $agendas = Agenda::with('projeto.empresaParceira')
            ->where('consultor_id', $consultor->id)
            ->whereBetween('data_hora', [$validated['data_inicio'], $validated['data_fim']])
            ->orderBy('data_hora')
            ->get();
            
        if ($agendas->isEmpty()) {
            return back()->withErrors(['geral' => 'Nenhuma agenda encontrada para este consultor no período selecionado.'])->withInput();
        }

        $recado = $validated['recado'] ?? 'Segue a sua agenda para o período selecionado.';

        try {
            Mail::to($consultor->email)->send(new AgendaMail($agendas, $recado, $user));
        } catch (\Exception $e) {
            return back()->withErrors(['geral' => 'Ocorreu um erro ao tentar enviar o email. Verifique a configuração e tente novamente.'])->withInput();
        }
        
        return redirect()->route('email.agendas.create')->with('success', 'Email com as agendas enviado com sucesso para ' . $consultor->nome . '!');
    }
}
