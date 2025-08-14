<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Relatório: Histórico de Tech Leads
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <h3 class="text-lg font-medium text-gray-900">Filtros do Relatório</h3>
                <form action="{{ route('relatorios.gerar') }}" method="POST" class="mt-6 space-y-6">
                    @csrf
                    <input type="hidden" name="tipo_relatorio" value="historico-techleads">
                    
                    <div>
                        <x-input-label for="contrato_id" value="Selecione o Contrato" />
                        <select name="contrato_id" id="contrato_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            <option value="">Selecione um contrato</option>
                            @foreach($contratos as $contrato)
                                <option value="{{ $contrato->id }}" {{ (isset($filtros['contrato_id']) && $filtros['contrato_id'] == $contrato->id) ? 'selected' : '' }}>
                                    {{ $contrato->numero_contrato }} - {{ $contrato->empresaParceira->nome_empresa }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-center gap-4">
                        <x-primary-button type="submit" name="formato" value="html">{{ __('Gerar Relatório') }}</x-primary-button>
                        <x-primary-button type="submit" name="formato" value="pdf" class="bg-red-600 hover:bg-red-700 focus:bg-red-700 active:bg-red-800 focus:ring-red-500">{{ __('Gerar PDF') }}</x-primary-button>
                    </div>
                </form>
            </div>

            @if(isset($resultados))
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <h3 class="text-lg font-medium text-gray-900 mb-1">Resultados para o Contrato: {{ $resultados['contrato']->numero_contrato }}</h3>
                    <p class="text-sm text-gray-600 mb-4">Cliente: {{ $resultados['contrato']->empresaParceira->nome_empresa }}</p>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome do Tech Lead</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Data de Início</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Data de Fim</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($resultados['historico'] as $entrada)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $entrada->tech_lead_nome }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">{{ \Carbon\Carbon::parse($entrada->data_inicio)->format('d/m/Y') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                        {{ $entrada->data_fim ? \Carbon\Carbon::parse($entrada->data_fim)->format('d/m/Y') : 'Atual' }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">Nenhum histórico de Tech Leads encontrado para este contrato.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
