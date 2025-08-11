<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Contrato;
use App\Models\EmpresaParceira;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ApontamentosExport;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\RelatorioService;
use App\Http\Requests\GerarRelatorioRequest;

class RelatorioController extends Controller
{
    protected $relatorioService;

    public function __construct(RelatorioService $relatorioService)
    {
        $this->relatorioService = $relatorioService;
    }

    /**
     * Retorna as opções para os filtros do formulário.
     */
    private function getFiltroOptions(): array
    {
        $user = Auth::user();
        
        $clientes = EmpresaParceira::orderBy('nome_empresa')->get();
        
        // --- LÓGICA DE FILTRO DE CONTRATOS ATUALIZADA ---
        if ($user->isTechLead()) {
            // Se o usuário for um Tech Lead, busca apenas os contratos aos quais ele está associado.
            $contratos = $user->contratos()->orderBy('numero_contrato')->get();
        } else {
            // Para outras funções (como Admin), busca todos os contratos.
            $contratos = Contrato::orderBy('numero_contrato')->get();
        }

        // Lógica para buscar colaboradores (respeitando a hierarquia do Tech Lead)
        if ($user->isTechLead()) {
            $colaboradores = $user->consultoresLiderados()->orderBy('nome')->get();
        } else {
            $colaboradores = User::where('funcao', 'consultor')->orderBy('nome')->get();
        }

        return compact('clientes', 'contratos', 'colaboradores');
    }

    /**
     * Exibe a página inicial de relatórios com os filtros.
     */
    public function index()
    {
        return view('relatorios.index', $this->getFiltroOptions());
    }

    /**
     * Gera o relatório com base nos filtros e no formato solicitado.
     */
    public function gerar(GerarRelatorioRequest $request)
    {
        $filtros = $request->validated();

        if ($filtros['formato'] === 'pdf') {
            $apontamentos = $this->relatorioService->getDadosParaExportacao($filtros);
            $apontamentosAprovados = $apontamentos->where('status', 'Aprovado');

            $totalHorasDecimal = $apontamentosAprovados->sum('horas_gastas');
            $horas = floor($totalHorasDecimal);
            $minutos = round(($totalHorasDecimal - $horas) * 60);
            $totalFormatado = sprintf('%02d:%02d', $horas, $minutos);

            $pdf = Pdf::loadView('relatorios.pdf', [
                'apontamentos' => $apontamentos,
                'apontamentosAprovados' => $apontamentosAprovados,
                'filtros' => $filtros,
                'totalFormatado' => $totalFormatado
            ]);
            return $pdf->download('relatorio_apontamentos_'.now()->format('Y-m-d').'.pdf');
        }

        if ($filtros['formato'] === 'excel') {
            $dadosParaExportacao = $this->relatorioService->getDadosParaExportacao($filtros);
            return Excel::download(new ApontamentosExport($dadosParaExportacao, $filtros), 'relatorio_apontamentos_'.now()->format('Y-m-d').'.xlsx');
        }

        $dadosRelatorio = $this->relatorioService->gerarDadosCompletos($filtros);
        
        return view('relatorios.index', array_merge(
            $dadosRelatorio,
            ['filtros' => $filtros],
            $this->getFiltroOptions()
        ));
    }
}
