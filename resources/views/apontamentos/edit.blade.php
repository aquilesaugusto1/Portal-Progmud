<x-app-layout>
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-6">
                        Editar Apontamento
                    </h2>

                    @if ($errors->any())
                        <div class="mb-4 rounded-md bg-red-50 p-4">
                            <div class="flex">
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-red-800">Ocorreram alguns erros:</h3>
                                    <div class="mt-2 text-sm text-red-700">
                                        <ul class="list-disc list-inside space-y-1">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- O formulário de edição agora está diretamente aqui --}}
                    <form action="{{ route('apontamentos.update', $apontamento) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Campo de Agenda desabilitado, pois não deve ser alterado na edição --}}
                            <div class="md:col-span-2">
                                <x-input-label for="agenda_id" :value="__('Agenda Realizada')" />
                                <input type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm bg-gray-100" value="{{ $apontamento->agenda->data->format('d/m/Y') }} - {{ $apontamento->contrato->empresaParceira->nome_empresa }} - {{ $apontamento->agenda->assunto }}" disabled>
                                <input type="hidden" name="agenda_id" value="{{ $apontamento->agenda_id }}">
                            </div>

                            <div>
                                <x-input-label for="hora_inicio" :value="__('Hora de Início')" />
                                <input type="time" name="hora_inicio" id="hora_inicio" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" value="{{ old('hora_inicio', $apontamento->hora_inicio) }}" required>
                            </div>
                            <div>
                                <x-input-label for="hora_fim" :value="__('Hora de Fim')" />
                                <input type="time" name="hora_fim" id="hora_fim" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" value="{{ old('hora_fim', $apontamento->hora_fim) }}" required>
                            </div>
                            <div class="md:col-span-2">
                                <x-input-label for="descricao" :value="__('Descrição das Atividades')" />
                                <textarea name="descricao" id="descricao" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>{{ old('descricao', $apontamento->descricao) }}</textarea>
                            </div>
                            <div class="md:col-span-2">
                                <x-input-label for="anexo" :value="__('Anexo (Opcional)')" />
                                <input type="file" name="anexo" id="anexo" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                @if($apontamento->caminho_anexo)
                                    <a href="{{ Storage::url($apontamento->caminho_anexo) }}" target="_blank" class="mt-2 inline-block text-sm text-indigo-600 hover:underline">Ver anexo atual</a>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('apontamentos.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">Cancelar</a>
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md shadow-sm">
                                Atualizar Apontamento
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
