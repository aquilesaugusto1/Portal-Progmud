<?php

namespace App\Http\Controllers;

use App\Models\Agenda;
use App\Models\Contrato;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AgendaController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Agenda::class);

        $user = Auth::user();
        $query = Agenda::with(['consultor', 'contrato.empresaParceira']);

        if ($user->isTechLead()) {
            $consultoresLideradosIds = $user->consultoresLiderados()->pluck('usuarios.id');
            $query->whereIn('consultor_id', $consultoresLideradosIds);
        } elseif ($user->isConsultor()) {
            $query->where('consultor_id', $user->id);
        }

        if ($request->filled('consultor_id')) {
            $query->where('consultor_id', $request->consultor_id);
        }
        if ($request->filled('contrato_id')) {
            $query->where('contrato_id', $request->contrato_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $calendarQuery = clone $query;

        $agendas = $query->latest('data_hora')->paginate(15)->withQueryString();

        $eventosDoCalendario = $this->formatarParaCalendario($calendarQuery->get());

        $consultores = User::where('status', 'Ativo')->where('funcao', 'consultor')->orderBy('nome')->get();
        $contratos = Contrato::with('empresaParceira')->where('status', 'Ativo')->orderBy('numero_contrato')->get();

        return view('agendas.index', compact('agendas', 'eventosDoCalendario', 'consultores', 'contratos'));
    }

    private function formatarParaCalendario($agendas)
    {
        return $agendas->map(function (Agenda $agenda) {
            $color = '#3B82F6';
            switch ($agenda->status) {
                case 'Realizada':
                    $color = '#10B981';
                    break;
                case 'Cancelada':
                    $color = '#EF4444';
                    break;
            }

            return [
                'id' => $agenda->id,
                'title' => $agenda->consultor->nome ?? 'Consultor N/A',
                'start' => $agenda->data_hora->toIso8601String(),
                'color' => $color,
                'extendedProps' => [
                    'assunto' => $agenda->assunto,
                    'consultor' => $agenda->consultor->nome ?? 'N/A',
                    'cliente' => $agenda->contrato->empresaParceira->nome_empresa ?? 'N/A',
                    'url' => route('agendas.show', $agenda),
                ],
            ];
        });
    }

    public function create()
    {
        $this->authorize('create', Agenda::class);
        $contratos = Contrato::with('empresaParceira')->where('status', 'Ativo')->orderBy('numero_contrato')->get();
        $consultores = collect();

        return view('agendas.create', compact('consultores', 'contratos'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Agenda::class);
        $validated = $request->validate([
            'consultor_id' => 'required|exists:usuarios,id',
            'contrato_id' => 'required|exists:contratos,id',
            'assunto' => 'required|string|max:255',
            'data_hora' => 'required|date',
            'descricao' => 'nullable|string',
            'status' => 'required|string|in:Agendada,Realizada,Cancelada',
        ]);
        $user = Auth::user();
        if ($user->isTechLead() && ! $user->consultoresLiderados()->where('usuarios.id', $validated['consultor_id'])->exists()) {
            return back()->withErrors(['consultor_id' => 'Você só pode criar agendas para consultores que você lidera.'])->withInput();
        }
        Agenda::create($validated);

        return redirect()->route('agendas.index')->with('success', 'Agenda criada com sucesso.');
    }

    public function show(Agenda $agenda)
    {
        $this->authorize('view', $agenda);

        return view('agendas.show', compact('agenda'));
    }

    private function getFilteredConsultantsForContract(?Contrato $contrato, User $user)
    {
        if (! $contrato) {
            return collect();
        }

        $query = User::where('funcao', 'consultor')->where('status', 'Ativo');

        $query->whereHas('contratos', function ($q) use ($contrato) {
            $q->where('contratos.id', $contrato->id);
        });

        if ($user->isTechLead()) {
            $lideradosIds = $user->consultoresLiderados()->pluck('usuarios.id');

            if ($lideradosIds->isEmpty()) {
                return collect();
            }
            $query->whereIn('usuarios.id', $lideradosIds);
        }

        return $query->orderBy('nome')->get();
    }

    public function edit(Agenda $agenda)
    {
        $this->authorize('update', $agenda);
        $contratos = Contrato::with('empresaParceira')->where('status', 'Ativo')->get();
        $consultores = $this->getFilteredConsultantsForContract($agenda->contrato, Auth::user());

        return view('agendas.edit', compact('agenda', 'consultores', 'contratos'));
    }

    public function update(Request $request, Agenda $agenda)
    {
        $this->authorize('update', $agenda);
        $validated = $request->validate([
            'consultor_id' => 'required|exists:usuarios,id',
            'contrato_id' => 'required|exists:contratos,id',
            'assunto' => 'required|string|max:255',
            'data_hora' => 'required|date',
            'descricao' => 'nullable|string',
            'status' => 'required|string|in:Agendada,Realizada,Cancelada',
        ]);
        $user = Auth::user();
        if ($user->isTechLead() && ! $user->consultoresLiderados()->where('usuarios.id', $validated['consultor_id'])->exists()) {
            return back()->withErrors(['consultor_id' => 'Você só pode atribuir agendas a este consultor.'])->withInput();
        }
        $agenda->update($validated);

        return redirect()->route('agendas.index')->with('success', 'Agenda atualizada com sucesso.');
    }

    public function destroy(Agenda $agenda)
    {
        $this->authorize('delete', $agenda);
        $agenda->delete();

        return redirect()->route('agendas.index')->with('success', 'Agenda excluída com sucesso.');
    }

    public function getConsultoresPorContrato($contratoId)
    {
        try {
            $this->authorize('create', Agenda::class);
            $contrato = Contrato::find($contratoId);
            $consultores = $this->getFilteredConsultantsForContract($contrato, Auth::user());

            return response()->json($consultores->map->only(['id', 'nome', 'sobrenome']));
        } catch (\Exception $e) {
            Log::error('Erro ao buscar consultores para o contrato.', [
                'contrato_id' => $contratoId,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json(['message' => 'Ocorreu um erro no servidor.'], 500);
        }
    }
}
