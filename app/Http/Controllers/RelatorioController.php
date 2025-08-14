<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\RelatorioService;
use Barryvdh\DomPDF\Facade\Pdf;

class RelatorioController extends Controller
{
    protected $relatorioService;

    public function __construct(RelatorioService $relatorioService)
    {
        $this->relatorioService = $relatorioService;
    }

    public function index()
    {
        return view('relatorios.index');
    }

    public function show(string $tipo)
    {
        switch ($tipo) {
            case 'historico-techleads':
                $dadosFiltro = $this->relatorioService->getFiltrosHistoricoTechLeads();
                return view('relatorios.historico-techleads', $dadosFiltro);

            case 'alocacao-consultores':
                $dadosFiltro = $this->relatorioService->getFiltrosAlocacao();
                return view('relatorios.alocacao-consultores', $dadosFiltro);

            case 'visao-geral-contratos':
                $dadosFiltro = $this->relatorioService->getFiltrosContratos();
                return view('relatorios.visao-geral-contratos', $dadosFiltro);

            default:
                abort(404);
        }
    }

    public function gerar(Request $request)
    {
        $tipo = $request->input('tipo_relatorio');
        
        switch ($tipo) {
            case 'historico-techleads':
                $filtros = $request->validate([
                    'contrato_id' => 'required|exists:contratos,id',
                    'formato' => 'required|in:html,pdf',
                ]);

                $dadosRelatorio = $this->relatorioService->gerarRelatorioHistoricoTechLeads($filtros);

                if ($filtros['formato'] === 'pdf') {
                    $pdf = Pdf::loadView('relatorios.pdf.historico-techleads', $dadosRelatorio);
                    return $pdf->download('relatorio_historico_techleads_'.now()->format('Y-m-d').'.pdf');
                }

                $dadosFiltro = $this->relatorioService->getFiltrosHistoricoTechLeads();
                return view('relatorios.historico-techleads', array_merge($dadosRelatorio, $dadosFiltro, ['filtros' => $filtros]));

            case 'alocacao-consultores':
                $filtros = $request->validate([
                    'data_inicio' => 'required|date',
                    'data_fim' => 'required|date|after_or_equal:data_inicio',
                    'consultores_id' => 'required|array',
                    'formato' => 'required|in:html,pdf',
                ]);

                $dadosRelatorio = $this->relatorioService->gerarRelatorioAlocacao($filtros);

                if ($filtros['formato'] === 'pdf') {
                    $pdf = Pdf::loadView('relatorios.pdf.alocacao-consultores', [
                        'resultados' => $dadosRelatorio['resultados'],
                        'filtros' => $filtros,
                        'dias_uteis' => $dadosRelatorio['dias_uteis']
                    ]);
                    return $pdf->download('relatorio_alocacao_consultores_'.now()->format('Y-m-d').'.pdf');
                }
                
                $dadosFiltro = $this->relatorioService->getFiltrosAlocacao();
                return view('relatorios.alocacao-consultores', array_merge($dadosRelatorio, $dadosFiltro, ['filtros' => $filtros]));

            case 'visao-geral-contratos':
                $filtros = $request->validate([
                    'contratos_id' => 'required|array',
                    'formato' => 'required|in:html,pdf',
                ]);

                $dadosRelatorio = $this->relatorioService->gerarRelatorioContratos($filtros);

                if ($filtros['formato'] === 'pdf') {
                    $pdf = Pdf::loadView('relatorios.pdf.visao-geral-contratos', [
                        'resultados' => $dadosRelatorio['resultados'],
                        'filtros' => $filtros
                    ]);
                    return $pdf->download('relatorio_visao_contratos_'.now()->format('Y-m-d').'.pdf');
                }

                $dadosFiltro = $this->relatorioService->getFiltrosContratos();
                return view('relatorios.visao-geral-contratos', array_merge($dadosRelatorio, $dadosFiltro, ['filtros' => $filtros]));

            default:
                return redirect()->route('relatorios.index')->with('error', 'Tipo de relatório inválido.');
        }
    }
}
