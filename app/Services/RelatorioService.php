<?php

namespace App\Services;

use App\Models\Apontamento;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class RelatorioService
{
    /**
     * Monta a query base para os relatórios com os filtros aplicados.
     */
    private function getQuery(array $filtros): Builder
    {
        $user = Auth::user();
        // Usar withDefault() nos relacionamentos para evitar erros de propriedade nula.
        // Isso garante que, se um contrato ou cliente não for encontrado, um modelo vazio será retornado.
        $query = Apontamento::with(['consultor', 'contrato.cliente' => fn($q) => $q->withDefault(), 'agenda'])
            ->whereBetween('data_apontamento', [$filtros['data_inicio'], $filtros['data_fim']]);

        if (!empty($filtros['colaborador_id'])) {
            $query->where('consultor_id', $filtros['colaborador_id']);
        }
        if (!empty($filtros['contrato_id'])) {
            $query->where('contrato_id', $filtros['contrato_id']);
        }
        if (!empty($filtros['empresa_id'])) {
            $query->whereHas('contrato', fn($q) => $q->where('cliente_id', $filtros['empresa_id']));
        }
        if (!empty($filtros['status'])) {
            $query->where('status', $filtros['status']);
        }

        if ($user->isTechLead()) {
            $lideradosIds = $user->consultoresLiderados()->pluck('usuarios.id');
            $query->whereIn('consultor_id', $lideradosIds);
        }

        return $query;
    }

    /**
     * Gera todos os dados necessários para a página de relatório (HTML).
     */
    public function gerarDadosCompletos(array $filtros): array
    {
        $query = $this->getQuery($filtros);
        $apontamentos = $query->orderBy('data_apontamento', 'desc')->get();
        
        $apontamentosAprovados = $apontamentos->where('status', 'Aprovado');

        $totalHoras = $apontamentosAprovados->sum('horas_gastas');

        $kpis = [
            'total_horas' => $totalHoras,
            'total_apontamentos' => $apontamentos->count(),
            'total_aprovados' => $apontamentosAprovados->count(),
            'media_horas' => $apontamentosAprovados->count() > 0 ? $totalHoras / $apontamentosAprovados->count() : 0,
        ];

        // CORREÇÃO: Agrupamento seguro para evitar erro com cliente nulo.
        $horasPorCliente = $apontamentosAprovados->groupBy(function ($apontamento) {
            return $apontamento->contrato->cliente->nome_empresa ?? 'Cliente não informado';
        })->map(fn($group) => $group->sum('horas_gastas'))->sortDesc();

        $horasPorConsultor = $apontamentosAprovados->groupBy('consultor.nome')
            ->map(fn($group) => $group->sum('horas_gastas'))
            ->sortDesc();

        return compact('apontamentos', 'kpis', 'horasPorCliente', 'horasPorConsultor');
    }

    /**
     * Retorna apenas a coleção de apontamentos para exportação (PDF/Excel).
     */
    public function getDadosParaExportacao(array $filtros): Collection
    {
        return $this->getQuery($filtros)->orderBy('data_apontamento', 'asc')->get();
    }
}
