<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-4">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Agendas
                </h2>
                <div class="flex items-center space-x-3">
                    @can('viewAlocacao', App\Models\Agenda::class)
                        <a href="{{ route('agendas.alocacao') }}" class="inline-flex items-center px-4 py-2 bg-slate-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-slate-700">
                            Visão de Alocação
                        </a>
                    @endcan
                    @can('create', App\Models\Agenda::class)
                        <a href="{{ route('agendas.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                            Nova Agenda
                        </a>
                    @endcan
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form action="{{ route('agendas.index') }}" method="GET" class="mb-6">
                        <div class="flex items-center">
                            <input type="text" name="search" placeholder="Pesquisar por assunto, consultor, projeto..." value="{{ $search ?? '' }}" class="w-full rounded-l-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white font-semibold rounded-r-md hover:bg-indigo-700">-</button>
                            <a href="{{ route('agendas.index') }}" class="ml-2 px-4 py-2 bg-gray-500 text-white font-semibold rounded-md hover:bg-gray-600">-</a>
                        </div>
                    </form>

                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data/Hora</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assunto</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Consultor</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tech Lead(s)</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="relative px-6 py-3"><span class="sr-only">Ações</span></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($agendas as $agenda)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $agenda->data_hora->format('d/m/Y H:i') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $agenda->assunto }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $agenda->consultor->nome }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @foreach($agenda->consultor->techLeads as $techLead)
                                            <span class="inline-block bg-purple-100 text-purple-800 text-xs font-semibold mr-2 px-2.5 py-0.5 rounded-full">{{ $techLead->nome }}</span>
                                        @endforeach
                                    </td>
                                     <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @switch($agenda->status)
                                                @case('Agendada') bg-blue-100 text-blue-800 @break
                                                @case('Realizada') bg-green-100 text-green-800 @break
                                                @case('Cancelada') bg-red-100 text-red-800 @break
                                            @endswitch">
                                            {{ $agenda->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        @can('update', $agenda)
                                            <a href="{{ route('agendas.edit', $agenda) }}" class="text-indigo-600 hover:text-indigo-900">Editar</a>
                                        @endcan
                                        
                                        @can('delete', $agenda)
                                        <form action="{{ route('agendas.destroy', $agenda) }}" method="POST" class="inline ml-4">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Tem certeza que deseja remover esta agenda?')">Remover</button>
                                        </form>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">Nenhuma agenda encontrada.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="mt-4">
                        {{ $agendas->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
