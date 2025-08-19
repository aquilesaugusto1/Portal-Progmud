<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Relatório de Alocação de Consultores
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <h3 class="text-lg font-medium text-gray-900">Filtros do Relatório</h3>
                <form action="{{ route('relatorios.gerar') }}" method="POST" class="mt-6 space-y-6">
                    @csrf
                    <input type="hidden" name="tipo_relatorio" value="alocacao-consultores">
                    
                    <div>
                        <x-input-label for="data_inicio" value="Período de Análise" />
                        <div class="flex items-center space-x-2 mt-1">
                            <x-text-input id="data_inicio" name="data_inicio" type="date" class="block w-full" :value="isset($filtros['data_inicio']) ? $filtros['data_inicio'] : now()->startOfMonth()->format('Y-m-d')" required />
                            <span class="text-gray-500">até</span>
                            <x-text-input id="data_fim" name="data_fim" type="date" class="block w-full" :value="isset($filtros['data_fim']) ? $filtros['data_fim'] : now()->endOfMonth()->format('Y-m-d')" required />
                        </div>
                        <div class="flex space-x-2 mt-2">
                            <x-secondary-button type="button" id="btnMesPassado">Mês Passado</x-secondary-button>
                            <x-secondary-button type="button" id="btnEsteMes">Este Mês</x-secondary-button>
                            <x-secondary-button type="button" id="btnProximoMes">Próximo Mês</x-secondary-button>
                        </div>
                    </div>

                    <div>
                        <x-input-label value="Consultores (PJ)" />
                        <div class="mt-2 border rounded-md p-4 max-h-60 overflow-y-auto space-y-2">
                            <div class="flex items-center">
                                <input type="checkbox" id="selecionar-todos" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <label for="selecionar-todos" class="ml-2 text-sm font-medium text-gray-900">Selecionar Todos</label>
                            </div>
                            <hr>
                            @foreach($consultores as $consultor)
                                <div class="flex items-center">
                                    <input type="checkbox" name="consultores_id[]" value="{{ $consultor->id }}" id="consultor_{{ $consultor->id }}" 
                                           class="consultor-checkbox rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                           {{ (isset($filtros['consultores_id']) && in_array($consultor->id, $filtros['consultores_id'])) ? 'checked' : '' }}>
                                    <label for="consultor_{{ $consultor->id }}" class="ml-2 text-sm text-gray-700">{{ $consultor->nome }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <x-primary-button type="submit" name="formato" value="html">{{ __('Gerar Relatório') }}</x-primary-button>
                        <x-primary-button type="submit" name="formato" value="pdf" class="bg-red-600 hover:bg-red-700 focus:bg-red-700 active:bg-red-800 focus:ring-red-500">{{ __('Gerar PDF') }}</x-primary-button>
                    </div>
                </form>
            </div>

            @if(isset($resultados))
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Resultados da Alocação</h3>
                    <p class="text-sm text-gray-600 mb-4">
                        Período de <strong>{{ \Carbon\Carbon::parse($filtros['data_inicio'])->format('d/m/Y') }}</strong> a <strong>{{ \Carbon\Carbon::parse($filtros['data_fim'])->format('d/m/Y') }}</strong> 
                        contém <strong>{{ $dias_uteis }} dias úteis</strong>.
                    </p>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Consultor</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Horas Apontadas</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Nº de Agendas</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Horas Úteis no Período</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Horas Úteis Restantes</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($resultados as $resultado)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $resultado['consultor']->nome }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">{{ number_format($resultado['horas_apontadas'], 2, ',', '.') }}h</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">{{ $resultado['numero_agendas'] }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">{{ number_format($resultado['horas_uteis_periodo'], 2, ',', '.') }}h</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-right @if($resultado['horas_uteis_restantes'] < 0) text-red-600 @else text-green-600 @endif">
                                        {{ number_format($resultado['horas_uteis_restantes'], 2, ',', '.') }}h
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                        <button type="button" class="btn-detalhes text-indigo-600 hover:text-indigo-900"
                                                data-consultor-id="{{ $resultado['consultor']->id }}"
                                                data-consultor-nome="{{ $resultado['consultor']->nome }}">
                                            Detalhes
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">Nenhum resultado encontrado.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Modal de Detalhes -->
    <div id="detalhesModal" class="fixed z-50 inset-0 overflow-y-auto hidden">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div id="modal-overlay" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Detalhes dos Apontamentos</h3>
                        <button type="button" id="closeModalButton" class="text-gray-400 hover:text-gray-500">
                            <span class="sr-only">Fechar</span>
                            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                    <div id="modal-body" class="mt-4 max-h-[60vh] overflow-y-auto">
                        <!-- Conteúdo dinâmico aqui -->
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" id="cancelModalButton" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:w-auto sm:text-sm">
                        Fechar
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const dataInicioEl = document.getElementById('data_inicio');
            const dataFimEl = document.getElementById('data_fim');

            function formatDate(date) {
                return date.toISOString().split('T')[0];
            }

            document.getElementById('btnEsteMes').addEventListener('click', function () {
                const hoje = new Date();
                dataInicioEl.value = formatDate(new Date(hoje.getFullYear(), hoje.getMonth(), 1));
                dataFimEl.value = formatDate(new Date(hoje.getFullYear(), hoje.getMonth() + 1, 0));
            });

            document.getElementById('btnMesPassado').addEventListener('click', function () {
                const hoje = new Date();
                dataInicioEl.value = formatDate(new Date(hoje.getFullYear(), hoje.getMonth() - 1, 1));
                dataFimEl.value = formatDate(new Date(hoje.getFullYear(), hoje.getMonth(), 0));
            });
            
            document.getElementById('btnProximoMes').addEventListener('click', function () {
                const hoje = new Date();
                dataInicioEl.value = formatDate(new Date(hoje.getFullYear(), hoje.getMonth() + 1, 1));
                dataFimEl.value = formatDate(new Date(hoje.getFullYear(), hoje.getMonth() + 2, 0));
            });

            const selecionarTodos = document.getElementById('selecionar-todos');
            const checkboxes = document.querySelectorAll('.consultor-checkbox');
            if (selecionarTodos) {
                selecionarTodos.addEventListener('change', function (e) {
                    checkboxes.forEach(checkbox => {
                        checkbox.checked = e.target.checked;
                    });
                });
            }

            const modal = document.getElementById('detalhesModal');
            const modalTitle = document.getElementById('modal-title');
            const modalBody = document.getElementById('modal-body');
            const closeModalButton = document.getElementById('closeModalButton');
            const cancelModalButton = document.getElementById('cancelModalButton');
            const modalOverlay = document.getElementById('modal-overlay');

            const hideModal = () => modal.classList.add('hidden');

            closeModalButton.addEventListener('click', hideModal);
            cancelModalButton.addEventListener('click', hideModal);
            modalOverlay.addEventListener('click', hideModal);

            document.querySelectorAll('.btn-detalhes').forEach(button => {
                button.addEventListener('click', function() {
                    const consultorId = this.dataset.consultorId;
                    const consultorNome = this.dataset.consultorNome;
                    const dataInicio = dataInicioEl.value;
                    const dataFim = dataFimEl.value;

                    modalTitle.textContent = `Detalhes dos Apontamentos de ${consultorNome}`;
                    modalBody.innerHTML = '<p class="text-center text-gray-500">Carregando...</p>';
                    modal.classList.remove('hidden');

                    const url = `/relatorios/detalhes-apontamentos?consultor_id=${consultorId}&data_inicio=${dataInicio}&data_fim=${dataFim}`;

                    fetch(url)
                        .then(async response => {
                            if (!response.ok) {
                                const errorData = await response.json().catch(() => null);
                                throw { status: response.status, data: errorData };
                            }
                            return response.json();
                        })
                        .then(data => {
                            let content = '<div class="space-y-4">';
                            if (data.length > 0) {
                                data.forEach(apontamento => {
                                    const dataFormatada = new Date(apontamento.data_apontamento).toLocaleDateString('pt-BR', { timeZone: 'UTC' });
                                    content += `
                                        <div class="p-4 border rounded-md shadow-sm bg-gray-50">
                                            <div class="flex justify-between items-start">
                                                <div>
                                                    <p class="font-bold text-gray-800">${apontamento.contrato?.empresa_parceira?.nome_empresa || 'N/A'}</p>
                                                    <p class="text-sm text-gray-600">${apontamento.agenda?.assunto || 'Atividade não especificada'}</p>
                                                </div>
                                                <div class="text-right">
                                                    <p class="text-sm font-semibold text-gray-800">${dataFormatada}</p>
                                                    <p class="text-sm text-gray-600">${parseFloat(apontamento.horas_gastas).toFixed(2).replace('.',',')}h</p>
                                                </div>
                                            </div>
                                            <p class="mt-2 text-sm text-gray-700 whitespace-pre-wrap">${apontamento.descricao || 'Sem descrição.'}</p>
                                        </div>
                                    `;
                                });
                            } else {
                                content += '<p class="text-center text-gray-500">Nenhum apontamento encontrado para este consultor no período selecionado.</p>';
                            }
                            content += '</div>';
                            modalBody.innerHTML = content;
                        })
                        .catch(error => {
                            console.error('Erro detalhado ao buscar detalhes:', error);
                            let errorMsg = 'Ocorreu um erro ao carregar os detalhes. Tente novamente.';
                            if (error.data && error.data.errors) {
                                errorMsg += '<br><pre class="mt-2 text-left text-xs bg-red-50 p-2 rounded"><code>' + JSON.stringify(error.data.errors, null, 2) + '</code></pre>';
                            }
                            modalBody.innerHTML = `<div class="text-center text-red-500">${errorMsg}</div>`;
                        });
                });
            });
        });
    </script>
    @endpush
</x-app-layout>
