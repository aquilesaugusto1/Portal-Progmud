<?php

namespace App\Http\Controllers;

use App\Models\Agenda;
use App\Models\Apontamento;
use App\Models\Contrato;
use App\Models\EmpresaParceira;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $stats = [];
        $contratosCriticos = collect();
        $consultoresAtivos = collect();
        $ultimas_agendas = collect();
        $chartLabels = [];
        $chartRealizadas = [];
        $chartAgendadas = [];
        $chartCanceladas = [];

        if ($user->funcao === 'admin') {
            $stats = [
                'Consultores' => User::where('funcao', 'consultor')->count(),
                'Tech Leads' => User::where('funcao', 'techlead')->count(),
                'Contratos' => Contrato::count(),
                'Clientes' => EmpresaParceira::count(),
            ];

            // $contratosCriticos = Contrato::where('baseline_horas_mes', '<', 10)->get();

            $consultoresAtivos = User::where('funcao', 'consultor')
                ->withSum(['apontamentos' => function ($query) {
                    $query->where('created_at', '>=', now()->subDays(30));
                }], 'horas_trabalhadas')
                ->get()
                ->sortByDesc('apontamentos_sum_horas_trabalhadas')
                ->map(function ($consultor) {
                    $consultor->horas_30_dias = $consultor->apontamentos_sum_horas_trabalhadas ?? 0;
                    return $consultor;
                });

            $agendasPorMes = Agenda::select(
                DB::raw('DATE_FORMAT(inicio_previsto, "%Y-%m") as mes'),
                'status',
                DB::raw('count(*) as total')
            )
            ->where('inicio_previsto', '>=', now()->subMonths(5)->startOfMonth())
            ->groupBy('mes', 'status')
            ->orderBy('mes', 'asc')
            ->get();
            
            $periodo = Carbon::now()->subMonths(5)->startOfMonth()->toPeriod(Carbon::now()->startOfMonth());
            $dadosGrafico = [];

            foreach ($periodo as $date) {
                $mes = $date->format('Y-m');
                $dadosGrafico[$mes] = ['Realizada' => 0, 'Agendada' => 0, 'Cancelada' => 0];
            }

            foreach ($agendasPorMes as $item) {
                if (isset($dadosGrafico[$item->mes])) {
                    $dadosGrafico[$item->mes][$item->status] = $item->total;
                }
            }

            foreach ($dadosGrafico as $mes => $status) {
                $chartLabels[] = Carbon::createFromFormat('Y-m', $mes)->format('M/y');
                $chartRealizadas[] = $status['Realizada'];
                $chartAgendadas[] = $status['Agendada'];
                $chartCanceladas[] = $status['Cancelada'];
            }

        } else {
            $meusContratosCount = 0;
            if ($user->funcao === 'techlead') {
                $meusContratosCount = Contrato::where('tech_lead_id', $user->id)->count();
            }

            $stats = [
                'Minhas Agendas Hoje' => Agenda::where('consultor_id', 'like', '%' . $user->id . '%')->whereDate('inicio_previsto', today())->count(),
                'Meus Contratos' => $meusContratosCount,
                'Apontamentos Pendentes' => Apontamento::where('consultor_id', 'like', '%' . $user->id . '%')->where('status_aprovacao', 'Pendente')->count(),
            ];

            $ultimas_agendas = Agenda::where('consultor_id', 'like', '%' . $user->id . '%')
                ->with(['consultor', 'contrato.cliente'])
                ->where('inicio_previsto', '>=', today())
                ->orderBy('inicio_previsto', 'asc')
                ->limit(5)
                ->get();
        }

        return view('dashboard', compact(
            'stats', 
            'contratosCriticos', 
            'consultoresAtivos',
            'ultimas_agendas',
            'chartLabels',
            'chartRealizadas',
            'chartAgendadas',
            'chartCanceladas'
        ));
    }
}
