<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Relatório de Planilha Semanal de Horas
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <h3 class="text-lg font-medium text-gray-900">Filtros do Relatório</h3>
                <form action="{{ route('relatorios.gerar') }}" method="POST" class="mt-6 space-y-6">
                    @csrf
                    <input type="hidden" name="tipo_relatorio" value="planilha-semanal">
                    <input type="hidden" name="formato" value="html">
                    
                    <div>
                        <x-input-label for="data_selecionada" value="Selecione uma data na semana desejada" />
                        <div class="flex items-center space-x-2 mt-1">
                            <x-text-input id="data_selecionada" name="data_selecionada" type="date" class="block w-full md:w-1/3" :value="$filtros['data_selecionada'] ?? now()->format('Y-m-d')" required />
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <x-primary-button type="submit">{{ __('Gerar Relatório') }}</x-primary-button>
                    </div>
                </form>
            </div>

            @if(isset($resultados))
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Resultados da Planilha Semanal</h3>
                    <p class="text-sm text-gray-600 mb-4">
                        Exibindo resultados para a semana de <strong>{{ $periodo['inicio']->format('d/m/Y') }}</strong> a <strong>{{ $periodo['fim']->format('d/m/Y') }}</strong>.
                    </p>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 border">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-r">Contrato</th>
                                    @foreach($periodo['dias'] as $dia)
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-r">
                                            {{ $dia['nome'] }}<br>{{ $dia['data'] }}
                                        </th>
                                    @endforeach
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-100">Total</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($resultados as $contratoId => $dados)
                                <tr>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900 border-r">{{ $dados['contrato_nome'] }}</td>
                                    @foreach($periodo['dias'] as $dia)
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 text-center border-r">
                                            {{ isset($dados['horas_por_dia'][$dia['data_iso']]) ? number_format($dados['horas_por_dia'][$dia['data_iso']], 2, ',', '.') . 'h' : '-' }}
                                        </td>
                                    @endforeach
                                    <td class="px-4 py-4 whitespace-nowrap text-sm font-bold text-gray-800 text-center bg-gray-50">
                                        {{ number_format($dados['total_horas'], 2, ',', '.') }}h
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="{{ 7 + 2 }}" class="px-6 py-4 text-center text-sm text-gray-500">Nenhum apontamento encontrado para o período selecionado.</td>
                                </tr>
                                @endforelse
                            </tbody>
                             <tfoot class="bg-gray-100 font-bold">
                                <tr>
                                    <td class="px-4 py-3 text-left text-xs text-gray-700 uppercase border-r">Total Geral</td>
                                    @foreach($periodo['dias'] as $dia)
                                        <td class="px-4 py-3 text-center text-xs text-gray-700 uppercase border-r">
                                            {{ number_format($totais_por_dia[$dia['data_iso']] ?? 0, 2, ',', '.') }}h
                                        </td>
                                    @endforeach
                                    <td class="px-4 py-3 text-center text-xs text-gray-700 uppercase">
                                        {{ number_format($total_geral, 2, ',', '.') }}h
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>