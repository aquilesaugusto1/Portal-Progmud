<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Projeto;
use App\Models\Consultor;
use App\Models\User;
use App\Models\EmpresaParceira;
use App\Models\Agenda;
use App\Models\Apontamento;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $data = [];

        switch ($user->funcao) {
            case 'admin':
                $data['stats'] = [
                    'Projetos' => Projeto::count(),
                    'Consultores' => Consultor::count(),
                    'Tech Leads' => User::where('funcao', 'techlead')->count(),
                    'Empresas' => EmpresaParceira::count(),
                ];

                $endDate = Carbon::now();
                $startDate = Carbon::now()->subMonths(5)->startOfMonth();
                $dbData = Agenda::select(
                        'status',
                        DB::raw('COUNT(*) as total'),
                        DB::raw("DATE_FORMAT(data_hora, '%Y-%m') as mes")
                    )
                    ->whereBetween('data_hora', [$startDate, $endDate])
                    ->groupBy('mes', 'status')
                    ->orderBy('mes', 'asc')
                    ->get();

                $pivotedData = $dbData->groupBy('mes')->map(function ($monthData) {
                    return $monthData->pluck('total', 'status');
                });

                $labels = [];
                $realizadasValues = [];
                $canceladasValues = [];
                $agendadasValues = [];
                
                for ($i = 0; $i < 6; $i++) {
                    $date = $startDate->copy()->addMonths($i);
                    $monthKey = $date->format('Y-m');
                    $labels[] = $date->format('M/y');
                    
                    $realizadasValues[] = $pivotedData[$monthKey]['Realizada'] ?? 0;
                    $canceladasValues[] = $pivotedData[$monthKey]['Cancelada'] ?? 0;
                    $agendadasValues[] = $pivotedData[$monthKey]['Agendada'] ?? 0;
                }

                $data['chartLabels'] = $labels;
                $data['chartRealizadas'] = $realizadasValues;
                $data['chartCanceladas'] = $canceladasValues;
                $data['chartAgendadas'] = $agendadasValues;

                $data['projetosCriticos'] = Projeto::with('empresaParceira')
                    ->get()
                    ->filter(function ($projeto) {
                        return $projeto->empresaParceira->saldo_total < 10;
                    })
                    ->take(5);
                
                $data['consultoresAtivos'] = Consultor::withCount(['apontamentos as horas_30_dias' => function ($query) {
                        $query->select(DB::raw('sum(horas_gastas)'))
                              ->where('data_apontamento', '>=', Carbon::now()->subDays(30));
                    }])
                    ->orderBy('horas_30_dias', 'desc')
                    ->limit(5)
                    ->get();

                break;

            case 'techlead':
                $consultoresLideradosIds = $user->consultoresLiderados()->pluck('consultores.id');
                $data['stats'] = [
                    'Consultores na Equipa' => $user->consultoresLiderados()->count(),
                    'Projetos Liderados' => $user->projetosLiderados()->count(),
                    'Próximas Agendas da Equipa' => Agenda::whereIn('consultor_id', $consultoresLideradosIds)
                                                         ->where('data_hora', '>=', now())
                                                         ->count(),
                ];
                $data['ultimas_agendas'] = Agenda::with('consultor', 'projeto.empresaParceira')
                                                 ->whereIn('consultor_id', $consultoresLideradosIds)
                                                 ->where('data_hora', '>=', now())
                                                 ->orderBy('data_hora', 'asc')
                                                 ->limit(5)
                                                 ->get();
                break;
            
            case 'consultor':
                $data['stats'] = [
                    'Projetos Ativos' => $user->consultor ? $user->consultor->projetos()->count() : 0,
                    'Próximas Agendas' => $user->consultor ? Agenda::where('consultor_id', $user->consultor->id)
                                                                  ->where('data_hora', '>=', now())
                                                                  ->count() : 0,
                    'Horas Apontadas (30d)' => $user->consultor ? round($user->consultor->apontamentos()
                                                                  ->where('data_apontamento', '>=', now()->subDays(30))
                                                                  ->sum('horas_gastas'), 2) : 0,
                ];
                $data['ultimas_agendas'] = $user->consultor ? Agenda::with('projeto.empresaParceira')
                                                 ->where('consultor_id', $user->consultor->id)
                                                 ->where('data_hora', '>=', now())
                                                 ->orderBy('data_hora', 'asc')
                                                 ->limit(5)
                                                 ->get() : collect();
                break;
        }

        return view('dashboard', $data);
    }
}