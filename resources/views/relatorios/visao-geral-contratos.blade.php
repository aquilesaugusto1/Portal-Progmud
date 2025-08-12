<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Relatório de Visão Geral de Contratos
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            {{-- Formulário de Filtros --}}
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <h3 class="text-lg font-medium text-gray-900">Filtros</h3>
                <form action="{{ route('relatorios.gerar') }}" method="POST" class="mt-4 space-y-4">
                    @csrf
                    <input type="hidden" name="tipo_relatorio" value="visao-geral-contratos">
                    
                    <div>
                        <x-input-label value="Contratos Ativos" />
                        <select name="contratos_id[]" multiple class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" size="10">
                            @foreach($contratos as $contrato)
                                <option value="{{ $contrato->id }}" {{ (isset($filtros['contratos_id']) && in_array($contrato->id, $filtros['contratos_id'])) ? 'selected' : '' }}>
                                    {{ $contrato->numero_contrato }} - {{ $contrato->cliente->nome_empresa }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mt-4">
                        <x-primary-button>Gerar Relatório</x-primary-button>
                    </div>
                </form>
            </div>

            {{-- Seção de Resultados --}}
            @if(isset($resultados))
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Resultados dos Contratos</h3>
                    <div class="space-y-4">
                        @foreach($resultados as $resultado)
                        <div class="border rounded-lg p-4">
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="font-bold text-lg">{{ $resultado['contrato']->numero_contrato }}</p>
                                    <p class="text-sm text-gray-600">{{ $resultado['contrato']->cliente->nome_empresa }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm">Saldo de Horas</p>
                                    <p class="font-bold text-2xl @if($resultado['saldo_horas'] < 10) text-red-500 @else text-green-600 @endif">
                                        {{ number_format($resultado['saldo_horas'], 2) }}h
                                    </p>
                                </div>
                            </div>
                            <div class="mt-4">
                                <div class="w-full bg-gray-200 rounded-full h-4">
                                    <div class="bg-indigo-600 h-4 rounded-full" style="width: {{ $resultado['percentual_gasto'] }}%"></div>
                                </div>
                                <div class="flex justify-between text-xs mt-1">
                                    <span>Gastas: {{ number_format($resultado['horas_gastas'], 2) }}h</span>
                                    <span>Contratadas: {{ number_format($resultado['contrato']->horas_contratadas, 2) }}h</span>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
