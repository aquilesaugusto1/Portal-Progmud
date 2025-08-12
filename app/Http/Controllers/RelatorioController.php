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
            case 'apontamentos':
                $dadosFiltro = $this->relatorioService->getFiltrosApontamentos();
                return view('relatorios.apontamentos', $dadosFiltro);

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
            case 'apontamentos':
                // ... lógica do relatório de apontamentos ...
                break;

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
