<?php

namespace App\Services;

use App\Models\Agenda;
use App\Models\Apontamento;
use App\Models\Contrato;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class RelatorioService
{
    public function getFiltrosAlocacao(): array
    {
        $consultoresPJ = User::where('funcao', 'consultor')
            ->whereIn('tipo_contrato', ['PJ Mensal', 'PJ Horista'])
            ->where('status', 'Ativo')
            ->orderBy('nome')
            ->get();

        return ['consultores' => $consultoresPJ];
    }

    public function gerarRelatorioAlocacao(array $filtros): array
    {
        $inicioPeriodo = Carbon::parse($filtros['data_inicio'])->startOfDay();
        $fimPeriodo = Carbon::parse($filtros['data_fim'])->endOfDay();

        $diasUteis = $this->getDiasUteisNoPeriodo($inicioPeriodo, $fimPeriodo);
        $horasUteisDoPeriodo = $diasUteis * 8;

        $consultores = User::whereIn('id', $filtros['consultores_id'])->get();
        $resultados = [];

        foreach ($consultores as $consultor) {
            $horasApontadas = Apontamento::where('consultor_id', $consultor->id)
                ->where('status', 'Aprovado')
                ->whereBetween('data_apontamento', [$inicioPeriodo, $fimPeriodo])
                ->sum('horas_gastas');

            $numeroDeAgendas = Agenda::where('consultor_id', $consultor->id)
                ->whereBetween('data_hora', [$inicioPeriodo, $fimPeriodo])
                ->where('status', '!=', 'Cancelada')
                ->count();

            $horasUteisRestantes = $horasUteisDoPeriodo - abs((float) $horasApontadas);

            $resultados[] = [
                'consultor' => $consultor,
                'horas_apontadas' => $horasApontadas,
                'numero_agendas' => $numeroDeAgendas,
                'horas_uteis_periodo' => $horasUteisDoPeriodo,
                'horas_uteis_restantes' => $horasUteisRestantes,
            ];
        }

        return ['resultados' => $resultados, 'dias_uteis' => $diasUteis];
    }

    private function getDiasUteisNoPeriodo(Carbon $inicio, Carbon $fim): int
    {
        $diasUteis = 0;
        $feriados = $this->getFeriados(range($inicio->year, $fim->year));
        $dataAtual = $inicio->copy();

        while ($dataAtual <= $fim) {
            if ($dataAtual->isWeekday() && ! in_array($dataAtual->format('Y-m-d'), $feriados)) {
                $diasUteis++;
            }
            $dataAtual->addDay();
        }

        return $diasUteis;
    }

    private function getFeriados(array $anos): array
    {
        $feriados = [];
        foreach ($anos as $ano) {
            $feriados[] = "{$ano}-01-01";
            $feriados[] = "{$ano}-04-21";
            $feriados[] = "{$ano}-05-01";
            $feriados[] = "{$ano}-09-07";
            $feriados[] = "{$ano}-10-12";
            $feriados[] = "{$ano}-11-02";
            $feriados[] = "{$ano}-11-15";
            $feriados[] = "{$ano}-12-25";

            $pascoaTimestamp = easter_date($ano);
            $feriados[] = date('Y-m-d', strtotime('-2 days', $pascoaTimestamp));
            $feriados[] = date('Y-m-d', strtotime('-47 days', $pascoaTimestamp));
            $feriados[] = date('Y-m-d', strtotime('+60 days', $pascoaTimestamp));
        }

        return $feriados;
    }

    public function getFiltrosContratos(): array
    {
        $contratos = Contrato::where('status', 'Ativo')->with('empresaParceira')->orderBy('numero_contrato')->get();

        return compact('contratos');
    }

    public function gerarRelatorioContratos(array $filtros): array
    {
        $contratos = Contrato::with('empresaParceira')->whereIn('id', $filtros['contratos_id'])->get();
        $resultados = [];

        foreach ($contratos as $contrato) {
            $horasOriginais = $contrato->baseline_horas_original ?? 0;
            $horasRestantes = $contrato->baseline_horas_mes ?? 0;
            $horasGastas = (float) $horasOriginais - (float) $horasRestantes;
            $percentualGasto = ($horasOriginais > 0) ? ($horasGastas / (float) $horasOriginais) * 100 : 0;

            $resultados[] = [
                'contrato' => $contrato,
                'horas_gastas' => $horasGastas,
                'saldo_horas' => $horasRestantes,
                'percentual_gasto' => round($percentualGasto),
            ];
        }

        return ['resultados' => $resultados];
    }

    public function getFiltrosHistoricoTechLeads(): array
    {
        $contratos = Contrato::where('status', 'Ativo')->with('empresaParceira')->orderBy('numero_contrato')->get();

        return compact('contratos');
    }

    public function gerarRelatorioHistoricoTechLeads(array $filtros): array
    {
        $contrato = Contrato::with('empresaParceira')->findOrFail($filtros['contrato_id']);

        $historico = DB::table('contrato_historico_techleads as historico')
            ->join('usuarios', 'historico.tech_lead_id', '=', 'usuarios.id')
            ->where('historico.contrato_id', $contrato->id)
            ->select('usuarios.nome as tech_lead_nome', 'historico.data_inicio', 'historico.data_fim')
            ->orderBy('historico.data_inicio', 'desc')
            ->get();

        return [
            'contrato' => $contrato,
            'historico' => $historico,
        ];
    }

    public function getDetalhesApontamentosPorConsultor(int $consultorId, string $dataInicio, string $dataFim): Collection
    {
        return Apontamento::with(['contrato.empresaParceira', 'agenda'])
            ->where('consultor_id', $consultorId)
            ->where('status', 'Aprovado')
            ->whereBetween('data_apontamento', [
                Carbon::parse($dataInicio)->startOfDay(),
                Carbon::parse($dataFim)->endOfDay()
            ])
            ->orderBy('data_apontamento', 'desc')
            ->get();
    }

    public function gerarRelatorioPlanilhaSemanal(array $filtros): array
    {
        $dataSelecionada = Carbon::parse($filtros['data_selecionada']);
        $inicioSemana = $dataSelecionada->copy()->startOfWeek(Carbon::MONDAY);
        $fimSemana = $dataSelecionada->copy()->endOfWeek(Carbon::SUNDAY);

        $periodo = [
            'inicio' => $inicioSemana,
            'fim' => $fimSemana,
            'dias' => [],
        ];
        $diasDaSemana = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];
        for ($i = 0; $i < 7; $i++) {
            $dia = $inicioSemana->copy()->addDays($i);
            $periodo['dias'][] = [
                'nome' => $diasDaSemana[$dia->dayOfWeek],
                'data' => $dia->format('d/m'),
                'data_iso' => $dia->format('Y-m-d'),
            ];
        }

        $apontamentos = Apontamento::with('contrato.empresaParceira')
            ->where('status', 'Aprovado')
            ->whereBetween('data_apontamento', [$inicioSemana, $fimSemana])
            ->get();

        $resultados = [];
        $totaisPorDia = array_fill_keys(array_column($periodo['dias'], 'data_iso'), 0);
        $totalGeral = 0;

        foreach ($apontamentos as $apontamento) {
            if (!$apontamento->contrato) {
                continue;
            }
            $contratoId = $apontamento->contrato->id;
            $dataApontamento = Carbon::parse($apontamento->data_apontamento)->format('Y-m-d');
            $horas = (float) $apontamento->horas_gastas;

            if (!isset($resultados[$contratoId])) {
                $resultados[$contratoId] = [
                    'contrato_nome' => $apontamento->contrato->empresaParceira->nome_empresa . ' - ' . $apontamento->contrato->numero_contrato,
                    'horas_por_dia' => [],
                    'total_horas' => 0,
                ];
            }

            if (!isset($resultados[$contratoId]['horas_por_dia'][$dataApontamento])) {
                $resultados[$contratoId]['horas_por_dia'][$dataApontamento] = 0;
            }
            $resultados[$contratoId]['horas_por_dia'][$dataApontamento] += $horas;
            $resultados[$contratoId]['total_horas'] += $horas;

            $totaisPorDia[$dataApontamento] += $horas;
            $totalGeral += $horas;
        }

        return [
            'resultados' => $resultados,
            'periodo' => $periodo,
            'totais_por_dia' => $totaisPorDia,
            'total_geral' => $totalGeral,
        ];
    }
}
