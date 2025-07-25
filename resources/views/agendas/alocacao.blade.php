<x-app-layout>
    <div class="py-12">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-4">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Visão de Alocação
                </h2>
                <a href="{{ route('agendas.index') }}" class="inline-flex items-center px-4 py-2 bg-slate-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-slate-700">
                    Visão Padrão
                </a>
            </div>

            <div class="bg-white p-4 rounded-lg shadow-sm mb-4">
                <form method="GET" action="{{ route('agendas.alocacao') }}">
                    <div class="flex items-center space-x-4">
                        <h3 class="text-sm font-medium text-gray-700">Visualizar Mês:</h3>
                        <select name="mes" class="rounded-md border-gray-300 shadow-sm text-sm">
                            @for ($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ $m == $mes ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}</option>
                            @endfor
                        </select>
                        <select name="ano" class="rounded-md border-gray-300 shadow-sm text-sm">
                             @for ($y = date('Y'); $y >= date('Y') - 2; $y--)
                                <option value="{{ $y }}" {{ $y == $ano ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                        <button type="submit" class="px-3 py-2 bg-indigo-600 text-white text-xs font-bold uppercase rounded-md hover:bg-indigo-700">Filtrar</button>
                    </div>
                </form>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm divide-y divide-gray-200">
                        <thead class="bg-slate-100">
                            <tr>
                                <th class="px-4 py-2 text-left font-semibold text-slate-600">Consultor</th>
                                <th class="px-4 py-2 text-left font-semibold text-slate-600">Tech Lead</th>
                                <th class="px-4 py-2 text-left font-semibold text-slate-600">Cliente</th>
                                <th class="px-4 py-2 text-left font-semibold text-slate-600">Projeto</th>
                                <th class="px-4 py-2 text-left font-semibold text-slate-600">Saldo Horas Cliente</th>
                                <th class="px-4 py-2 text-left font-semibold text-slate-600">Horas Apontadas (Mês)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($consultores as $consultor)
                                @if($consultor->projetos->isEmpty())
                                    <tr class="bg-gray-50">
                                        <td class="px-4 py-2 font-medium text-gray-800">{{ $consultor->nome }}</td>
                                        <td colspan="5" class="px-4 py-2 text-gray-500 italic">Nenhum projeto associado</td>
                                    </tr>
                                @else
                                    @foreach ($consultor->projetos as $index => $projeto)
                                        <tr>
                                            @if ($index === 0)
                                                <td class="px-4 py-2 font-medium text-gray-800 align-top" rowspan="{{ $consultor->projetos->count() }}">{{ $consultor->nome }}</td>
                                            @endif
                                            <td class="px-4 py-2 align-top">
                                                @foreach($projeto->techLeads as $techLead)
                                                    <span class="inline-block bg-purple-100 text-purple-800 text-xs font-semibold mr-2 px-2.5 py-0.5 rounded-full">{{ $techLead->nome }}</span>
                                                @endforeach
                                            </td>
                                            <td class="px-4 py-2 text-slate-700">{{ $projeto->empresaParceira->nome_empresa }}</td>
                                            <td class="px-4 py-2 text-slate-700">{{ $projeto->nome_projeto }}</td>
                                            <td class="px-4 py-2 font-bold {{ $projeto->empresaParceira->saldo_total < 0 ? 'text-red-600' : 'text-slate-800' }}">
                                                {{ number_format($projeto->empresaParceira->saldo_total, 1) }}h
                                            </td>
                                            @php
                                                $horasNoProjeto = $consultor->apontamentos
                                                    ->filter(fn($ap) => $ap->agenda && $ap->agenda->projeto_id == $projeto->id)
                                                    ->sum('horas_gastas');
                                            @endphp
                                            <td class="px-4 py-2 font-semibold text-indigo-600">{{ number_format($horasNoProjeto, 1) }}h</td>
                                        </tr>
                                    @endforeach
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>