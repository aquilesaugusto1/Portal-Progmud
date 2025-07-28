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

        $agendas = $query->latest()->paginate(15)->withQueryString();

        return view('agendas.index', compact('agendas'));
    }

    public function create()
    {
        $this->authorize('create', Agenda::class);

        $contratos = Contrato::where('status', 'Ativo')->with('cliente')->orderBy('numero_contrato')->get();
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
            'inicio_previsto' => 'required|date',
            'fim_previsto' => 'required|date|after_or_equal:inicio_previsto',
            'descricao' => 'nullable|string',
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
        $consultores = $agenda->contrato ? $agenda->contrato->consultores()->orderBy('nome')->get() : collect();

        return view('agendas.edit', compact('agenda', 'consultores', 'contratos'));
    }

    public function update(Request $request, Agenda $agenda)
    {
        $this->authorize('update', $agenda);

        $validated = $request->validate([
            'consultor_id' => 'required|exists:usuarios,id',
            'contrato_id' => 'required|exists:contratos,id',
            'assunto' => 'required|string|max:255',
            'inicio_previsto' => 'required|date',
            'fim_previsto' => 'required|date|after_or_equal:inicio_previsto',
            'status' => 'required|string',
            'descricao' => 'nullable|string',
        ]);

        $user = Auth::user();
        if ($user->funcao === 'techlead' && !$user->consultoresLiderados()->where('id', $validated['consultor_id'])->exists()) {
             return back()->withErrors(['consultor_id' => 'Você só pode atribuir agendas para consultores que você lidera.'])->withInput();
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
     * API endpoint to get consultants for a given contract.
     */
    public function getConsultoresPorContrato(Contrato $contrato)
    {
        try {
            // Apenas usuários que podem criar agendas podem usar esta API
            $this->authorize('create', Agenda::class);

            $consultores = $contrato->consultores()
                                    ->where('status', 'Ativo')
                                    ->orderBy('nome')
                                    ->get(['id', 'nome', 'sobrenome']);
            
            return response()->json($consultores);

        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json(['message' => 'Você não tem permissão para ver estes consultores.'], 403);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar consultores para o contrato ' . $contrato->id . ': ' . $e->getMessage());
            return response()->json(['message' => 'Ocorreu um erro no servidor ao buscar consultores.'], 500);
        }
    }
}
