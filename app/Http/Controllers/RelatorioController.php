<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Contrato;
use App\Models\EmpresaParceira;
use App\Services\RelatorioService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ApontamentosExport;
use Barryvdh\DomPDF\Facade\Pdf;

class RelatorioController extends Controller
{
    protected $relatorioService;

    public function __construct(RelatorioService $relatorioService)
    {
        $this->relatorioService = $relatorioService;
    }

    private function getFiltroOptions()
    {
        $user = Auth::user();
        $consultores = collect();
        $contratos = collect();
        $empresas = collect();

        if ($user->isAdmin() || $user->isCoordenador()) {
            $consultores = User::where('funcao', 'consultor')->where('status', 'Ativo')->orderBy('nome')->get();
            $contratos = Contrato::where('status', 'Ativo')->orderBy('nome_contrato')->get();
            $empresas = EmpresaParceira::where('status', 'Ativo')->orderBy('razao_social')->get();

        } elseif ($user->isTechLead()) {
            $consultores = $user->consultoresLiderados()->where('status', 'Ativo')->orderBy('nome')->get();
            $consultorIds = $consultores->pluck('id');
            
            $contratos = Contrato::whereHas('usuarios', function ($query) use ($consultorIds) {
                $query->whereIn('users.id', $consultorIds);
            })->where('status', 'Ativo')->orderBy('nome_contrato')->get();
            
            $empresaIds = $contratos->pluck('empresa_parceira_id')->unique();
            $empresas = EmpresaParceira::whereIn('id', $empresaIds)->where('status', 'Ativo')->orderBy('razao_social')->get();
        }

        return compact('consultores', 'contratos', 'empresas');
    }

    public function index()
    {
        $filtroOptions = $this->getFiltroOptions();
        return view('relatorios.index', $filtroOptions);
    }

    public function gerar(Request $request)
    {
        $validated = $request->validate([
            'data_inicio' => 'nullable|date',
            'data_fim' => 'nullable|date|after_or_equal:data_inicio',
            'colaborador_id' => 'nullable|exists:users,id',
            'contrato_id' => 'nullable|exists:contratos,id',
            'empresa_id' => 'nullable|exists:empresas_parceiras,id',
            'status' => 'nullable|string',
            'formato' => 'required|in:html,pdf,excel'
        ]);

        $apontamentos = $this->relatorioService->getDadosRelatorio($request);
        $filtros = $request->only(['data_inicio', 'data_fim', 'colaborador_id', 'contrato_id', 'empresa_id', 'status']);

        if ($request->formato === 'pdf') {
            $pdf = Pdf::loadView('relatorios.pdf', compact('apontamentos', 'filtros'));
            return $pdf->download('relatorio_apontamentos.pdf');
        }

        if ($request->formato === 'excel') {
            return Excel::download(new ApontamentosExport($apontamentos), 'relatorio_apontamentos.xlsx');
        }

        $graficoData = $this->relatorioService->getDadosGrafico($apontamentos);
        $filtroOptions = $this->getFiltroOptions();

        return view('relatorios.index', array_merge(
            [
                'apontamentos' => $apontamentos,
                'filtros' => $filtros,
                'graficoLabels' => $graficoData['labels'],
                'graficoValues' => $graficoData['values'],
            ],
            $filtroOptions
        ));
    }
}
