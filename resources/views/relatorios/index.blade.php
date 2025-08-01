<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 font-weight-bold">
            {{ __('Relatório de Apontamentos') }}
        </h2>
    </x-slot>

    <div class="card my-4">
        <div class="card-body">
            <form action="{{ route('relatorios.gerar') }}" method="POST">
                @csrf
                <div class="row">
                    <!-- Data Início -->
                    <div class="col-md-3 mb-3">
                        <label for="data_inicio" class="form-label">Data Início</label>
                        <input type="date" id="data_inicio" name="data_inicio" class="form-control" value="{{ $filtros['data_inicio'] ?? old('data_inicio') }}">
                    </div>

                    <!-- Data Fim -->
                    <div class="col-md-3 mb-3">
                        <label for="data_fim" class="form-label">Data Fim</label>
                        <input type="date" id="data_fim" name="data_fim" class="form-control" value="{{ $filtros['data_fim'] ?? old('data_fim') }}">
                    </div>

                    <!-- Consultor -->
                    <div class="col-md-3 mb-3">
                        <label for="colaborador_id" class="form-label">Consultor</label>
                        <select id="colaborador_id" name="colaborador_id" class="form-select">
                            <option value="">Todos</option>
                            @foreach($consultores as $consultor)
                                <option value="{{ $consultor->id }}" @selected(isset($filtros['colaborador_id']) && $filtros['colaborador_id'] == $consultor->id)>
                                    {{ $consultor->nome }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Empresa Parceira -->
                    <div class="col-md-3 mb-3">
                        <label for="empresa_id" class="form-label">Cliente</label>
                        <select id="empresa_id" name="empresa_id" class="form-select">
                            <option value="">Todos</option>
                            @foreach($empresas as $empresa)
                                <option value="{{ $empresa->id }}" @selected(isset($filtros['empresa_id']) && $filtros['empresa_id'] == $empresa->id)>
                                    {{ $empresa->razao_social }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Contrato -->
                    <div class="col-md-3 mb-3">
                        <label for="contrato_id" class="form-label">Contrato</label>
                        <select id="contrato_id" name="contrato_id" class="form-select">
                            <option value="">Todos</option>
                            @foreach($contratos as $contrato)
                                <option value="{{ $contrato->id }}" @selected(isset($filtros['contrato_id']) && $filtros['contrato_id'] == $contrato->id)>
                                    {{ $contrato->codigo_contrato }} - {{ $contrato->nome_contrato }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Status -->
                    <div class="col-md-3 mb-3">
                        <label for="status" class="form-label">Status do Apontamento</label>
                        <select id="status" name="status" class="form-select">
                            <option value="">Todos</option>
                            <option value="Pendente" @selected(isset($filtros['status']) && $filtros['status'] == 'Pendente')>Pendente</option>
                            <option value="Aprovado" @selected(isset($filtros['status']) && $filtros['status'] == 'Aprovado')>Aprovado</option>
                            <option value="Reprovado" @selected(isset($filtros['status']) && $filtros['status'] == 'Reprovado')>Reprovado</option>
                        </select>
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" name="formato" value="html" class="btn btn-primary">
                        <i class="fas fa-search"></i> Gerar Relatório
                    </button>
                    <button type="submit" name="formato" value="pdf" class="btn btn-secondary">
                        <i class="fas fa-file-pdf"></i> Gerar PDF
                    </button>
                    <button type="submit" name="formato" value="excel" class="btn btn-success">
                        <i class="fas fa-file-excel"></i> Gerar Excel
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if(isset($apontamentos))
    <div class="card my-4">
        <div class="card-header">
            <h4>Resultados</h4>
        </div>
        <div class="card-body">
            @if(isset($graficoLabels) && $graficoLabels->isNotEmpty())
                <div class="mb-5">
                    <h5>Total de Horas Aprovadas por Consultor</h5>
                    <canvas id="graficoHoras"></canvas>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Consultor</th>
                            <th>Cliente</th>
                            <th>Contrato</th>
                            <th>Horas Aprovadas</th>
                            <th>Status</th>
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
                                <td>
                                    <span class="badge 
                                        @if($apontamento->status == 'Aprovado') bg-success
                                        @elseif($apontamento->status == 'Reprovado') bg-danger
                                        @else bg-warning @endif">
                                        {{ $apontamento->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">Nenhum apontamento encontrado para os filtros selecionados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @if(isset($graficoLabels))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('graficoHoras');
            if (ctx) {
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: {!! json_encode($graficoLabels) !!},
                        datasets: [{
                            label: 'Horas Aprovadas',
                            data: {!! json_encode($graficoValues) !!},
                            backgroundColor: 'rgba(21, 115, 71, 0.8)',
                            borderColor: 'rgba(21, 115, 71, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            }
                        }
                    }
                });
            }
        });
    </script>
    @endif
    @endpush
</x-app-layout>
