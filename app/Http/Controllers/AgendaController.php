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
        $query = Agenda::with(['consultor', 'contrato.cliente']);

        if ($user->funcao === 'techlead') {
            $consultoresLideradosIds = $user->consultoresLiderados()->pluck('id');
            $query->whereIn('consultor_id', $consultoresLideradosIds);
        } elseif ($user->funcao === 'consultor') {
            $query->where('consultor_id', $user->id);
        }

        $agendas = $query->latest('data_hora')->paginate(15)->withQueryString();

        return view('agendas.index', compact('agendas'));
    }

    public function create()
    {
        $this->authorize('create', Agenda::class);

        $contratos = Contrato::where('status', 'Ativo')->with('cliente')->orderBy('numero_contrato')->get();
        $consultores = collect(); // Será carregado dinamicamente

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
        if ($user->funcao === 'techlead' && !$user->consultoresLiderados()->where('id', $validated['consultor_id'])->exists()) {
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

    public function edit(Agenda $agenda)
    {
        $this->authorize('update', $agenda);
        
        $contratos = Contrato::where('status', 'Ativo')->with('cliente')->get();
        $consultores = collect();
        if ($agenda->contrato) {
            $query = $agenda->contrato->consultores();
            $user = Auth::user();
            if ($user->funcao === 'techlead') {
                $lideradosIds = $user->consultoresLiderados()->pluck('id');
                $query->whereIn('usuarios.id', $lideradosIds);
            }
            $consultores = $query->orderBy('nome')->get();
        }

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
        if ($user->funcao === 'techlead' && !$user->consultoresLiderados()->where('id', $validated['consultor_id'])->exists()) {
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

    /**
     * API endpoint to get consultants for a given contract, respecting user roles.
     */
    public function getConsultoresPorContrato($contratoId)
    {
        try {
            $this->authorize('create', Agenda::class);

            $contrato = Contrato::find($contratoId);
            if (!$contrato) {
                return response()->json([], 404);
            }

            $user = Auth::user();
            $query = $contrato->consultores()->where('status', 'Ativo');

            if ($user->funcao === 'techlead') {
                $lideradosIds = $user->consultoresLiderados()->pluck('id');
                $query->whereIn('usuarios.id', $lideradosIds);
            }
            
            $consultores = $query->orderBy('nome')->get(['usuarios.id', 'nome', 'sobrenome']);
            
            return response()->json($consultores);

        } catch (\Exception $e) {
            Log::error('Erro ao buscar consultores para o contrato.', [
                'contrato_id' => $contratoId,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            return response()->json(['message' => 'Ocorreu um erro no servidor.'], 500);
        }
    }
}
