<?php

namespace App\Http\Controllers;

use App\Models\CpTotvs;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CpTotvsController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', CpTotvs::class);
        $cpTotvs = CpTotvs::latest()->paginate(10);
        return view('cp-totvs.index', compact('cpTotvs'));
    }

    public function create(): View
    {
        $this->authorize('create', CpTotvs::class);
        $cp = new CpTotvs();
        return view('cp-totvs.create', compact('cp'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', CpTotvs::class);
        $validatedData = $request->validate($this->getValidationRules());

        $validatedData['user_creator_id'] = Auth::id();
        $validatedData['user_updater_id'] = Auth::id();

        CpTotvs::create($validatedData);

        return redirect()->route('cp-totvs.index')->with('success', 'CP da TOTVS criado com sucesso.');
    }

    public function show(CpTotvs $cp_totv): View
    {
        $this->authorize('view', $cp_totv);
        return view('cp-totvs.show', ['cp' => $cp_totv]);
    }

    public function edit(CpTotvs $cp_totv): View
    {
        $this->authorize('update', $cp_totv);
        return view('cp-totvs.edit', ['cp' => $cp_totv]);
    }

    public function update(Request $request, CpTotvs $cp_totv): RedirectResponse
    {
        $this->authorize('update', $cp_totv);
        $validatedData = $request->validate($this->getValidationRules($cp_totv->id));

        $validatedData['user_updater_id'] = Auth::id();

        $cp_totv->update($validatedData);

        return redirect()->route('cp-totvs.index')->with('success', 'CP da TOTVS atualizado com sucesso.');
    }

    public function toggleStatus(CpTotvs $cp_totv): RedirectResponse
    {
        $this->authorize('toggleStatus', $cp_totv);
        $novoStatus = $cp_totv->status === 'Ativo' ? 'Inativo' : 'Ativo';
        $cp_totv->update(['status' => $novoStatus]);
        $mensagem = $novoStatus === 'Ativo' ? 'CP da TOTVS ativado com sucesso.' : 'CP da TOTVS desabilitado com sucesso.';

        return back()->with('success', $mensagem);
    }

    private function getValidationRules(?int $id = null): array
    {
        return [
            'nome' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('cp_totvs')->ignore($id)],
            'telefone' => ['nullable', 'string', 'max:20'],
            'status' => ['required', 'string'],
        ];
    }
}