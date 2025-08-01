<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Contrato;
use App\Models\EmpresaParceira;
use App\Models\Apontamento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ApontamentosExport;
use Barryvdh\DomPDF\Facade\Pdf;

class RelatorioController extends Controller
{
    private function converterHorasParaDecimal($horario)
    {
        if (is_null($horario) || !is_string($horario) || !str_contains($horario, ':')) {
            return 0;
        }
        $partes = explode(':', $horario);
        $horas = (int)($partes[0] ?? 0);
        $minutos = (int)($partes[1] ?? 0);
        return $horas + ($minutos / 60.0);
    }

    private function getFiltroOptions()
    {
        $user = Auth::user();
        $consultores = collect();
        $contratos = collect();
        $empresas = collect();

        if ($user->isAdmin() || $user->isCoordenador()) {
            $consultores = User::where('funcao', 'consultor')->where('status', 'Ativo')->orderBy('nome')->get();
            $contratos = Contrato::with('cliente')->where('status', 'Ativo')->orderBy('numero_contrato')->get();
            $empresas = EmpresaParceira::where('status', 'Ativo')->orderBy('nome_empresa')->get();
        } elseif ($user->isTechLead()) {
            $consultores = $user->consultoresLiderados()->where('status', 'Ativo')->orderBy('nome')->get();
            $consultorIds = $consultores->pluck('id');
            
            $contratos = Contrato::with('cliente')->whereHas('usuarios', function ($query) use ($consultorIds) {
                $query->whereIn('usuarios.id', $consultorIds);
            })->where('status', 'Ativo')->orderBy('numero_contrato')->get();
            
            $empresaIds = $contratos->pluck('cliente_id')->unique();
            $empresas = EmpresaParceira::whereIn('id', $empresaIds)->where('status', 'Ativo')->orderBy('nome_empresa')->get();
        }

        return compact('consultores', 'contratos', 'empresas');
    }

    public function index()
    {
        $filtroOptions = $this->getFiltroOptions();
        return view('relatorios.index', $filtroOptions);
    }

    public function gerar(Request $request)
    {
        $validated = $request->validate([
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date|after_or_equal:data_inicio',
            'colaborador_id' => 'nullable|exists:users,id',
            'contrato_id' => 'nullable|exists:contratos,id',
            'empresa_id' => 'nullable|exists:empresas_parceiras,id',
            'status' => 'nullable|string',
            'formato' => 'required|in:html,pdf,excel'
        ]);

        $user = Auth::user();
        $query = Apontamento::with(['consultor', 'contrato.cliente'])
            ->whereBetween('data_apontamento', [$request->data_inicio, $request->data_fim]);

        if ($request->filled('colaborador_id')) {
            $query->where('consultor_id', $request->colaborador_id);
        }
        if ($request->filled('contrato_id')) {
            $query->where('contrato_id', $request->contrato_id);
        }
        if ($request->filled('empresa_id')) {
            $query->whereHas('contrato', function ($q) use ($request) {
                $q->where('cliente_id', $request->empresa_id);
            });
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($user->isTechLead()) {
            $lideradosIds = $user->consultoresLiderados()->pluck('usuarios.id');
            $query->whereIn('consultor_id', $lideradosIds);
        }
        
        $apontamentos = $query->orderBy('data_apontamento', 'desc')->get();
        $filtros = $request->only(['data_inicio', 'data_fim', 'colaborador_id', 'contrato_id', 'empresa_id', 'status']);
        
        $apontamentosAprovados = $apontamentos->where('status', 'Aprovado');

        if ($request->formato === 'pdf') {
            $pdf = Pdf::loadView('relatorios.pdf', compact('apontamentos', 'filtros', 'apontamentosAprovados'));
            return $pdf->download('relatorio_apontamentos_'.now()->format('Y-m-d').'.pdf');
        }

        if ($request->formato === 'excel') {
            return Excel::download(new ApontamentosExport($apontamentos), 'relatorio_apontamentos_'.now()->format('Y-m-d').'.xlsx');
        }

        $totalHoras = $apontamentosAprovados->reduce(fn($c, $i) => $c + $this->converterHorasParaDecimal($i->horas_gastas), 0);
        
        $kpis = [
            'total_horas' => $totalHoras,
            'total_apontamentos' => $apontamentos->count(),
            'total_aprovados' => $apontamentosAprovados->count(),
            'media_horas' => $apontamentosAprovados->count() > 0 ? $totalHoras / $apontamentosAprovados->count() : 0,
        ];

        $horasPorCliente = $apontamentosAprovados->groupBy('contrato.cliente.nome_empresa')
            ->map(fn($g) => $g->reduce(fn($c, $i) => $c + $this->converterHorasParaDecimal($i->horas_gastas), 0))
            ->sortDesc();

        $horasPorConsultor = $apontamentosAprovados->groupBy('consultor.nome')
            ->map(fn($g) => $g->reduce(fn($c, $i) => $c + $this->converterHorasParaDecimal($i->horas_gastas), 0))
            ->sortDesc();

        $filtroOptions = $this->getFiltroOptions();

        return view('relatorios.index', array_merge(
            compact('apontamentos', 'filtros', 'kpis', 'horasPorCliente', 'horasPorConsultor'),
            $filtroOptions
        ));
    }
}