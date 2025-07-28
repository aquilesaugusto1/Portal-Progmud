<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class ColaboradorController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $query = User::query();
        if ($request->filled('nome')) {
            $query->where('nome', 'like', '%' . $request->nome . '%');
        }
        if ($request->filled('funcao')) {
            $query->where('funcao', $request->funcao);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        $colaboradores = $query->latest()->paginate(10)->withQueryString();
        return view('colaboradores.index', compact('colaboradores'));
    }

    public function create()
    {
        $this->authorize('create', User::class);
        $techLeads = User::where('funcao', 'techlead')->where('status', 'Ativo')->orderBy('nome')->get();
        return view('colaboradores.create', compact('techLeads'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', User::class);
        $request->validate($this->getValidationRules());
        DB::transaction(function () use ($request) {
            $colaborador = User::create($this->getData($request));
            if ($request->has('tech_leads')) {
                $colaborador->techLeads()->sync($request->input('tech_leads'));
            }
        });
        return redirect()->route('colaboradores.index')->with('success', 'Colaborador criado com sucesso.');
    }

    public function show(User $colaborador)
    {
        $this->authorize('view', $colaborador);
        return view('colaboradores.show', compact('colaborador'));
    }

    public function edit(User $colaborador)
    {
        $this->authorize('update', $colaborador);
        $techLeads = User::where('funcao', 'techlead')->where('status', 'Ativo')->orderBy('nome')->get();
        return view('colaboradores.edit', compact('colaborador', 'techLeads'));
    }

    public function update(Request $request, User $colaborador)
    {
        $this->authorize('update', $colaborador);
        $request->validate($this->getValidationRules($colaborador->id));
        DB::transaction(function () use ($request, $colaborador) {
            $colaborador->update($this->getData($request, false));
            $techLeads = $request->input('tech_leads', []);
            $colaborador->techLeads()->sync($techLeads);
        });
        return redirect()->route('colaboradores.index')->with('success', 'Colaborador atualizado com sucesso.');
    }

    public function toggleStatus(User $colaborador)
    {
        $this->authorize('toggleStatus', $colaborador);
        $novoStatus = $colaborador->status === 'Ativo' ? 'Inativo' : 'Ativo';
        $colaborador->update(['status' => $novoStatus]);
        $mensagem = $novoStatus === 'Ativo' ? 'Colaborador habilitado.' : 'Colaborador desabilitado.';
        return redirect()->route('colaboradores.index')->with('success', $mensagem);
    }

    private function getValidationRules($id = null)
    {
        $rules = [
            'nome' => ['required', 'string', 'max:255'],
            'sobrenome' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:usuarios,email,' . $id],
            'funcao' => ['required', 'string', 'in:consultor,techlead,administrativo,coordenador_operacoes,coordenador_tecnico,comercial'],
            'tipo_contrato' => ['nullable', 'string'],
            'data_nascimento' => ['nullable', 'date'],
            'nacionalidade' => ['nullable', 'string', 'max:255'],
            'naturalidade' => ['nullable', 'string', 'max:255'],
            'endereco.rua' => ['nullable', 'string', 'max:255'],
            'endereco.bairro' => ['nullable', 'string', 'max:255'],
            'endereco.cidade' => ['nullable', 'string', 'max:255'],
            'endereco.estado' => ['nullable', 'string', 'max:255'],
            'endereco.pais' => ['nullable', 'string', 'max:255'],
            'email_totvs_partner' => ['nullable', 'string', 'lowercase', 'email', 'max:255'],
            'status' => ['required', 'string'],
            'cargo' => ['nullable', 'string', 'max:255'],
            'nivel' => ['nullable', 'string'],
            'tech_leads' => ['nullable', 'array'],
            'tech_leads.*' => ['exists:usuarios,id'],
            'dados_empresa_prestador.razao_social' => ['nullable', 'string', 'max:255'],
            'dados_empresa_prestador.cnpj' => ['nullable', 'string', 'max:255'],
            'dados_bancarios.banco' => ['nullable', 'string', 'max:255'],
            'dados_bancarios.agencia' => ['nullable', 'string', 'max:255'],
            'dados_bancarios.conta' => ['nullable', 'string', 'max:255'],
        ];
        if (!$id || $request->filled('password')) {
            $rules['password'] = ['required', 'confirmed', Rules\Password::defaults()];
        }
        return $rules;
    }

    private function getData(Request $request, $isCreate = true)
    {
        $data = $request->except(['_token', '_method', 'password_confirmation', 'tech_leads']);
        
        if ($isCreate) {
            $data['password'] = Hash::make($request->password);
        } elseif ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        } else {
            unset($data['password']);
        }

        $data['dados_empresa_prestador'] = in_array($request->tipo_contrato, ['PJ Mensal', 'PJ Horista']) ? $request->dados_empresa_prestador : null;
        return $data;
    }
}
