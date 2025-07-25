<x-app-layout>
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-6">
                        Detalhes do Projeto: {{ $projeto->nome_projeto }}
                    </h2>
                    <div class="space-y-6">
                        <div>
                            <h3 class="text-md font-bold text-gray-600 uppercase">Cliente</h3>
                            <p>{{ $projeto->empresaParceira->nome_empresa }}</p>
                        </div>
                        <div>
                            <h3 class="text-md font-bold text-gray-600 uppercase">Tipo</h3>
                            <p>{{ strtoupper($projeto->tipo) }}</p>
                        </div>
                        <div>
                            <h3 class="text-md font-bold text-gray-600 uppercase">Consultores Associados</h3>
                            <ul class="list-disc list-inside mt-2">
                                @forelse($projeto->consultores as $consultor)
                                    <li>{{ $consultor->nome }}</li>
                                @empty
                                    <li>Nenhum consultor associado.</li>
                                @endforelse
                            </ul>
                        </div>
                        @if($projeto->tipo === 'act+')
                        <div>
                            <h3 class="text-md font-bold text-gray-600 uppercase">Tech Leads Associados</h3>
                            <ul class="list-disc list-inside mt-2">
                                @forelse($projeto->techLeads as $techLead)
                                    <li>{{ $techLead->nome }}</li>
                                @empty
                                    <li>Nenhum Tech Lead associado.</li>
                                @endforelse
                            </ul>
                        </div>
                        @endif
                    </div>
                    <div class="flex items-center justify-end mt-6">
                        <a href="{{ route('projetos.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            Voltar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
