{{-- resources/views/relatorios/partials/detalhado.blade.php --}}
<table class="min-w-full divide-y divide-gray-200 mb-4">
    <thead class="bg-gray-50">
        <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Consultor</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descrição</th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Horas</th>
        </tr>
    </thead>
    <tbody class="bg-white divide-y divide-gray-200">
        @forelse ($resultados as $apontamento)
            <tr>
                <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $apontamento->data_apontamento->format('d/m/Y') }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $apontamento->consultor->nome }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $apontamento->agenda->empresaParceira->nome_empresa }}</td>
                <td class="px-6 py-4 text-sm">{{ Str::limit($apontamento->descricao, 50) }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-right">{{ number_format($apontamento->horas_gastas, 2, ',', '.') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">Nenhum apontamento encontrado para os filtros selecionados.</td>
            </tr>
        @endforelse
    </tbody>
    <tfoot class="bg-gray-100">
        <tr>
            <td colspan="4" class="px-6 py-3 text-right text-sm font-bold text-gray-700 uppercase">Total Geral de Horas:</td>
            <td class="px-6 py-3 text-right text-sm font-bold text-gray-700">{{ number_format($totalGeralHoras, 2, ',', '.') }}</td>
        </tr>
    </tfoot>
</table>