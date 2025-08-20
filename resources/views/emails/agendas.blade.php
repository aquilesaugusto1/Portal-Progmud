<x-mail::message>
# Olá!

{{ $recado }}

Aqui está um resumo da sua agenda de atividades para o período selecionado.

@foreach($agendas as $agenda)
<x-mail::panel>
**Assunto:** {{ $agenda->assunto }}<br>
{{-- CORREÇÃO: Usando as novas colunas 'data' e 'hora_inicio' --}}
**Data e Hora:** {{ $agenda->data->format('d/m/Y') }} às {{ \Carbon\Carbon::parse($agenda->hora_inicio)->format('H:i') }}<br>
**Cliente:** {{ $agenda->contrato->empresaParceira->nome_empresa ?? 'N/A' }}<br>
**Contrato:** {{ $agenda->contrato->numero_contrato ?? 'N/A' }}<br>
**Status:** {{ $agenda->status }}
</x-mail::panel>
@endforeach

<x-mail::button :url="route('agendas.index')">
Acessar Minhas Agendas
</x-mail::button>

Qualquer dúvida, por favor, entre em contato.

Atenciosamente,<br>
**{{ $remetente->nome }}**<br>
{{ config('app.name') }}
</x-mail::message>
