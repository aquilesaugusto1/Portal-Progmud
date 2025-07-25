<?php

namespace App\Http\Controllers;

use App\Models\Projeto;
use App\Models\EmpresaParceira;
use App\Models\Consultor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjetoController extends Controller
{
    public function index()
    {
        $projetos = Projeto::with('empresaParceira')->latest()->paginate(10);
        return view('projetos.index', compact('projetos'));
    }

    public function create()
    {
        $empresas = EmpresaParceira::all();
        $consultores = Consultor::where('status', 'Ativo')->get();
        $techLeads = User::where('funcao', 'techlead')->get();
        return view('projetos.create', compact('empresas', 'consultores', 'techLeads'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome_projeto' => 'required|string|max:255',
            'empresa_parceira_id' => 'required|exists:empresas_parceiras,id',
            'tipo' => 'required|in:ams,act,act+',
            'consultores' => 'nullable|array',
            'consultores.*' => 'exists:consultores,id',
            'tech_leads' => 'nullable|array',
            'tech_leads.*' => 'exists:usuarios,id',
        ]);
        
        if ($validated['tipo'] === 'act+' && empty($request->tech_leads)) {
            return back()->withErrors(['tech_leads' => 'Para projetos do tipo ACT+, é obrigatório associar pelo menos um Tech Lead.'])->withInput();
        }

        DB::transaction(function () use ($request, $validated) {
            $projeto = Projeto::create($validated);
            $projeto->consultores()->sync($request->input('consultores', []));

            if ($validated['tipo'] === 'act+') {
                $projeto->techLeads()->sync($request->input('tech_leads', []));
            }
        });

        return redirect()->route('projetos.index')->with('success', 'Projeto criado com sucesso.');
    }

    public function show(Projeto $projeto)
    {
        $projeto->load('empresaParceira', 'consultores', 'techLeads');
        return view('projetos.show', compact('projeto'));
    }

    public function edit(Projeto $projeto)
    {
        $empresas = EmpresaParceira::all();
        $consultores = Consultor::where('status', 'Ativo')->get();
        $techLeads = User::where('funcao', 'techlead')->get();
        $projeto->load('consultores', 'techLeads');
        return view('projetos.edit', compact('projeto', 'empresas', 'consultores', 'techLeads'));
    }

    public function update(Request $request, Projeto $projeto)
    {
        $validated = $request->validate([
            'nome_projeto' => 'required|string|max:255',
            'empresa_parceira_id' => 'required|exists:empresas_parceiras,id',
            'tipo' => 'required|in:ams,act,act+',
            'consultores' => 'nullable|array',
            'consultores.*' => 'exists:consultores,id',
            'tech_leads' => 'nullable|array',
            'tech_leads.*' => 'exists:usuarios,id',
        ]);
        
        if ($validated['tipo'] === 'act+' && empty($request->tech_leads)) {
            return back()->withErrors(['tech_leads' => 'Para projetos do tipo ACT+, é obrigatório associar pelo menos um Tech Lead.'])->withInput();
        }

        DB::transaction(function () use ($request, $projeto, $validated) {
            $projeto->update($validated);
            $projeto->consultores()->sync($request->input('consultores', []));
            
            if ($validated['tipo'] === 'act+') {
                $projeto->techLeads()->sync($request->input('tech_leads', []));
            } else {
                $projeto->techLeads()->detach();
            }
        });

        return redirect()->route('projetos.index')->with('success', 'Projeto atualizado com sucesso.');
    }

    public function destroy(Projeto $projeto)
    {
        DB::transaction(function () use ($projeto) {
            $projeto->consultores()->detach();
            $projeto->techLeads()->detach();
            $projeto->delete();
        });

        return redirect()->route('projetos.index')->with('success', 'Projeto removido com sucesso.');
    }
}
