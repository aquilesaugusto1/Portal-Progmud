<?php

namespace App\Services;

use App\Models\Apontamento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RelatorioService
{
    public function getDadosRelatorio(Request $request)
    {
        $user = Auth::user();
        $query = Apontamento::query()
            ->with([
                'colaborador',
                'agenda.contrato.empresaParceira'
            ])
            ->select('apontamentos.*');

        // Filtros de data e status
        if ($request->filled('data_inicio')) {
            $query->where('data', '>=', $request->data_inicio);
        }

        if ($request->filled('data_fim')) {
            $query->where('data', '<=', $request->data_fim);
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filtros de Entidade
        if ($request->filled('colaborador_id')) {
            $query->where('colaborador_id', $request->colaborador_id);
        }

        if ($request->filled('empresa_id')) {
            $query->whereHas('agenda.contrato', function ($q) use ($request) {
                $q->where('empresa_parceira_id', $request->empresa_id);
            });
        }

        if ($request->filled('contrato_id')) {
            $query->whereHas('agenda', function ($q) use ($request) {
                $q->where('contrato_id', $request->contrato_id);
            });
        }

        // Lógica de Permissão
        if ($user->isConsultor()) {
            $query->where('colaborador_id', $user->id);
        } 
        elseif ($user->isTechLead()) {
            $lideradosIds = $user->consultoresLiderados->pluck('id')->toArray();
            
            // Se um consultor específico foi filtrado, verifica se ele é um liderado
            if ($request->filled('colaborador_id') && !in_array($request->colaborador_id, $lideradosIds)) {
                 // Retorna uma coleção vazia se o tech lead tentar filtrar um consultor que não lidera
                return collect();
            }
            
            $query->whereIn('colaborador_id', $lideradosIds);
        }
        // Se for admin ou coordenador, não aplica filtro de permissão (vê tudo)

        return $query->orderBy('data', 'asc')->get();
    }

    public function getDadosGrafico($apontamentos)
    {
        if ($apontamentos->isEmpty()) {
            return [
                'labels' => [],
                'values' => [],
            ];
        }

        $dados = $apontamentos->groupBy('colaborador.nome')
            ->map(function ($group) {
                return $group->sum(function ($apontamento) {
                    if (empty($apontamento->horas_aprovadas)) {
                        return 0;
                    }
                    
                    $partes = explode(':', $apontamento->horas_aprovadas);
                    $horas = isset($partes[0]) ? (int)$partes[0] : 0;
                    $minutos = isset($partes[1]) ? (int)$partes[1] / 60 : 0;
                    
                    return $horas + $minutos;
                });
            })
            ->sortDesc();

        return [
            'labels' => $dados->keys(),
            'values' => $dados->values(),
        ];
    }
}
