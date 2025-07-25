<?php

namespace App\Http\Controllers;

use App\Models\EmpresaParceira;
use Illuminate\Http\Request;

class EmpresaParceiraController extends Controller
{
    public function index()
    {
        $empresas = EmpresaParceira::latest()->paginate(10);
        return view('empresas.index', compact('empresas'));
    }

    public function create()
    {
        return view('empresas.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nome_empresa' => 'required|string|max:255|unique:empresas_parceiras',
            'contato_principal' => 'nullable|string|max:255',
            'telefone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'ramo_atividade' => 'nullable|string|max:255',
            'horas_contratadas' => 'nullable|numeric|min:0',
        ]);

        $validatedData['saldo_horas'] = $validatedData['horas_contratadas'] ?? 0;

        EmpresaParceira::create($validatedData);

        return redirect()->route('empresas.index')
                         ->with('success', 'Empresa parceira criada com sucesso.');
    }

    public function show(EmpresaParceira $empresa)
    {
        return view('empresas.show', compact('empresa'));
    }

    public function edit(EmpresaParceira $empresa)
    {
        return view('empresas.edit', compact('empresa'));
    }

    public function update(Request $request, EmpresaParceira $empresa)
    {
        $validatedData = $request->validate([
            'nome_empresa' => 'required|string|max:255|unique:empresas_parceiras,nome_empresa,' . $empresa->id,
            'contato_principal' => 'nullable|string|max:255',
            'telefone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'ramo_atividade' => 'nullable|string|max:255',
            'horas_contratadas' => 'nullable|numeric|min:0',
        ]);

        $horasContratadasAnteriores = $empresa->horas_contratadas;
        $novasHorasContratadas = $validatedData['horas_contratadas'] ?? 0;
        
        $diferencaHoras = $novasHorasContratadas - $horasContratadasAnteriores;
        $validatedData['saldo_horas'] = $empresa->saldo_horas + $diferencaHoras;

        $empresa->update($validatedData);

        return redirect()->route('empresas.index')
                         ->with('success', 'Empresa parceira atualizada com sucesso.');
    }

    public function destroy(EmpresaParceira $empresa)
    {
        $empresa->delete();

        return redirect()->route('empresas.index')
                         ->with('success', 'Empresa parceira removida com sucesso.');
    }
}