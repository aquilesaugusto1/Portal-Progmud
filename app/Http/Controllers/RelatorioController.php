<?php

namespace App\Http\Controllers;

use App\Models\Contrato;
use App\Services\RelatorioService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class RelatorioController extends Controller
{
    protected RelatorioService $relatorioService;

    public function __construct(RelatorioService $relatorioService)
    {
        $this->relatorioService = $relatorioService;
    }

    public function index(): View
    {
        return view('relatorios.index');
    }

    public function show(string $tipo): View
    {
        Log::info('Acessando RelatorioController@show', ['tipo' => $tipo]);

        switch ($tipo) {
            case 'historico-techleads':
                $dadosFiltro = $this->relatorioService->getFiltrosHistoricoTechLeads();
                Log::info('Dados para a view inicial (show):', $dadosFiltro);

                return view('relatorios.historico-techleads', $dadosFiltro);

            case 'alocacao-consultores':
                $dadosFiltro = $this->relatorioService->getFiltrosAlocacao();

                return view('relatorios.alocacao-consultores', $dadosFiltro);

            case 'visao-geral-contratos':
                $dadosFiltro = $this->relatorioService->getFiltrosContratos();

                return view('relatorios.visao-geral-contratos', $dadosFiltro);
            
            case 'planilha-semanal':
                return view('relatorios.planilha-semanal');

            default:
                abort(404);
        }
    }

    public function gerar(Request $request): View|RedirectResponse|Response
    {
        $tipo = $request->input('tipo_relatorio');

        Log::info('Acessando RelatorioController@gerar', ['tipo' => $tipo, 'request_data' => $request->all()]);

        switch ($tipo) {
            case 'planilha-semanal':
                $filtros = $request->validate([
                    'data_selecionada' => 'required|date',
                    'formato' => 'required|in:html',
                ]);

                $dadosRelatorio = $this->relatorioService->gerarRelatorioPlanilhaSemanal($filtros);

                return view('relatorios.planilha-semanal', array_merge($dadosRelatorio, ['filtros' => $filtros]));

            case 'historico-techleads':
                $filtros = $request->validate([
                    'contrato_id' => 'required|exists:contratos,id',
                    'formato' => 'required|in:html,pdf',
                ]);

                $dadosRelatorio = $this->relatorioService->gerarRelatorioHistoricoTechLeads($filtros);
                $contrato = Contrato::findOrFail($filtros['contrato_id']);

                if ($filtros['formato'] === 'pdf') {
                    $dadosParaPdf = array_merge($dadosRelatorio, ['contrato' => $contrato]);
                    Log::info('Dados enviados para o PDF:', $dadosParaPdf);
                    $pdf = Pdf::loadView('relatorios.pdf.historico-techleads', $dadosParaPdf);

                    return $pdf->download('relatorio_historico_techleads_'.now()->format('Y-m-d').'.pdf');
                }

                $dadosFiltro = $this->relatorioService->getFiltrosHistoricoTechLeads();

                $dadosParaView = array_merge($dadosRelatorio, $dadosFiltro, ['filtros' => $filtros, 'contrato' => $contrato]);

                Log::info('Dados finais enviados para a view (gerar):', $dadosParaView);

                return view('relatorios.historico-techleads', $dadosParaView);

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
                        'dias_uteis' => $dadosRelatorio['dias_uteis'],
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
                        'filtros' => $filtros,
                    ]);

                    return $pdf->download('relatorio_visao_contratos_'.now()->format('Y-m-d').'.pdf');
                }

                $dadosFiltro = $this->relatorioService->getFiltrosContratos();

                return view('relatorios.visao-geral-contratos', array_merge($dadosRelatorio, $dadosFiltro, ['filtros' => $filtros]));

            default:
                return redirect()->route('relatorios.index')->with('error', 'Tipo de relatório inválido.');
        }
    }

    public function detalhesApontamentos(Request $request): JsonResponse
    {
        Log::info('Iniciando busca de detalhes de apontamentos.', $request->all());

        try {
            $validatedData = $request->validate([
                'consultor_id' => 'required|integer|exists:usuarios,id',
                'data_inicio' => 'required|date',
                'data_fim' => 'required|date',
            ]);

            Log::info('Validação bem-sucedida.');

            $detalhes = $this->relatorioService->getDetalhesApontamentosPorConsultor(
                (int) $validatedData['consultor_id'],
                $validatedData['data_inicio'],
                $validatedData['data_fim']
            );

            Log::info('Busca de detalhes concluída com sucesso.', ['count' => $detalhes->count()]);
            return response()->json($detalhes);

        } catch (ValidationException $e) {
            Log::error('Erro de validação ao buscar detalhes de apontamentos.', [
                'errors' => $e->errors(),
                'request' => $request->all()
            ]);
            return response()->json(['message' => 'Dados inválidos.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::critical('Erro inesperado ao buscar detalhes de apontamentos.', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            return response()->json(['message' => 'Ocorreu um erro interno no servidor.'], 500);
        }
    }
}
