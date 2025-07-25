<?php

namespace App\Http\Controllers;

use App\Models\Consultor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class ConsultorController extends Controller
{
    public function index()
    {
        $consultores = Consultor::with('usuario')->latest()->paginate(10);
        return view('consultores.index', compact('consultores'));
    }

    public function create()
    {
        $techLeads = User::where('funcao', 'techlead')->get();
        return view('consultores.create', compact('techLeads'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'telefone' => ['nullable', 'string', 'max:20'],
            'tech_leads' => ['nullable', 'array'],
            'tech_leads.*' => ['exists:usuarios,id'],
        ]);

        DB::transaction(function () use ($request) {
            $user = User::create([
                'nome' => $request->nome,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'funcao' => 'consultor',
            ]);

            $consultor = new Consultor([
                'nome' => $request->nome,
                'email' => $request->email,
                'telefone' => $request->telefone,
                'status' => 'Ativo',
            ]);
            
            $consultor->usuario_id = $user->id;
            $consultor->save();

            if ($request->has('tech_leads')) {
                $consultor->techLeads()->sync($request->tech_leads);
            }
        });

        return redirect()->route('consultores.index')
                         ->with('success', 'Consultor criado com sucesso.');
    }

    public function show(Consultor $consultor)
    {
        $consultor->load('usuario', 'techLeads');
        return view('consultores.show', compact('consultor'));
    }

    public function edit(Consultor $consultor)
    {
        $techLeads = User::where('funcao', 'techlead')->get();
        $consultor->load('techLeads');
        return view('consultores.edit', compact('consultor', 'techLeads'));
    }

    public function update(Request $request, Consultor $consultor)
    {
        $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:usuarios,email,' . $consultor->usuario_id],
            'telefone' => ['nullable', 'string', 'max:20'],
            'status' => ['required', 'string', 'in:Ativo,Inativo'],
            'tech_leads' => ['nullable', 'array'],
            'tech_leads.*' => ['exists:usuarios,id'],
        ]);

        DB::transaction(function () use ($request, $consultor) {
            $consultor->usuario->update([
                'nome' => $request->nome,
                'email' => $request->email,
            ]);

            $consultor->update([
                'nome' => $request->nome,
                'email' => $request->email,
                'telefone' => $request->telefone,
                'status' => $request->status,
            ]);

            $consultor->techLeads()->sync($request->input('tech_leads', []));
        });
        
        return redirect()->route('consultores.index')
                         ->with('success', 'Consultor atualizado com sucesso.');
    }

    public function destroy(Consultor $consultor)
    {
        DB::transaction(function () use ($consultor) {
            $user = $consultor->usuario;
            $consultor->delete();
            $user->delete();
        });

        return redirect()->route('consultores.index')
                         ->with('success', 'Consultor removido com sucesso.');
    }
}
