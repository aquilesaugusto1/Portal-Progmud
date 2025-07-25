<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class ColaboradorController extends Controller
{
    public function index()
    {
        $colaboradores = User::latest()->paginate(10);
        return view('colaboradores.index', compact('colaboradores'));
    }

    public function create()
    {
        $gestores = User::whereIn('funcao', ['admin', 'techlead', 'coordenador_operacoes', 'coordenador_tecnico'])->where('status', 'Ativo')->get();
        return view('colaboradores.create', compact('gestores'));
    }

    public function store(Request $request)
    {
        $request->validate($this->getValidationRules());

        DB::transaction(function () use ($request) {
            User::create($this->getData($request));
        });

        return redirect()->route('colaboradores.index')->with('success', 'Colaborador criado com sucesso.');
    }

    public function show(User $colaborador)
    {
        return view('colaboradores.show', compact('colaborador'));
    }

    public function edit(User $colaborador)
    {
        $gestores = User::whereIn('funcao', ['admin', 'techlead', 'coordenador_operacoes', 'coordenador_tecnico'])->where('status', 'Ativo')->where('id', '!=', $colaborador->id)->get();
        return view('colaboradores.edit', compact('colaborador', 'gestores'));
    }

    public function update(Request $request, User $colaborador)
    {
        $request->validate($this->getValidationRules($colaborador->id));

        DB::transaction(function () use ($request, $colaborador) {
            $colaborador->update($this->getData($request, false));
        });

        return redirect()->route('colaboradores.index')->with('success', 'Colaborador atualizado com sucesso.');
    }

    public function toggleStatus(User $colaborador)
    {
        $novoStatus = $colaborador->status === 'Ativo' ? 'Inativo' : 'Ativo';
        $colaborador->update(['status' => $novoStatus]);
        $mensagem = $novoStatus === 'Ativo' ? 'Colaborador habilitado com sucesso.' : 'Colaborador desabilitado com sucesso.';
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
            'subordinado_a' => ['nullable', 'exists:usuarios,id'],
            'dados_empresa_prestador.razao_social' => ['nullable', 'string', 'max:255'],
            'dados_empresa_prestador.cnpj' => ['nullable', 'string', 'max:255'],
            'dados_bancarios.banco' => ['nullable', 'string', 'max:255'],
            'dados_bancarios.agencia' => ['nullable', 'string', 'max:255'],
            'dados_bancarios.conta' => ['nullable', 'string', 'max:255'],
        ];

        if (!$id || request()->filled('password')) {
            $rules['password'] = ['required', 'confirmed', Rules\Password::defaults()];
        }

        return $rules;
    }

    private function getData(Request $request)
    {
        $data = $request->except(['_token', '_method', 'password_confirmation']);
        
        $perfilSelecionado = $request->input('funcao');

        $data['funcao'] = match ($perfilSelecionado) {
            'administrativo' => 'admin',
            'consultor' => 'consultor',
            'techlead' => 'techlead',
            default => 'admin',
        };

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        } else {
            unset($data['password']);
        }

        $data['dados_empresa_prestador'] = in_array($request->tipo_contrato, ['PJ Mensal', 'PJ Horista']) ? $request->dados_empresa_prestador : null;
        
        return $data;
    }
}