<?php

namespace App\Http\Controllers;

use App\Models\Agenda;
use App\Models\Consultor;
use App\Models\Projeto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class AgendaController extends Controller
{
    use AuthorizesRequests;

    public function alocacao(Request $request)
    {
        $this->authorize('viewAlocacao', Agenda::class);

        $mes = $request->input('mes', date('m'));
        $ano = $request->input('ano', date('Y'));

        $consultores = Consultor::with([
                'projetos.empresaParceira', 
                'projetos.techLeads', 
                'apontamentos' => function($query) use ($mes, $ano) {
                    $query->whereYear('data_apontamento', $ano)->whereMonth('data_apontamento', $mes);
                }
            ])
            ->where('status', 'Ativo')
            ->orderBy('nome')
            ->get();

        return view('agendas.alocacao', compact('consultores', 'mes', 'ano'));
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Agenda::class);

        $user = Auth::user();
        $search = $request->input('search');

        $agendasQuery = Agenda::with('consultor.techLeads', 'projeto.empresaParceira')->latest();

        if ($user->funcao === 'consultor') {
            $agendasQuery->where('consultor_id', $user->consultor->id);
        }

        if ($search) {
            $agendasQuery->where(function ($query) use ($search) {
                $query->where('assunto', 'like', "%{$search}%")
                    ->orWhereHas('consultor', function ($subQuery) use ($search) {
                        $subQuery->where('nome', 'like', "%{$search}%");
                    })
                    ->orWhereHas('projeto', function ($subQuery) use ($search) {
                        $subQuery->where('nome_projeto', 'like', "%{$search}%");
                    })
                    ->orWhereHas('projeto.empresaParceira', function ($subQuery) use ($search) {
                        $subQuery->where('nome_empresa', 'like', "%{$search}%");
                    });
            });
        }
        
        $agendas = $agendasQuery->paginate(10)->withQueryString();

        return view('agendas.index', compact('agendas', 'search'));
    }

    public function create()
    {
        $this->authorize('create', Agenda::class);
        $projetos = Projeto::with('empresaParceira')->orderBy('nome_projeto')->get();
        
        $user = Auth::user();
        $consultores = ($user->funcao === 'admin') 
            ? Consultor::where('status', 'Ativo')->orderBy('nome')->get()
            : $user->consultoresLiderados()->where('status', 'Ativo')->orderBy('nome')->get();

        return view('agendas.create', compact('projetos', 'consultores'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Agenda::class);

        $validated = $request->validate([
            'data_hora' => 'required|date',
            'assunto' => 'required|string|max:255',
            'status' => 'required|string|in:Agendada,Realizada,Cancelada',
            'consultor_id' => 'required|exists:consultores,id',
            'projeto_id' => 'required|exists:projetos,id',
        ]);
        
        $user = Auth::user();
        if ($user->funcao === 'techlead' && !$user->consultoresLiderados()->where('consultores.id', $validated['consultor_id'])->exists()) {
            return back()->withErrors(['consultor_id' => 'Você não tem permissão para criar agendas para este consultor.'])->withInput();
        }

        Agenda::create($validated);

        return redirect()->route('agendas.index')->with('success', 'Agenda criada com sucesso.');
    }

    public function edit(Agenda $agenda)
    {
        $this->authorize('update', $agenda);
        
        $projetos = Projeto::with('empresaParceira')->orderBy('nome_projeto')->get();
        $user = Auth::user();
        $consultores = ($user->funcao === 'admin') 
            ? Consultor::where('status', 'Ativo')->orderBy('nome')->get()
            : $user->consultoresLiderados()->where('status', 'Ativo')->orderBy('nome')->get();

        return view('agendas.edit', compact('agenda', 'projetos', 'consultores'));
    }

    public function update(Request $request, Agenda $agenda)
    {
        $this->authorize('update', $agenda);

        $validated = $request->validate([
            'data_hora' => 'required|date',
            'assunto' => 'required|string|max:255',
            'status' => 'required|string|in:Agendada,Realizada,Cancelada',
            'consultor_id' => 'required|exists:consultores,id',
            'projeto_id' => 'required|exists:projetos,id',
        ]);

        $user = Auth::user();
        if ($user->funcao === 'techlead' && !$user->consultoresLiderados()->where('consultores.id', $validated['consultor_id'])->exists()) {
            return back()->withErrors(['consultor_id' => 'Você não tem permissão para atribuir agendas a este consultor.'])->withInput();
        }

        $agenda->update($validated);

        return redirect()->route('agendas.index')->with('success', 'Agenda atualizada com sucesso.');
    }

    public function destroy(Agenda $agenda)
    {
        $this->authorize('delete', $agenda);
        $agenda->delete();
        return redirect()->route('agendas.index')->with('success', 'Agenda removida com sucesso.');
    }
}
