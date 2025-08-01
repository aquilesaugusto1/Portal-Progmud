<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 font-weight-bold">
            <i class="fas fa-chart-line me-2"></i>
            {{ __('Relatório de Apontamentos') }}
        </h2>
    </x-slot>

    {{-- Card de Filtros --}}
    <div class="card my-4 shadow-sm">
        <div class="card-header bg-light border-0 py-3">
            <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filtros do Relatório</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('relatorios.gerar') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="data_inicio" class="form-label">Período</label>
                        <div class="input-group">
                            <input type="date" id="data_inicio" name="data_inicio" class="form-control" value="{{ $filtros['data_inicio'] ?? old('data_inicio', now()->startOfMonth()->format('Y-m-d')) }}" required>
                            <span class="input-group-text">a</span>
                            <input type="date" id="data_fim" name="data_fim" class="form-control" value="{{ $filtros['data_fim'] ?? old('data_fim', now()->endOfMonth()->format('Y-m-d')) }}" required>
                        </div>
                    </div>
                     <div class="col-md-6">
                        <label for="empresa_id" class="form-label">Cliente</label>
                        <select id="empresa_id" name="empresa_id" class="form-select">
                            <option value="">Todos os Clientes</option>
                            @foreach($empresas as $empresa)
                                <option value="{{ $empresa->id }}" @selected(isset($filtros['empresa_id']) && $filtros['empresa_id'] == $empresa->id)>
                                    {{ $empresa->nome_empresa }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="contrato_id" class="form-label">Contrato</label>
                        <select id="contrato_id" name="contrato_id" class="form-select">
                            <option value="">Todos os Contratos</option>
                            @foreach($contratos as $contrato)
                                <option value="{{ $contrato->id }}" @selected(isset($filtros['contrato_id']) && $filtros['contrato_id'] == $contrato->id)>
                                    {{ $contrato->cliente->nome_empresa }} - {{ $contrato->numero_contrato }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="colaborador_id" class="form-label">Consultor</label>
                        <select id="colaborador_id" name="colaborador_id" class="form-select">
                            <option value="">Todos os Consultores</option>
                            @foreach($consultores as $consultor)
                                <option value="{{ $consultor->id }}" @selected(isset($filtros['colaborador_id']) && $filtros['colaborador_id'] == $consultor->id)>
                                    {{ $consultor->nome }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                     <div class="col-md-4">
                        <label for="status" class="form-label">Status do Apontamento</label>
                        <select id="status" name="status" class="form-select">
                            <option value="">Todos</option>
                            <option value="Aprovado" @selected(isset($filtros['status']) && $filtros['status'] == 'Aprovado')>Aprovado</option>
                            <option value="Pendente" @selected(isset($filtros['status']) && $filtros['status'] == 'Pendente')>Pendente</option>
                            <option value="Reprovado" @selected(isset($filtros['status']) && $filtros['status'] == 'Reprovado')>Reprovado</option>
                        </select>
                    </div>
                </div>

                <hr class="my-4">

                <div class="d-flex justify-content-end">
                    <button type="submit" name="formato" value="html" class="btn btn-primary btn-lg me-2">
                        <i class="fas fa-search me-1"></i> Gerar Relatório
                    </button>
                    <button type="submit" name="formato" value="pdf" class="btn btn-outline-danger me-2">
                        <i class="fas fa-file-pdf me-1"></i> Gerar PDF
                    </button>
                    <button type="submit" name="formato" value="excel" class="btn btn-outline-success">
                        <i class="fas fa-file-excel me-1"></i> Gerar Excel
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Seção de Resultados --}}
    @if(isset($apontamentos))
    <div class="mt-5">
        {{-- KPIs --}}
        <div class="row">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card text-white bg-primary shadow-lg">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="card-title">Horas Aprovadas</div>
                                <div class="h2 fw-bold">{{ number_format($kpis['total_horas'], 2) }}h</div>
                            </div>
                            <i class="fas fa-clock fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card text-white bg-info shadow-lg">
                    <div class="card-body">
                         <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="card-title">Apontamentos</div>
                                <div class="h2 fw-bold">{{ $kpis['total_apontamentos'] }}</div>
                            </div>
                            <i class="fas fa-file-alt fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card text-white bg-success shadow-lg">
                    <div class="card-body">
                         <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="card-title">Aprovados</div>
                                <div class="h2 fw-bold">{{ $kpis['total_aprovados'] }}</div>
                            </div>
                           <i class="fas fa-check-circle fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card text-white bg-secondary shadow-lg">
                     <div class="card-body">
                         <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="card-title">Média Horas</div>
                                <div class="h2 fw-bold">{{ number_format($kpis['media_horas'], 2) }}h</div>
                            </div>
                            <i class="fas fa-balance-scale fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Gráficos --}}
        <div class="row">
            <div class="col-lg-8 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white border-0 py-3">Horas Aprovadas por Cliente</div>
                    <div class="card-body"><canvas id="clienteChart"></canvas></div>
                </div>
            </div>
            <div class="col-lg-4 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white border-0 py-3">Distribuição por Consultor</div>
                    <div class="card-body"><canvas id="consultorChart"></canvas></div>
                </div>
            </div>
        </div>

        {{-- Tabela de Dados --}}
        <div class="card my-4 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0">Detalhes dos Apontamentos</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="px-4 py-3">Data</th>
                                <th>Consultor</th>
                                <th>Cliente</th>
                                <th>Contrato</th>
                                <th class="text-end">Horas Gastas</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($apontamentos as $apontamento)
                                <tr>
                                    <td class="px-4">{{ \Carbon\Carbon::parse($apontamento->data_apontamento)->format('d/m/Y') }}</td>
                                    <td>{{ $apontamento->consultor->nome ?? 'N/A' }}</td>
                                    <td>{{ $apontamento->contrato->cliente->nome_empresa ?? 'N/A' }}</td>
                                    <td>{{ $apontamento->contrato->numero_contrato ?? 'N/A' }}</td>
                                    <td class="text-end fw-bold">{{ $apontamento->horas_gastas ?? '00:00' }}</td>
                                    <td class="text-center px-4">
                                        <span class="badge w-100 rounded-pill fs-6 
                                            @if($apontamento->status == 'Aprovado') bg-success-subtle text-success-emphasis
                                            @elseif($apontamento->status == 'Reprovado') bg-danger-subtle text-danger-emphasis
                                            @else bg-warning-subtle text-warning-emphasis @endif">
                                            {{ $apontamento->status }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <i class="fas fa-search fa-3x text-muted mb-3"></i>
                                        <p class="h5 text-muted">Nenhum apontamento encontrado</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @if(isset($kpis) && $kpis['total_aprovados'] > 0)
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const chartColors = ['#0d6efd', '#6f42c1', '#198754', '#0dcaf0', '#ffc107', '#6c757d', '#fd7e14'];

            const clienteCtx = document.getElementById('clienteChart');
            if (clienteCtx) {
                new Chart(clienteCtx, {
                    type: 'bar',
                    data: {
                        labels: @json($horasPorCliente->keys()),
                        datasets: [{
                            label: 'Horas Aprovadas',
                            data: @json($horasPorCliente->values()),
                            backgroundColor: chartColors,
                            borderRadius: 4,
                        }]
                    },
                    options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
                });
            }

            const consultorCtx = document.getElementById('consultorChart');
            if (consultorCtx) {
                new Chart(consultorCtx, {
                    type: 'doughnut',
                    data: {
                        labels: @json($horasPorConsultor->keys()),
                        datasets: [{
                            data: @json($horasPorConsultor->values()),
                            backgroundColor: chartColors,
                            hoverOffset: 4
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