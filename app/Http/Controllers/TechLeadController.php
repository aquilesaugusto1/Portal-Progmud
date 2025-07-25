<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Consultor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class TechLeadController extends Controller
{
    public function index()
    {
        $techLeads = User::where('funcao', 'techlead')->latest()->paginate(10);
        return view('techleads.index', compact('techLeads'));
    }

    public function create()
    {
        return view('techleads.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        User::create([
            'nome' => $request->nome,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'funcao' => 'techlead',
        ]);

        return redirect()->route('techleads.index')
                         ->with('success', 'Tech Lead criado com sucesso.');
    }

    public function show(User $techlead)
    {
        $techlead->load('consultoresLiderados');
        return view('techleads.show', compact('techlead'));
    }

    public function edit(User $techlead)
    {
        $consultores = Consultor::all();
        $techlead->load('consultoresLiderados');
        return view('techleads.edit', compact('techlead', 'consultores'));
    }

    public function update(Request $request, User $techlead)
    {
        $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:usuarios,email,' . $techlead->id],
            'consultores' => ['nullable', 'array'],
            'consultores.*' => ['exists:consultores,id'],
        ]);

        $techlead->update($request->only('nome', 'email'));
        
        $techlead->consultoresLiderados()->sync($request->input('consultores', []));

        return redirect()->route('techleads.index')
                         ->with('success', 'Tech Lead atualizado com sucesso.');
    }

    public function destroy(User $techlead)
    {
        $techlead->consultoresLiderados()->detach();
        $techlead->delete();

        return redirect()->route('techleads.index')
                         ->with('success', 'Tech Lead removido com sucesso.');
    }
}
