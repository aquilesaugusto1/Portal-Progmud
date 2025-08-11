<?php

namespace App\Http\Controllers;

use App\Models\Apontamento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AprovacaoController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Apontamento::class);

        $user = Auth::user();
        $query = Apontamento::with(['consultor', 'agenda.contrato.cliente'])
                            ->where('status', 'Pendente');

        if ($user->funcao === 'techlead') {
            $consultorIds = $user->consultoresLiderados()->pluck('id');
            $query->whereIn('consultor_id', $consultorIds);
        }

        $apontamentos = $query->latest()->paginate(15);

        return view('aprovacoes.index', compact('apontamentos'));
    }

    public function aprovar(Request $request, Apontamento $apontamento)
    {
        $this->authorize('approve', $apontamento);

        try {
            DB::transaction(function () use ($apontamento) {
                // 1. Atualiza o status do apontamento
                $apontamento->status = 'Aprovado';
                $apontamento->aprovado_por_id = Auth::id();
                $apontamento->data_aprovacao = now();
                $apontamento->motivo_rejeicao = null;
                $apontamento->save();

                // 2. Atualiza o status da agenda relacionada
                if ($apontamento->agenda) {
                    $apontamento->agenda->status = 'Realizada';
                    $apontamento->agenda->save();
                }

                // 3. Se for faturável, deduz as horas do contrato
                if ($apontamento->faturavel && $apontamento->contrato && $apontamento->contrato->baseline_horas_mes !== null) {
                    $horasASubtrair = abs($apontamento->horas_gastas);
                    $apontamento->contrato->decrement('baseline_horas_mes', $horasASubtrair);
                }
            });

            $message = $apontamento->faturavel 
                ? 'Apontamento aprovado e faturado com sucesso!' 
                : 'Apontamento aprovado com sucesso (horas não faturadas).';

            return redirect()->route('aprovacoes.index')->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Erro ao aprovar apontamento: ' . $e->getMessage());
            return back()->withErrors('Ocorreu um erro inesperado ao tentar aprovar o apontamento.');
        }
    }

    public function rejeitar(Request $request, Apontamento $apontamento)
    {
        $this->authorize('approve', $apontamento);
        
        $validated = $request->validate(['motivo_rejeicao' => 'required|string|max:500']);

        $apontamento->status = 'Rejeitado';
        $apontamento->faturavel = false; // CORRIGIDO: de 'faturado' para 'faturavel'
        $apontamento->motivo_rejeicao = $validated['motivo_rejeicao'];
        $apontamento->aprovado_por_id = Auth::id();
        $apontamento->data_aprovacao = now();
        $apontamento->save();

        return redirect()->route('aprovacoes.index')->with('success', 'Apontamento rejeitado com sucesso.');
    }
}