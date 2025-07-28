<?php

namespace App\Http\Controllers;

use App\Models\Agenda;
use App\Models\Contrato;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class AgendaController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        $this->authorize('viewAny', Agenda::class);

        if ($user->funcao === 'techlead') {
            $consultoresLideradosIds = $user->consultoresLiderados()->pluck('id');
            $agendas = Agenda::whereIn('consultor_id', $consultoresLideradosIds)
                ->with(['consultor', 'contrato.cliente'])
                ->latest()
                ->paginate(15);
        } else {
             $agendas = Agenda::with(['consultor', 'contrato.cliente'])->latest()->paginate(15);
        }
        
        return view('agendas.index', compact('agendas'));
    }

    public function create()
    {
        $this->authorize('create', Agenda::class);

        $consultores = User::where('funcao', 'consultor')->where('status', 'Ativo')->orderBy('nome')->get();
        $contratos = Contrato::where('status', 'Ativo')->with('cliente')->get();
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

        $consultores = User::where('funcao', 'consultor')->where('status', 'Ativo')->orderBy('nome')->get();
        $contratos = Contrato::where('status', 'Ativo')->with('cliente')->get();
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

        $agenda->update($validated);

        return redirect()->route('agendas.index')->with('success', 'Agenda atualizada com sucesso.');
    }
}