<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Relatório de Apontamentos</title>
    <style>
        body { font-family: sans-serif; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Relatório de Apontamentos</h1>
    <p>Período de {{ \Carbon\Carbon::parse($filtros['data_inicio'])->format('d/m/Y') }} a {{ \Carbon\Carbon::parse($filtros['data_fim'])->format('d/m/Y') }}</p>

    <table>
        <thead>
            <tr>
                <th>Data</th>
                <th>Consultor</th>
                <th>Cliente</th>
                <th>Projeto</th>
                <th>Horas</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($apontamentos as $apontamento)
                <tr>
                    <td>{{ $apontamento->data_apontamento->format('d/m/Y') }}</td>
                    <td>{{ $apontamento->consultor->nome }}</td>
                    <td>{{ $apontamento->agenda->projeto->empresaParceira->nome_empresa }}</td>
                    <td>{{ $apontamento->agenda->projeto->nome_projeto }}</td>
                    <td>{{ number_format($apontamento->horas_gastas, 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center;">Nenhum apontamento encontrado.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" style="text-align: right; font-weight: bold;">Total de Horas:</td>
                <td style="font-weight: bold;">{{ number_format($apontamentos->sum('horas_gastas'), 2, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>