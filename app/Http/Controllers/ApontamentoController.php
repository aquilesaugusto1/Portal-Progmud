<?php

namespace App\Http\Controllers;

use App\Models\Agenda;
use App\Models\Apontamento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ApontamentoController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        return view('apontamentos.index');
    }

    public function getAgendasAsEvents(Request $request)
    {
        $start = Carbon::parse($request->start)->toDateTimeString();
        $end = Carbon::parse($request->end)->toDateTimeString();

        $query = Agenda::with('consultor', 'projeto.empresaParceira', 'apontamento')
                       ->whereBetween('data_hora', [$start, $end]);

        $user = Auth::user();

        if ($user->funcao === 'consultor') {
            $query->where('consultor_id', $user->consultor->id);
        } elseif ($user->funcao === 'techlead') {
            $consultor_ids = $user->consultoresLiderados()->pluck('consultores.id');
            $query->whereIn('consultor_id', $consultor_ids);
        }

        $agendas = $query->get();

        return response()->json($this->formatEvents($agendas));
    }

    public function storeOrUpdate(Request $request)
    {
        $validated = $request->validate([
            'agenda_id' => 'required|exists:agendas,id',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fim' => 'required|date_format:H:i|after:hora_inicio',
            'descricao' => 'required|string|max:1000',
            'anexo' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,jpg,png|max:2048',
        ]);

        $agenda = Agenda::findOrFail($validated['agenda_id']);
        $this->authorize('update', $agenda);

        $apontamento = Apontamento::firstOrNew(['agenda_id' => $agenda->id]);

        if ($apontamento->status === 'Aprovado') {
            return response()->json(['message' => 'Apontamentos aprovados não podem ser alterados.'], 403);
        }

        $inicio = Carbon::createFromTimeString($validated['hora_inicio']);
        $fim = Carbon::createFromTimeString($validated['hora_fim']);
        
        $apontamento->fill([
            'consultor_id' => $agenda->consultor_id,
            'data_apontamento' => $agenda->data_hora->format('Y-m-d'),
            'hora_inicio' => $validated['hora_inicio'],
            'hora_fim' => $validated['hora_fim'],
            'horas_gastas' => round($fim->diffInMinutes($inicio) / 60, 2),
            'descricao' => $validated['descricao'],
            'status' => 'Pendente',
            'motivo_rejeicao' => null,
        ]);

        if ($request->hasFile('anexo')) {
            if ($apontamento->caminho_anexo) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($apontamento->caminho_anexo);
            }
            $apontamento->caminho_anexo = $request->file('anexo')->store('anexos', 'public');
        }

        $apontamento->save();

        return response()->json(['message' => 'Apontamento salvo e enviado para aprovação!']);
    }

    private function formatEvents($agendas)
    {
        return $agendas->map(function ($agenda) {
            $apontamento = $agenda->apontamento;
            $status = $apontamento->status ?? 'Não Apontado';
            $color = '#3B82F6'; // Azul - Agendada

            if ($agenda->status === 'Cancelada') $color = '#6b7280'; // Cinza
            elseif ($status === 'Pendente') $color = '#F59E0B'; // Amarelo
            elseif ($status === 'Aprovado') $color = '#10B981'; // Verde
            elseif ($status === 'Rejeitado') $color = '#EF4444'; // Vermelho

            return [
                'id' => $agenda->id,
                'title' => $agenda->projeto->empresaParceira->nome_empresa,
                'start' => $agenda->data_hora,
                'color' => $color,
                'extendedProps' => [
                    'consultor' => $agenda->consultor->nome,
                    'assunto' => $agenda->assunto . ' (Projeto: ' . $agenda->projeto->nome_projeto . ')',
                    'status' => $status,
                    'hora_inicio' => $apontamento ? Carbon::parse($apontamento->hora_inicio)->format('H:i') : '',
                    'hora_fim' => $apontamento ? Carbon::parse($apontamento->hora_fim)->format('H:i') : '',
                    'descricao' => $apontamento->descricao ?? '',
                    'anexo_url' => $apontamento && $apontamento->caminho_anexo ? \Illuminate\Support\Facades\Storage::url($apontamento->caminho_anexo) : null,
                    'motivo_rejeicao' => $apontamento->motivo_rejeicao ?? null,
                ]
            ];
        });
    }
}
