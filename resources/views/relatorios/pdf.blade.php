<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Apontamentos</title>
    <style>
        @page { margin: 120px 50px 80px 50px; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #333; font-size: 10px; }
        header { position: fixed; top: -100px; left: 0; right: 0; height: 80px; text-align: left; border-bottom: 2px solid #0d6efd; }
        header img { height: 50px; float: left; }
        header .header-text { float: right; text-align: right; }
        header h1 { margin: 0; font-size: 24px; color: #0d6efd; }
        header p { margin: 5px 0 0; font-size: 12px; }
        footer { position: fixed; bottom: -60px; left: 0; right: 0; height: 50px; text-align: center; border-top: 1px solid #ddd; font-size: 9px; }
        footer .pagenum:before { content: counter(page); }
        .content-title { font-size: 18px; font-weight: bold; margin-bottom: 20px; color: #333; }
        .filters-summary { margin-bottom: 25px; border-left: 4px solid #0dcaf0; padding: 10px 15px; background-color: #f8f9fa; }
        .filters-summary h5 { margin: 0 0 10px; font-size: 12px; font-weight: bold; color: #0dcaf0; }
        .filters-summary p { margin: 0; padding: 2px 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        tbody tr:nth-child(even) { background-color: #f9f9f9; }
        .total-section { margin-top: 30px; text-align: right; }
        .total-box { display: inline-block; padding: 15px; background-color: #e9ecef; border: 1px solid #dee2e6; text-align: center; }
        .total-box .label { font-size: 12px; font-weight: bold; display: block; }
        .total-box .value { font-size: 20px; font-weight: bold; color: #0d6efd; }
    </style>
</head>
<body>
    <header>
        <img src="{{ public_path('images/logo-agen.png') }}" alt="Logo">
        <div class="header-text">
            <h1>Relatório de Apontamentos</h1>
            <p>Gerado em: {{ now()->format('d/m/Y H:i') }}</p>
        </div>
    </header>

    <footer>
        Relatório de Apontamentos | {{ config('app.name', 'Laravel') }} | Página <span class="pagenum"></span>
    </footer>

    <main>
        @if(isset($filtros))
        <div class="filters-summary">
            <h5>FILTROS APLICADOS</h5>
            <p><strong>Período:</strong> {{ \Carbon\Carbon::parse($filtros['data_inicio'])->format('d/m/Y') }} a {{ \Carbon\Carbon::parse($filtros['data_fim'])->format('d/m/Y') }}</p>
            @if($filtros['empresa_id'] ?? null) <p><strong>Cliente:</strong> {{ \App\Models\EmpresaParceira::find($filtros['empresa_id'])->nome_empresa ?? 'N/A' }}</p> @endif
            @if($filtros['contrato_id'] ?? null) <p><strong>Contrato:</strong> {{ \App\Models\Contrato::find($filtros['contrato_id'])->numero_contrato ?? 'N/A' }}</p> @endif
            @if($filtros['colaborador_id'] ?? null) <p><strong>Consultor:</strong> {{ \App\Models\User::find($filtros['colaborador_id'])->nome ?? 'N/A' }}</p> @endif
            @if($filtros['status'] ?? null) <p><strong>Status:</strong> {{ $filtros['status'] }}</p> @endif
        </div>
        @endif

        <div class="content-title">Detalhes dos Apontamentos</div>
        <table>
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Consultor</th>
                    <th>Cliente</th>
                    <th>Contrato</th>
                    <th style="text-align: right;">Horas Gastas</th>
                    <th style="text-align: center;">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($apontamentos as $apontamento)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($apontamento->data_apontamento)->format('d/m/Y') }}</td>
                        <td>{{ $apontamento->consultor->nome ?? 'N/A' }}</td>
                        <td>{{ $apontamento->contrato->cliente->nome_empresa ?? 'N/A' }}</td>
                        <td>{{ $apontamento->contrato->numero_contrato ?? 'N/A' }}</td>
                        <td style="text-align: right;">{{ $apontamento->horas_gastas ?? '00:00' }}</td>
                        <td style="text-align: center;">{{ $apontamento->status }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 20px;">Nenhum apontamento encontrado para os filtros selecionados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($apontamentosAprovados->isNotEmpty())
        <div class="total-section">
             @php
                $totalSegundos = $apontamentosAprovados->reduce(function ($carry, $apontamento) {
                    if (empty($apontamento->horas_gastas)) return $carry;
                    $partes = explode(':', $apontamento->horas_gastas);
                    return $carry + ((int)($partes[0] ?? 0) * 3600) + ((int)($partes[1] ?? 0) * 60);
                }, 0);
                $horas = floor($totalSegundos / 3600);
                $minutos = floor(($totalSegundos % 3600) / 60);
                $totalFormatado = sprintf('%02d:%02d', $horas, $minutos);
            @endphp
            <div class="total-box">
                <span class="label">TOTAL DE HORAS APROVADAS</span>
                <span class="value">{{ $totalFormatado }}</span>
            </div>
        </div>
        @endif
    </main>
</body>
</html>