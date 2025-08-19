@component('mail::message')
# Apontamento Rejeitado

Olá, **{{ $apontamento->consultor->nome ?? 'Consultor' }}**.

Seu apontamento de horas foi revisado e precisa de sua atenção.

@component('mail::panel')
**Motivo da Rejeição:**<br>
{{ $apontamento->motivo_rejeicao }}
@endcomponent

**Detalhes do Apontamento:**

| | |
|:---|:---|
| **Cliente** | {{ $apontamento->contrato->empresaParceira->nome_empresa ?? 'N/A' }} |
| **Data** | {{ $apontamento->agenda ? \Carbon\Carbon::parse($apontamento->agenda->data)->format('d/m/Y') : 'N/A' }} |
| **Horas** | {{ number_format($apontamento->horas_gastas, 2) }}h |
| **Atividade** | {{ $apontamento->agenda->assunto ?? 'N/A' }} |

Por favor, acesse o portal para fazer as correções necessárias.

@component('mail::button', ['url' => route('apontamentos.index')])
Acessar Portal
@endcomponent

Atenciosamente,<br>
{{ config('app.name') }}
@endcomponent
