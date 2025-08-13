<?php

namespace App\Services;

use App\Models\User;
use App\Models\Agenda;
use App\Models\Contrato;
use App\Models\Apontamento;
use App\Models\EmpresaParceira;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class RelatorioService
{
    public function getFiltrosApontamentos()
    {
        $user = Auth::user();
        $clientes = EmpresaParceira::orderBy('nome_empresa')->get();
        $contratos = $user->isTechLead() ? $user->contratos()->orderBy('numero_contrato')->get() : Contrato::orderBy('numero_contrato')->get();
        $colaboradores = $user->isTechLead() ? $user->consultoresLiderados()->orderBy('nome')->get() : User::where('funcao', 'consultor')->orderBy('nome')->get();
        return compact('clientes', 'contratos', 'colaboradores');
    }

    public function gerarRelatorioApontamentos(array $filtros)
    {
        $query = Apontamento::with(['consultor', 'contrato.cliente']);
        $apontamentos = $query->get();
        return ['resultados' => $apontamentos];
    }

    public function getFiltrosAlocacao()
    {
        $consultoresPJ = User::where('funcao', 'consultor')
                                ->whereIn('tipo_contrato', ['PJ Mensal', 'PJ Horista'])
                                ->where('status', 'Ativo')
                                ->orderBy('nome')
                                ->get();
        return ['consultores' => $consultoresPJ];
    }

    public function gerarRelatorioAlocacao(array $filtros)
    {
        $inicioPeriodo = Carbon::parse($filtros['data_inicio'])->startOfDay();
        $fimPeriodo = Carbon::parse($filtros['data_fim'])->endOfDay();

        $diasUteis = $this->getDiasUteisNoPeriodo($inicioPeriodo, $fimPeriodo);
        $horasUteisDoPeriodo = $diasUteis * 8;

        $consultores = User::whereIn('id', $filtros['consultores_id'])->get();
        $resultados = [];

        foreach ($consultores as $consultor) {
            $horasApontadas = Apontamento::where('consultor_id', $consultor->id)
                ->whereBetween('data_apontamento', [$inicioPeriodo, $fimPeriodo])
                ->sum('horas_gastas');

            $numeroDeAgendas = Agenda::where('consultor_id', $consultor->id)
                ->whereBetween('data_hora', [$inicioPeriodo, $fimPeriodo])
                ->where('status', '!=', 'Cancelada')
                ->count();
            
            $horasUteisRestantes = $horasUteisDoPeriodo - abs($horasApontadas);

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
            if ($dataAtual->isWeekday() && !in_array($dataAtual->format('Y-m-d'), $feriados)) {
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

    public function getFiltrosContratos()
    {
        $contratos = Contrato::where('status', 'Ativo')->with('cliente')->orderBy('numero_contrato')->get();
        return compact('contratos');
    }

    public function gerarRelatorioContratos(array $filtros)
    {
        $contratos = Contrato::with('cliente')->whereIn('id', $filtros['contratos_id'])->get();
        $resultados = [];

        foreach ($contratos as $contrato) {
            $horasOriginais = $contrato->baseline_horas_original ?? 0;
            $horasRestantes = $contrato->baseline_horas_mes ?? 0;
            $horasGastas = $horasOriginais - $horasRestantes;
            $percentualGasto = ($horasOriginais > 0) ? ($horasGastas / $horasOriginais) * 100 : 0;

            $resultados[] = [
                'contrato' => $contrato,
                'horas_gastas' => $horasGastas,
                'saldo_horas' => $horasRestantes,
                'percentual_gasto' => round($percentualGasto)
            ];
        }
        return ['resultados' => $resultados];
    }
}