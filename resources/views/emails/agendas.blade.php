<x-mail::message>
# Olá!

{{ $recado }}

Aqui está um resumo da sua agenda de atividades.

@foreach($agendas as $agenda)
**{{ $agenda->data_hora->format('d/m/Y \à\s H:i') }}** - *{{ $agenda->status }}*
- **Projeto:** {{ $agenda->projeto->nome_projeto }}
- **Cliente:** {{ $agenda->projeto->empresaParceira->nome_empresa }}
- **Assunto:** {{ $agenda->assunto }}
---
@endforeach

Qualquer dúvida, por favor, entre em contato.

Atenciosamente,<br>
**{{ $remetente->nome }}**<br>
{{ config('app.name') }}
</x-mail::message>
