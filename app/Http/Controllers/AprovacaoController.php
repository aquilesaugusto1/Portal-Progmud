<?php

namespace App\Http\Controllers;

use App\Models\Apontamento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        $validated = $request->validate([
            'faturado' => 'required|boolean',
        ]);
        
        $apontamento->status = 'Aprovado';
        $apontamento->faturado = $validated['faturado'];
        $apontamento->aprovado_por_id = Auth::id();
        $apontamento->data_aprovacao = now();
        $apontamento->motivo_rejeicao = null;
        $apontamento->save();
        
        $message = $validated['faturado'] 
            ? 'Apontamento aprovado e faturado com sucesso!' 
            : 'Apontamento aprovado com sucesso (horas não faturadas).';

        return redirect()->route('aprovacoes.index')->with('success', $message);
    }

    public function rejeitar(Request $request, Apontamento $apontamento)
    {
        $this->authorize('approve', $apontamento);
        
        $validated = $request->validate(['motivo_rejeicao' => 'required|string|max:500']);

        $apontamento->status = 'Rejeitado';
        $apontamento->faturado = false;
        $apontamento->motivo_rejeicao = $validated['motivo_rejeicao'];
        $apontamento->aprovado_por_id = Auth::id();
        $apontamento->data_aprovacao = now();
        $apontamento->save();

        return redirect()->route('aprovacoes.index')->with('success', 'Apontamento rejeitado com sucesso.');
    }
}
