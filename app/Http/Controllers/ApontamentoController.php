<?php

namespace App\Http\Controllers;

use App\Models\Agenda;
use App\Models\Apontamento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class ApontamentoController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Apontamento::class);
        return view('apontamentos.index');
    }

    public function events(Request $request)
    {
        $this->authorize('viewAny', Apontamento::class);

        $start = Carbon::parse($request->start)->toDateTimeString();
        $end = Carbon::parse($request->end)->toDateTimeString();
        $user = Auth::user();

        $query = Agenda::with(['consultor', 'contrato.cliente', 'apontamento'])
                       ->whereBetween('inicio_previsto', [$start, $end]);

        if ($user->funcao === 'consultor') {
            $query->where('consultor_id', $user->id);
        } elseif ($user->funcao === 'techlead') {
            $consultor_ids = $user->consultoresLiderados()->pluck('id');
            $query->whereIn('consultor_id', $consultor_ids);
        }

        $agendas = $query->get();
        return response()->json($this->formatEvents($agendas));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'agenda_id' => 'required|exists:agendas,id',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fim' => 'required|date_format:H:i|after:hora_inicio',
            'descricao' => 'required|string|max:1000',
            'anexo' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,jpg,png|max:2048',
        ]);

        $agenda = Agenda::findOrFail($validated['agenda_id']);
        $apontamento = Apontamento::firstOrNew(['agenda_id' => $agenda->id]);

        if ($apontamento->exists) {
            $this->authorize('update', $apontamento);
        } else {
            $this->authorize('create', Apontamento::class);
        }

        $inicio = Carbon::createFromTimeString($validated['hora_inicio']);
        $fim = Carbon::createFromTimeString($validated['hora_fim']);
        
        $dataToSave = [
            'consultor_id' => $agenda->consultor_id,
            'contrato_id' => $agenda->contrato_id,
            'data_apontamento' => $agenda->inicio_previsto->format('Y-m-d'),
            'hora_inicio' => $validated['hora_inicio'],
            'hora_fim' => $validated['hora_fim'],
            'horas_gastas' => round($fim->diffInMinutes($inicio) / 60, 2),
            'descricao' => $validated['descricao'],
            'status' => 'Pendente',
            'motivo_rejeicao' => null,
        ];

        if ($request->hasFile('anexo')) {
            if ($apontamento->caminho_anexo) {
                Storage::disk('public')->delete($apontamento->caminho_anexo);
            }
            $dataToSave['caminho_anexo'] = $request->file('anexo')->store('anexos', 'public');
        }

        $apontamento->fill($dataToSave)->save();

        return response()->json(['message' => 'Apontamento salvo e enviado para aprovação!']);
    }

    public function destroy(Apontamento $apontamento)
    {
        $this->authorize('delete', $apontamento);

        if ($apontamento->caminho_anexo) {
            Storage::disk('public')->delete($apontamento->caminho_anexo);
        }
        $apontamento->delete();

        return response()->json(['message' => 'Apontamento removido com sucesso.']);
    }

    private function formatEvents($agendas)
    {
        return $agendas->map(function ($agenda) {
            $apontamento = $agenda->apontamento;
            $status = 'Não Apontado';
            $color = '#6B7280';

            if ($agenda->status === 'Cancelada') {
                $status = 'Cancelada';
                $color = '#EF4444';
            } elseif ($apontamento) {
                 $status = $apontamento->status;
                 switch ($status) {
                    case 'Pendente': $color = '#F59E0B'; break;
                    case 'Aprovado': $color = '#10B981'; break;
                    case 'Rejeitado': $color = '#EF4444'; break;
                 }
            } else {
                $status = 'Agendada';
                $color = '#3B82F6';
            }

            return [
                'id' => $agenda->id,
                'title' => $agenda->contrato->cliente->nome_empresa,
                'start' => $agenda->inicio_previsto,
                'end' => $agenda->fim_previsto,
                'color' => $color,
                'extendedProps' => [
                    'apontamento_id' => $apontamento->id ?? null,
                    'consultor' => $agenda->consultor->nome,
                    'assunto' => $agenda->assunto,
                    'contrato' => $agenda->contrato->numero_contrato ?? 'N/A',
                    'status' => $status,
                    'hora_inicio' => $apontamento ? Carbon::parse($apontamento->hora_inicio)->format('H:i') : '',
                    'hora_fim' => $apontamento ? Carbon::parse($apontamento->hora_fim)->format('H:i') : '',
                    'descricao' => $apontamento->descricao ?? '',
                    'anexo_url' => $apontamento && $apontamento->caminho_anexo ? Storage::url($apontamento->caminho_anexo) : null,
                    'motivo_rejeicao' => $apontamento->motivo_rejeicao ?? null,
                ]
            ];
        });
    }
}
