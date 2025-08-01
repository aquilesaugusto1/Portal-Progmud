<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Apontamento;
use App\Models\User;
use App\Models\Contrato;
use App\Models\EmpresaParceira;
use Illuminate\Support\Facades\Auth;
use App\Exports\ApontamentosExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class RelatorioController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $consultores = collect();
        $contratos = collect();

        if ($user->funcao === 'admin' || str_contains($user->funcao, 'coordenador')) {
            $consultores = User::where('funcao', 'consultor')->where('status', 'Ativo')->orderBy('nome')->get();
            $contratos = Contrato::where('status', 'Ativo')->with('cliente')->get();
        } elseif ($user->funcao === 'techlead') {
            $consultores = $user->consultoresLiderados()->where('status', 'Ativo')->orderBy('nome')->get();
            $consultorIds = $consultores->pluck('id');
            $contratos = Contrato::whereHas('consultores', function ($query) use ($consultorIds) {
                $query->whereIn('usuarios.id', $consultorIds);
            })->where('status', 'Ativo')->with('cliente')->get();
        }

        return view('relatorios.index', compact('consultores', 'contratos'));
    }

    public function gerar(Request $request)
    {
        $validated = $request->validate([
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date|after_or_equal:data_inicio',
            'consultor_id' => 'nullable|exists:usuarios,id',
            'contrato_id' => 'nullable|exists:contratos,id',
            'formato' => 'required|in:html,pdf,excel'
        ]);

        $user = Auth::user();
        $query = Apontamento::with('consultor', 'contrato.cliente')
                            ->where('status', 'Aprovado')
                            ->whereBetween('data_apontamento', [$validated['data_inicio'], $validated['data_fim']]);

        // Aplicar regras de permissão
        if ($user->funcao === 'consultor') {
            $query->where('consultor_id', $user->id);
        } elseif ($user->funcao === 'techlead') {
            $lideradosIds = $user->consultoresLiderados()->pluck('id')->toArray();
            $query->whereIn('consultor_id', $lideradosIds);

            // Garante que o tech lead não filtre por um consultor que não lidera
            if ($request->filled('consultor_id') && !in_array($request->consultor_id, $lideradosIds)) {
                abort(403, 'Ação não autorizada.');
            }
        }

        // Aplicar filtros do formulário
        if ($request->filled('consultor_id')) {
            $query->where('consultor_id', $request->consultor_id);
        }
        if ($request->filled('contrato_id')) {
            $query->where('contrato_id', $request->contrato_id);
        }

        $apontamentos = $query->latest('data_apontamento')->get();
        $filtros = $request->only(['data_inicio', 'data_fim', 'consultor_id', 'contrato_id']);

        if ($request->formato === 'pdf') {
            $pdf = Pdf::loadView('relatorios.pdf', compact('apontamentos', 'filtros'));
            return $pdf->download('relatorio_apontamentos.pdf');
        }

        if ($request->formato === 'excel') {
            return Excel::download(new ApontamentosExport($apontamentos, $filtros), 'relatorio_apontamentos.xlsx');
        }

        $kpis = [
            'total_horas' => $apontamentos->sum('horas_gastas'),
            'total_apontamentos' => $apontamentos->count(),
            'media_horas' => $apontamentos->count() > 0 ? $apontamentos->sum('horas_gastas') / $apontamentos->count() : 0,
        ];

        $horasPorCliente = $apontamentos->groupBy('contrato.cliente.nome_empresa')
            ->map->sum('horas_gastas')->sortDesc();

        $horasPorConsultor = $apontamentos->groupBy('consultor.nome')
            ->map->sum('horas_gastas')->sortDesc();

        return view('relatorios.resultado', compact('apontamentos', 'kpis', 'horasPorCliente', 'horasPorConsultor', 'filtros'));
    }
}
