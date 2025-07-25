<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-start">
                         <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                            Detalhes da Empresa: {{ $empresa->nome_empresa }}
                        </h2>
                         <div class="text-right">
                            <h3 class="text-md font-bold text-gray-500 uppercase">Saldo de Horas</h3>
                            <p class="text-3xl font-bold {{ $empresa->saldo_total < 0 ? 'text-red-600' : 'text-gray-800' }}">
                                {{ number_format($empresa->saldo_total, 2, ',', '.') }}
                            </p>
                        </div>
                    </div>
                   
                    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6 border-t pt-6">
                        <div>
                            <h3 class="text-md font-bold text-gray-600 uppercase">Contato Principal</h3>
                            <p>{{ $empresa->contato_principal ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <h3 class="text-md font-bold text-gray-600 uppercase">Email</h3>
                            <p>{{ $empresa->email ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <h3 class="text-md font-bold text-gray-600 uppercase">Telefone</h3>
                            <p>{{ $empresa->telefone ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <h3 class="text-md font-bold text-gray-600 uppercase">Ramo de Atividade</h3>
                            <p>{{ $empresa->ramo_atividade ?? 'N/A' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center justify-end mt-6">
                        <a href="{{ route('empresas.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            Voltar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>