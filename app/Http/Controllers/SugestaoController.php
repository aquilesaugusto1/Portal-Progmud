<?php

namespace App\Http\Controllers;

use App\Models\Sugestao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SugestaoController extends Controller
{
    public function index()
    {
        $sugestoes = Sugestao::with('usuario')->latest()->paginate(15);
        return view('sugestoes.index', compact('sugestoes'));
    }

    public function create()
    {
        return view('sugestoes.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'descricao' => 'required|string',
        ]);

        Sugestao::create([
            'usuario_id' => Auth::id(),
            'titulo' => $validated['titulo'],
            'descricao' => $validated['descricao'],
        ]);

        return redirect()->route('sugestoes.index')->with('success', 'Sugestão enviada com sucesso!');
    }
}
