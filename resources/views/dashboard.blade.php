<x-app-layout>
    <div class="space-y-8">
        <div>
            <h1 class="text-3xl font-bold text-slate-800">Olá, {{ Auth::user()->nome }}!</h1>
            <p class="mt-1 text-lg text-slate-600">Bem-vindo(a) de volta ao seu painel Agen.</p>
        </div>

        @if(Auth::user()->funcao === 'admin')
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="p-6 rounded-lg shadow-lg bg-indigo-500 text-white">
                    <p class="text-sm font-medium text-indigo-100">{{ array_keys($stats)[0] }}</p>
                    <p class="mt-1 text-4xl font-bold">{{ array_values($stats)[0] }}</p>
                </div>
                <div class="p-6 rounded-lg shadow-lg bg-blue-500 text-white">
                    <p class="text-sm font-medium text-blue-100">{{ array_keys($stats)[1] }}</p>
                    <p class="mt-1 text-4xl font-bold">{{ array_values($stats)[1] }}</p>
                </div>
                <div class="p-6 rounded-lg shadow-lg bg-purple-500 text-white">
                    <p class="text-sm font-medium text-purple-100">{{ array_keys($stats)[2] }}</p>
                    <p class="mt-1 text-4xl font-bold">{{ array_values($stats)[2] }}</p>
                </div>
                <div class="p-6 rounded-lg shadow-lg bg-teal-500 text-white">
                    <p class="text-sm font-medium text-teal-100">{{ array_keys($stats)[3] }}</p>
                    <p class="mt-1 text-4xl font-bold">{{ array_values($stats)[3] }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-md border border-slate-200">
                    <h2 class="text-xl font-bold text-slate-800 mb-4">Volume de Agendas Mensais por Status</h2>
                    <canvas id="agendasChart"></canvas>
                </div>

                <div class="space-y-8">
                    <div class="bg-white p-6 rounded-xl shadow-md border border-slate-200">
                        <h3 class="text-lg font-bold text-slate-800 mb-3">Projetos com Baixo Saldo de Horas</h3>
                        <ul class="space-y-3">
                            @forelse($projetosCriticos as $projeto)
                                <li class="flex justify-between items-center text-sm">
                                    <span class="font-medium text-slate-700">{{ $projeto->nome_projeto }}</span>
                                    <span class="font-bold text-red-500">{{ number_format($projeto->empresaParceira->saldo_total, 1) }}h</span>
                                </li>
                            @empty
                                <p class="text-sm text-slate-500">Nenhum projeto com saldo crítico.</p>
                            @endforelse
                        </ul>
                    </div>
                    <div class="bg-white p-6 rounded-xl shadow-md border border-slate-200">
                        <h3 class="text-lg font-bold text-slate-800 mb-3">Consultores Mais Ativos (Últimos 30 dias)</h3>
                        <ul class="space-y-3">
                            @forelse($consultoresAtivos as $consultor)
                                <li class="flex justify-between items-center text-sm">
                                    <span class="font-medium text-slate-700">{{ $consultor->nome }}</span>
                                    <span class="font-bold text-indigo-600">{{ number_format($consultor->horas_30_dias, 1) }}h</span>
                                </li>
                            @empty
                                <p class="text-sm text-slate-500">Sem apontamentos nos últimos 30 dias.</p>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>

            @push('scripts')
                <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                <script>
                    const ctx = document.getElementById('agendasChart');
                    new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: @json($chartLabels),
                            datasets: [
                                {
                                    label: 'Realizadas',
                                    data: @json($chartRealizadas),
                                    backgroundColor: 'rgba(34, 197, 94, 0.8)',
                                },
                                {
                                    label: 'Agendadas',
                                    data: @json($chartAgendadas),
                                    backgroundColor: 'rgba(59, 130, 246, 0.8)',
                                },
                                {
                                    label: 'Canceladas',
                                    data: @json($chartCanceladas),
                                    backgroundColor: 'rgba(239, 68, 68, 0.8)',
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            scales: {
                                x: { stacked: true },
                                y: { stacked: true, beginAtZero: true }
                            },
                            plugins: {
                                legend: { position: 'top' },
                                tooltip: {
                                    mode: 'index',
                                    intersect: false
                                },
                            },
                            interaction: {
                                mode: 'index',
                                intersect: false
                            }
                        }
                    });
                </script>
            @endpush

        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @php $colors = ['bg-indigo-500', 'bg-purple-500', 'bg-teal-500']; $i = 0; @endphp
                @foreach($stats as $label => $value)
                <div class="p-6 rounded-lg shadow-lg text-white {{ $colors[$i++ % count($colors)] }}">
                     <p class="text-sm font-medium opacity-80">{{ $label }}</p>
                    <p class="mt-1 text-4xl font-bold">{{ $value }}</p>
                </div>
                @endforeach
            </div>
            <div class="mt-8 bg-white p-6 rounded-xl shadow-md border border-slate-200">
                <h2 class="text-xl font-bold text-slate-800 mb-4">Suas Próximas Agendas</h2>
                <div class="space-y-4">
                    @forelse($ultimas_agendas as $agenda)
                        <div class="flex items-center justify-between p-4 rounded-lg bg-slate-50 border border-slate-200">
                            <div>
                                <p class="font-semibold text-slate-800">{{ $agenda->assunto }}</p>
                                <p class="text-sm text-slate-500">
                                    {{ $agenda->consultor->nome }} para <strong>{{ $agenda->projeto->empresaParceira->nome_empresa }}</strong> (Projeto: {{ $agenda->projeto->nome_projeto }})
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-slate-800">{{ $agenda->data_hora->format('d/m/Y') }}</p>
                                <p class="text-sm text-slate-500">{{ $agenda->data_hora->format('H:i') }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            <h3 class="mt-2 text-sm font-medium text-slate-900">Nenhuma agenda para exibir.</h3>
                            <p class="mt-1 text-sm text-slate-500">Parece que está tudo tranquilo por aqui!</p>
                        </div>
                    @endforelse
                </div>
            </div>
        @endif
    </div>
</x-app-layout>