<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Apontamento;
use App\Models\Consultor;
use App\Models\EmpresaParceira;
use Illuminate\Support\Facades\DB;
use App\Exports\ApontamentosExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class RelatorioController extends Controller
{
    public function index()
    {
        $consultores = Consultor::orderBy('nome')->get();
        $empresas = EmpresaParceira::orderBy('nome_empresa')->get();

        return view('relatorios.index', compact('consultores', 'empresas'));
    }

    public function gerar(Request $request)
    {
        $request->validate([
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date|after_or_equal:data_inicio',
            'consultor_id' => 'nullable|exists:consultores,id',
            'empresa_id' => 'nullable|exists:empresas_parceiras,id',
            'formato' => 'required|in:html,pdf,excel'
        ]);

        $query = Apontamento::with('consultor', 'agenda.projeto.empresaParceira')
                            ->where('status', 'Aprovado')
                            ->whereBetween('data_apontamento', [$request->data_inicio, $request->data_fim]);

        if ($request->filled('consultor_id')) {
            $query->where('consultor_id', $request->consultor_id);
        }

        if ($request->filled('empresa_id')) {
            $query->whereHas('agenda.projeto', function ($q) use ($request) {
                $q->where('empresa_parceira_id', $request->empresa_id);
            });
        }

        $apontamentos = $query->latest('data_apontamento')->get();
        $filtros = $request->only(['data_inicio', 'data_fim', 'consultor_id', 'empresa_id']);

        if ($request->formato === 'pdf') {
            $pdf = Pdf::loadView('relatorios.pdf', [
                'apontamentos' => $apontamentos,
                'filtros' => $filtros
            ]);
            return $pdf->download('relatorio_apontamentos.pdf');
        }

        if ($request->formato === 'excel') {
            return Excel::download(new ApontamentosExport($apontamentos, $filtros), 'relatorio_apontamentos.xlsx');
        }

        $kpis = [
            'total_horas' => $apontamentos->sum('horas_gastas'),
            'total_apontamentos' => $apontamentos->count(),
            'media_horas' => $apontamentos->count() > 0 ? $apontamentos->sum('horas_gastas') / $apontamentos->count() : 0,
        ];

        $horasPorCliente = $apontamentos->groupBy('agenda.projeto.empresaParceira.nome_empresa')
            ->map->sum('horas_gastas')->sortDesc();

        $horasPorConsultor = $apontamentos->groupBy('consultor.nome')
            ->map->sum('horas_gastas')->sortDesc();

        return view('relatorios.resultado', compact('apontamentos', 'kpis', 'horasPorCliente', 'horasPorConsultor', 'filtros'));
    }
}