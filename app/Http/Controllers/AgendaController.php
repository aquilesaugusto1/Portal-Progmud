<?php

namespace App\Http\Controllers;

use App\Models\Agenda;
use App\Models\User;
use App\Models\Projeto;
use Illuminate\Http\Request;

class AgendaController extends Controller
{
    public function index()
    {
        $agendas = Agenda::with(['consultor', 'projeto.empresaParceira'])->latest()->paginate(10);
        return view('agendas.index', compact('agendas'));
    }

    public function create()
    {
        $consultores = User::where('funcao', 'consultor')->where('status', 'Ativo')->get();
        $projetos = Projeto::with('empresaParceira')->get();
        return view('agendas.create', compact('consultores', 'projetos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'consultor_id' => 'required|exists:usuarios,id',
            'projeto_id' => 'required|exists:projetos,id',
            'data_hora' => 'required|date',
            'assunto' => 'required|string|max:255',
            'status' => 'required|string|in:Agendada,Realizada,Cancelada',
        ]);

        Agenda::create($request->all());

        return redirect()->route('agendas.index')->with('success', 'Agenda criada com sucesso.');
    }

    public function show(Agenda $agenda)
    {
        return view('agendas.show', compact('agenda'));
    }

    public function edit(Agenda $agenda)
    {
        $consultores = User::where('funcao', 'consultor')->where('status', 'Ativo')->get();
        $projetos = Projeto::with('empresaParceira')->get();
        return view('agendas.edit', compact('agenda', 'consultores', 'projetos'));
    }

    public function update(Request $request, Agenda $agenda)
    {
        $request->validate([
            'consultor_id' => 'required|exists:usuarios,id',
            'projeto_id' => 'required|exists:projetos,id',
            'data_hora' => 'required|date',
            'assunto' => 'required|string|max:255',
            'status' => 'required|string|in:Agendada,Realizada,Cancelada',
        ]);

        $agenda->update($request->all());

        return redirect()->route('agendas.index')->with('success', 'Agenda atualizada com sucesso.');
    }

    public function destroy(Agenda $agenda)
    {
        $agenda->delete();
        return redirect()->route('agendas.index')->with('success', 'Agenda removida com sucesso.');
    }
}