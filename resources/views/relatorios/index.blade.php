<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight">Relatório de Apontamentos</h2>
            </div>
            
            {{-- Card de Filtros com Tailwind --}}
            <div class="bg-white p-6 rounded-xl shadow-md border border-slate-200">
                <h3 class="text-lg font-bold text-slate-800 mb-4">Filtros do Relatório</h3>
                <form action="{{ route('relatorios.gerar') }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        {{-- Período --}}
                        <div>
                            <label for="data_inicio" class="block text-sm font-medium text-gray-700">Período</label>
                            <div class="flex items-center mt-1">
                                <input type="date" id="data_inicio" name="data_inicio" class="form-input w-full rounded-l-md border-gray-300 shadow-sm" value="{{ $filtros['data_inicio'] ?? old('data_inicio', now()->startOfMonth()->format('Y-m-d')) }}" required>
                                <span class="px-3 py-2 border-t border-b border-gray-300 bg-gray-50 text-gray-500">a</span>
                                <input type="date" id="data_fim" name="data_fim" class="form-input w-full rounded-r-md border-gray-300 shadow-sm" value="{{ $filtros['data_fim'] ?? old('data_fim', now()->endOfMonth()->format('Y-m-d')) }}" required>
                            </div>
                        </div>

                        {{-- Cliente (Empresa) --}}
                        <div>
                            <label for="empresa_id" class="block text-sm font-medium text-gray-700">Cliente</label>
                            <select id="empresa_id" name="empresa_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">Todos os Clientes</option>
                                @foreach($clientes as $cliente)
                                    <option value="{{ $cliente->id }}" {{ (isset($filtros['empresa_id']) && $filtros['empresa_id'] == $cliente->id) ? 'selected' : '' }}>{{ $cliente->nome_empresa }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        {{-- Contrato --}}
                        <div>
                            <label for="contrato_id" class="block text-sm font-medium text-gray-700">Contrato</label>
                            <select id="contrato_id" name="contrato_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">Todos os Contratos</option>
                                @foreach($contratos as $contrato)
                                    {{-- CORREÇÃO: Exibindo o número do contrato, que é um campo que existe. --}}
                                    <option value="{{ $contrato->id }}" {{ (isset($filtros['contrato_id']) && $filtros['contrato_id'] == $contrato->id) ? 'selected' : '' }}>
                                        {{ $contrato->numero_contrato ?? "Contrato ID: {$contrato->id}" }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Colaborador --}}
                        <div>
                            <label for="colaborador_id" class="block text-sm font-medium text-gray-700">Colaborador</label>
                            <select id="colaborador_id" name="colaborador_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">Todos os Colaboradores</option>
                                @foreach($colaboradores as $colaborador)
                                    <option value="{{ $colaborador->id }}" {{ (isset($filtros['colaborador_id']) && $filtros['colaborador_id'] == $colaborador->id) ? 'selected' : '' }}>{{ $colaborador->nome }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Status --}}
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                            <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">Todos os Status</option>
                                <option value="Aprovado" {{ (isset($filtros['status']) && $filtros['status'] == 'Aprovado') ? 'selected' : '' }}>Aprovado</option>
                                <option value="Pendente" {{ (isset($filtros['status']) && $filtros['status'] == 'Pendente') ? 'selected' : '' }}>Pendente</option>
                                <option value="Reprovado" {{ (isset($filtros['status']) && $filtros['status'] == 'Reprovado') ? 'selected' : '' }}>Reprovado</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex items-center justify-end mt-6 border-t pt-4">
                        <a href="{{ route('relatorios.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900 mr-4">Limpar Filtros</a>
                        <button type="submit" name="formato" value="html" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">Gerar Relatório</button>
                        <button type="submit" name="formato" value="pdf" class="ml-3 inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700">Gerar PDF</button>
                        <button type="submit" name="formato" value="excel" class="ml-3 inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700">Gerar Excel</button>
                    </div>
                </form>
            </div>

            {{-- Seção de Resultados --}}
            @if(isset($apontamentos))
                @if($apontamentos->isEmpty())
                    <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 rounded-md" role="alert">
                        <p class="font-bold">Nenhum resultado encontrado</p>
                        <p>Nenhum apontamento corresponde aos filtros selecionados.</p>
                    </div>
                @else
                    <!-- KPIs -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div class="bg-indigo-500 text-white p-6 rounded-lg shadow-lg">
                            <p class="text-sm font-medium text-indigo-100">Total de Horas Aprovadas</p>
                            <p class="mt-1 text-4xl font-bold">{{ number_format($kpis['total_horas'], 1, ',', '.') }}h</p>
                        </div>
                        <div class="bg-blue-500 text-white p-6 rounded-lg shadow-lg">
                            <p class="text-sm font-medium text-blue-100">Total de Apontamentos</p>
                            <p class="mt-1 text-4xl font-bold">{{ $kpis['total_apontamentos'] }}</p>
                        </div>
                        <div class="bg-green-500 text-white p-6 rounded-lg shadow-lg">
                            <p class="text-sm font-medium text-green-100">Apontamentos Aprovados</p>
                            <p class="mt-1 text-4xl font-bold">{{ $kpis['total_aprovados'] }}</p>
                        </div>
                        <div class="bg-purple-500 text-white p-6 rounded-lg shadow-lg">
                            <p class="text-sm font-medium text-purple-100">Média Horas / Apont. Aprovado</p>
                            <p class="mt-1 text-4xl font-bold">{{ number_format($kpis['media_horas'], 1, ',', '.') }}h</p>
                        </div>
                    </div>

                    <!-- Gráficos -->
                    <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
                        <div class="lg:col-span-3 bg-white p-6 rounded-xl shadow-md border border-slate-200">
                            <h3 class="text-lg font-bold text-slate-800 mb-4">Horas Aprovadas por Cliente</h3>
                            <canvas id="clienteChart"></canvas>
                        </div>
                        <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-md border border-slate-200">
                            <h3 class="text-lg font-bold text-slate-800 mb-4">Distribuição de Horas por Consultor</h3>
                            <canvas id="consultorChart"></canvas>
                        </div>
                    </div>

                    <!-- Tabela Detalhada -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h3 class="text-lg font-bold text-slate-800 mb-4">Todos os Apontamentos</h3>
                            @include('relatorios.partials.detalhado', ['resultados' => $apontamentos, 'totalGeralHoras' => $kpis['total_horas']])
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        @if(isset($apontamentos) && !$apontamentos->isEmpty() && $kpis['total_aprovados'] > 0)
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const clienteCtx = document.getElementById('clienteChart');
                if (clienteCtx) {
                    new Chart(clienteCtx, {
                        type: 'bar',
                        data: {
                            labels: @json($horasPorCliente->keys()),
                            datasets: [{
                                label: 'Horas Aprovadas',
                                data: @json($horasPorCliente->values()),
                                backgroundColor: 'rgba(79, 70, 229, 0.8)',
                                borderColor: 'rgba(79, 70, 229, 1)',
                                borderWidth: 1
                            }]
                        },
                        options: { indexAxis: 'y', responsive: true, plugins: { legend: { display: false } } }
                    });
                }

                const consultorCtx = document.getElementById('consultorChart');
                if(consultorCtx) {
                    new Chart(consultorCtx, {
                        type: 'doughnut',
                        data: {
                            labels: @json($horasPorConsultor->keys()),
                            datasets: [{
                                label: 'Horas',
                                data: @json($horasPorConsultor->values()),
                                backgroundColor: ['#4f46e5', '#6366f1', '#818cf8', '#a5b4fc', '#c7d2fe', '#10b981', '#f59e0b'],
                            }]
                        },
                        options: { responsive: true, plugins: { legend: { position: 'top' } } }
                    });
                }
            });
        </script>
        @endif
    @endpush
</x-app-layout>
