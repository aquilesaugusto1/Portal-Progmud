<x-mail::message>
# Olá!

{{ $recado }}

Aqui está um resumo da sua agenda de atividades.

<x-mail::table>
| Data / Hora | Cliente | Contrato | Assunto | Status |
|:----------- |:------- |:-------- |:------- |:------ |
@foreach($agendas as $agenda)
| **{{ $agenda->data_hora->format('d/m/y H:i') }}** | {{ $agenda->contrato->empresaParceira->nome_empresa ?? 'N/A' }} | {{ $agenda->contrato->numero_contrato ?? 'N/A' }} | {{ $agenda->assunto }} | *{{ $agenda->status }}* |
@endforeach
</x-mail::table>

Qualquer dúvida, por favor, entre em contato.

Atenciosamente,<br>
**{{ $remetente->nome }}**<br>
{{ config('app.name') }}
</x-mail::message>
