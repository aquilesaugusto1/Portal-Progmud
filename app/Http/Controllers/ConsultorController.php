<?php

namespace App\Http\Controllers;

use App\Models\Consultor;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class ConsultorController extends Controller
{
    public function index(): View
    {
        $consultores = Consultor::with('usuario')->latest()->paginate(10);

        return view('consultores.index', compact('consultores'));
    }

    public function create(): View
    {
        $techLeads = User::where('funcao', 'techlead')->get();

        return view('consultores.create', compact('techLeads'));
    }

    public function store(Request $request): RedirectResponse
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
                'nome' => $request->string('nome'),
                'email' => $request->string('email'),
                'password' => Hash::make((string) $request->input('password')),
                'funcao' => 'consultor',
            ]);

            $consultor = new Consultor([
                'nome' => $request->string('nome'),
                'email' => $request->string('email'),
                'telefone' => $request->string('telefone'),
                'status' => 'Ativo',
            ]);

            $consultor->usuario()->associate($user);
            $consultor->save();

            if ($request->has('tech_leads')) {
                $consultor->techLeads()->sync((array) $request->input('tech_leads'));
            }
        });

        return redirect()->route('consultores.index')
            ->with('success', 'Consultor criado com sucesso.');
    }

    public function show(Consultor $consultor): View
    {
        $consultor->load('usuario', 'techLeads');

        return view('consultores.show', compact('consultor'));
    }

    public function edit(Consultor $consultor): View
    {
        $techLeads = User::where('funcao', 'techlead')->get();
        $consultor->load('techLeads');

        return view('consultores.edit', compact('consultor', 'techLeads'));
    }

    public function update(Request $request, Consultor $consultor): RedirectResponse
    {
        $consultor->load('usuario');

        $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:usuarios,email,'.$consultor->usuario->id],
            'telefone' => ['nullable', 'string', 'max:20'],
            'status' => ['required', 'string', 'in:Ativo,Inativo'],
            'tech_leads' => ['nullable', 'array'],
            'tech_leads.*' => ['exists:usuarios,id'],
        ]);

        DB::transaction(function () use ($request, $consultor) {
            $consultor->usuario->update([
                'nome' => $request->string('nome'),
                'email' => $request->string('email'),
            ]);

            $consultor->update([
                'nome' => $request->string('nome'),
                'email' => $request->string('email'),
                'telefone' => $request->string('telefone'),
                'status' => $request->string('status'),
            ]);

            $consultor->techLeads()->sync((array) $request->input('tech_leads', []));
        });

        return redirect()->route('consultores.index')
            ->with('success', 'Consultor atualizado com sucesso.');
    }

    public function destroy(Consultor $consultor): RedirectResponse
    {
        $consultor->load('usuario');

        DB::transaction(function () use ($consultor) {
            $user = $consultor->usuario;
            $consultor->delete();
            if ($user) {
                $user->delete();
            }
        });

        return redirect()->route('consultores.index')
            ->with('success', 'Consultor removido com sucesso.');
    }
}
