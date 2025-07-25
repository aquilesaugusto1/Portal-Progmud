<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-4">
                Aprovações de Apontamentos Pendentes
            </h2>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    @forelse ($apontamentos as $apontamento)
                        <div class="mb-6 pb-6 border-b last:border-b-0">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="font-bold text-lg text-slate-800">{{ $apontamento->agenda->assunto }}</p>
                                    <p class="text-sm text-slate-600">
                                        <span class="font-semibold">{{ $apontamento->consultor->nome }}</span> para
                                        <strong>{{ $apontamento->agenda->projeto->empresaParceira->nome_empresa }}</strong>
                                    </p>
                                    <p class="text-xs text-slate-500">Enviado em: {{ $apontamento->created_at->format('d/m/Y H:i') }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-2xl font-bold text-indigo-600">{{ number_format($apontamento->horas_gastas, 1) }}h</p>
                                    <p class="text-xs text-slate-500">Saldo Cliente: {{ number_format($apontamento->agenda->projeto->empresaParceira->saldo_total, 1) }}h</p>
                                </div>
                            </div>
                            <div class="mt-4 p-4 bg-slate-50 rounded-md">
                                <p class="text-sm font-semibold text-slate-700">Descrição:</p>
                                <p class="text-sm text-slate-600 whitespace-pre-wrap">{{ $apontamento->descricao }}</p>
                                @if($apontamento->caminho_anexo)
                                    <a href="{{ Storage::url($apontamento->caminho_anexo) }}" target="_blank" class="mt-2 inline-block text-sm text-indigo-600 hover:underline">Ver Anexo</a>
                                @endif
                            </div>
                            <div class="mt-4 flex flex-wrap items-center gap-4">
                                <form action="{{ route('aprovacoes.aprovar', $apontamento) }}" method="POST" class="flex items-center gap-4">
                                    @csrf

                                    <div>
                                        <label for="faturado_{{ $apontamento->id }}" class="block text-xs font-medium text-gray-700">Ação</label>
                                        <select name="faturado" id="faturado_{{ $apontamento->id }}" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                            <option value="1" selected>Aprovar e Faturar</option>
                                            <option value="0">Aprovar (Não Faturar)</option>
                                        </select>
                                    </div>

                                    @if($apontamento->agenda->projeto->empresaParceira->saldo_total < $apontamento->horas_gastas)
                                        <div class="pt-5 text-xs text-red-600 flex items-center">
                                            <input type="checkbox" name="forcar_aprovacao" id="forcar_{{ $apontamento->id }}" class="mr-2 h-4 w-4 rounded border-gray-300 text-indigo-600">
                                            <label for="forcar_{{ $apontamento->id }}">Forçar (saldo insuficiente)</label>
                                        </div>
                                    @endif
                                    <div class="pt-5">
                                        <button type="submit" class="px-4 py-2 bg-green-500 text-white text-xs font-bold uppercase rounded-md hover:bg-green-600">Confirmar</button>
                                    </div>
                                </form>

                                <form action="{{ route('aprovacoes.rejeitar', $apontamento) }}" method="POST" class="flex-grow">
                                    @csrf
                                    <div class="flex items-end">
                                        <div class="flex-grow">
                                            <label for="motivo_rejeicao_{{ $apontamento->id }}" class="block text-xs font-medium text-gray-700">Rejeitar com motivo</label>
                                            <input type="text" name="motivo_rejeicao" placeholder="Motivo da rejeição..." id="motivo_rejeicao_{{ $apontamento->id }}" class="w-full text-xs rounded-l-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        </div>
                                        <button type="submit" class="px-4 py-2 bg-red-500 text-white text-xs font-bold uppercase rounded-r-md hover:bg-red-600 h-[38px]">Rejeitar</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @empty
                        <p class="text-center text-slate-500">Nenhum apontamento pendente de aprovação.</p>
                    @endforelse
                    <div class="mt-4">
                        {{ $apontamentos->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>