<x-app-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
        <div class="sm:flex sm:justify-between sm:items-center mb-8">
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Contratos</h1>
            </div>
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                <a href="{{ route('contratos.create') }}" class="btn bg-indigo-500 hover:bg-indigo-600 text-white">
                    <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                        <path d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z" />
                    </svg>
                    <span class="hidden xs:block ml-2">Novo Contrato</span>
                </a>
            </div>
        </div>

        <div class="bg-white shadow-lg rounded-sm border border-slate-200">
            <header class="px-5 py-4">
                <h2 class="font-semibold text-slate-800">Todos os Contratos <span class="text-slate-400 font-medium">{{ $contratos->total() }}</span></h2>
            </header>
            <div>
                <div class="overflow-x-auto">
                    <table class="table-auto w-full">
                        <thead class="text-xs font-semibold uppercase text-slate-500 bg-slate-50">
                            <tr>
                                <th class="p-2 whitespace-nowrap"><div class="font-semibold text-left">Cliente</div></th>
                                <th class="p-2 whitespace-nowrap"><div class="font-semibold text-left">Nº Contrato</div></th>
                                <th class="p-2 whitespace-nowrap"><div class="font-semibold text-left">Tipo</div></th>
                                <th class="p-2 whitespace-nowrap"><div class="font-semibold text-left">Status</div></th>
                                <th class="p-2 whitespace-nowrap"><div class="font-semibold text-left">Data Início</div></th>
                                <th class="p-2 whitespace-nowrap"><div class="font-semibold text-left">Data Fim</div></th>
                                <th class="p-2 whitespace-nowrap"><div class="font-semibold text-center">Ações</div></th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y divide-slate-200">
                            @forelse ($contratos as $contrato)
                                <tr>
                                    <td class="p-2 whitespace-nowrap"><div class="font-medium text-slate-800">{{ $contrato->cliente->fantasia }}</div></td>
                                    <td class="p-2 whitespace-nowrap"><div class="text-left">{{ $contrato->numero_contrato }}</div></td>
                                    <td class="p-2 whitespace-nowrap"><div class="text-left">{{ $contrato->tipo_contrato }}</div></td>
                                    <td class="p-2 whitespace-nowrap">
                                        <div class="font-medium @if($contrato->status === 'Ativo') text-emerald-500 @else text-rose-500 @endif">{{ $contrato->status }}</div>
                                    </td>
                                    <td class="p-2 whitespace-nowrap"><div class="text-left">{{ $contrato->data_inicio->format('d/m/Y') }}</div></td>
                                    <td class="p-2 whitespace-nowrap"><div class="text-left">{{ $contrato->data_termino ? $contrato->data_termino->format('d/m/Y') : 'N/A' }}</div></td>
                                    <td class="p-2 whitespace-nowrap">
                                        <div class="flex items-center justify-center space-x-2">
                                            <a href="{{ route('contratos.edit', $contrato) }}" class="text-slate-400 hover:text-slate-500">
                                                <span class="sr-only">Editar</span>
                                                <svg class="w-8 h-8 fill-current" viewBox="0 0 32 32"><path d="M19.7 8.3c-.4-.4-1-.4-1.4 0l-10 10c-.2.2-.3.4-.3.7v4c0 .6.4 1 1 1h4c.3 0 .5-.1.7-.3l10-10c.4-.4.4-1 0-1.4l-4-4zM12.6 22H10v-2.6l6-6 2.6 2.6-6 6zm7.4-7.4L17.4 12l1.6-1.6 2.6 2.6-1.6 1.6z"/></svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="p-4 text-center text-slate-500">Nenhum contrato encontrado.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="mt-8">
            {{ $contratos->links() }}
        </div>
    </div>
</x-app-layout>