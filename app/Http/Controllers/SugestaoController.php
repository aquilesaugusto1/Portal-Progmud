<?php

namespace App\Http\Controllers;

use App\Models\Sugestao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SugestaoController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Sugestao::class);
        
        $query = Sugestao::with('usuario')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $sugestoes = $query->paginate(9)->withQueryString();

        return view('sugestoes.index', compact('sugestoes'));
    }

    public function create()
    {
        $this->authorize('create', Sugestao::class);
        return view('sugestoes.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Sugestao::class);
        $request->validate([
            'titulo' => 'required|string|max:255',
            'descricao' => 'required|string',
        ]);

        Sugestao::create([
            'titulo' => $request->titulo,
            'descricao' => $request->descricao,
            'status' => 'Pendente',
            'usuario_id' => Auth::id(),
        ]);

        return redirect()->route('sugestoes.index')->with('success', 'Sugestão enviada com sucesso!');
    }

    public function update(Request $request, Sugestao $sugestao)
    {
        $this->authorize('update', $sugestao);
        $request->validate([
            'status' => 'required|string|in:Pendente,Em Análise,Concluída,Rejeitada',
        ]);

        $sugestao->update(['status' => $request->status]);

        return back()->with('success', 'Status da sugestão atualizado.');
    }
}