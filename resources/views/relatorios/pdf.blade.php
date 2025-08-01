<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Apontamentos</title>
    <style>
        @page {
            margin: 100px 40px 60px 40px;
        }
        body { 
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
            color: #333;
        }
        header {
            position: fixed;
            top: -80px;
            left: 0px;
            right: 0px;
            height: 60px;
            line-height: 35px;
            text-align: center;
            border-bottom: 1px solid #ccc;
        }
        header img {
            height: 40px;
        }
        footer {
            position: fixed; 
            bottom: -40px; 
            left: 0px; 
            right: 0px;
            height: 30px; 
            font-size: 9px;
            text-align: center;
            line-height: 25px;
            border-top: 1px solid #ccc;
        }
        .pagenum:before {
            content: counter(page);
        }
        .report-title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .filters-summary {
            margin-bottom: 20px;
            border: 1px solid #eee;
            padding: 10px;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .filters-summary h4 {
            margin-top: 0;
            margin-bottom: 10px;
            font-size: 12px;
            font-weight: bold;
        }
        .filters-summary p {
            margin: 0 0 5px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
            vertical-align: top;
            word-wrap: break-word;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .total-row {
            font-weight: bold;
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <header>
        <img src="{{ public_path('images/logo-agen.png') }}" alt="Logo">
    </header>

    <footer>
        Relatório gerado em {{ date('d/m/Y H:i') }} | Página <span class="pagenum"></span>
    </footer>

    <main>
        <div class="report-title">Relatório de Apontamentos</div>

        @if(isset($filtros))
        <div class="filters-summary">
            <h4>Filtros Aplicados</h4>
            <p>
                <strong>Período:</strong>
                {{ isset($filtros['data_inicio']) ? \Carbon\Carbon::parse($filtros['data_inicio'])->format('d/m/Y') : 'N/A' }} a
                {{ isset($filtros['data_fim']) ? \Carbon\Carbon::parse($filtros['data_fim'])->format('d/m/Y') : 'N/A' }}
            </p>
            @if(isset($filtros['colaborador_id']) && $filtros['colaborador_id'])
                <p><strong>Consultor:</strong> {{ \App\Models\User::find($filtros['colaborador_id'])->nome ?? 'N/A' }}</p>
            @endif
            @if(isset($filtros['empresa_id']) && $filtros['empresa_id'])
                <p><strong>Cliente:</strong> {{ \App\Models\EmpresaParceira::find($filtros['empresa_id'])->razao_social ?? 'N/A' }}</p>
            @endif
            @if(isset($filtros['contrato_id']) && $filtros['contrato_id'])
                @php
                    $contratoFiltro = \App\Models\Contrato::find($filtros['contrato_id']);
                @endphp
                <p><strong>Contrato:</strong> {{ $contratoFiltro ? $contratoFiltro->codigo_contrato . ' - ' . $contratoFiltro->nome_contrato : 'N/A' }}</p>
            @endif
            @if(isset($filtros['status']) && $filtros['status'])
                <p><strong>Status:</strong> {{ $filtros['status'] }}</p>
            @endif
        </div>
        @endif

        <table>
            <thead>
                <tr>
                    <th style="width:10%;">Data</th>
                    <th style="width:20%;">Consultor</th>
                    <th style="width:25%;">Cliente</th>
                    <th style="width:25%;">Contrato</th>
                    <th style="width:10%;">Horas</th>
                    <th style="width:10%;">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($apontamentos as $apontamento)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($apontamento->data)->format('d/m/Y') }}</td>
                        <td>{{ $apontamento->colaborador->nome ?? 'N/A' }}</td>
                        <td>{{ $apontamento->agenda->contrato->empresaParceira->razao_social ?? 'N/A' }}</td>
                        <td>{{ $apontamento->agenda->contrato->codigo_contrato ?? '' }} - {{ $apontamento->agenda->contrato->nome_contrato ?? 'N/A' }}</td>
                        <td>{{ $apontamento->horas_aprovadas ?? '00:00' }}</td>
                        <td>{{ $apontamento->status }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center;">Nenhum apontamento encontrado.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="4" style="text-align: right;">Total de Horas Aprovadas:</td>
                    <td colspan="2">
                        @php
                            $totalSegundos = 0;
                            foreach ($apontamentos as $apontamento) {
                                if($apontamento->status == 'Aprovado' && !empty($apontamento->horas_aprovadas)) {
                                    $partes = explode(':', $apontamento->horas_aprovadas);
                                    $totalSegundos += ((int)($partes[0] ?? 0) * 3600) + ((int)($partes[1] ?? 0) * 60);
                                }
                            }
                            $horas = floor($totalSegundos / 3600);
                            $minutos = floor(($totalSegundos % 3600) / 60);
                            echo sprintf('%02d:%02d', $horas, $minutos);
                        @endphp
                    </td>
                </tr>
            </tfoot>
        </table>
    </main>
</body>
</html>
