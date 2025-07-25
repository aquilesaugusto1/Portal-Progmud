<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Nova Empresa Parceira
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form action="{{ route('empresas.store') }}" method="POST">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="nome_empresa" class="block font-medium text-sm text-gray-700">Nome da Empresa</label>
                                <input type="text" name="nome_empresa" id="nome_empresa" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" value="{{ old('nome_empresa') }}" required>
                            </div>
                             <div>
                                <label for="contato_principal" class="block font-medium text-sm text-gray-700">Contato Principal</label>
                                <input type="text" name="contato_principal" id="contato_principal" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" value="{{ old('contato_principal') }}">
                            </div>
                            <div>
                                <label for="email" class="block font-medium text-sm text-gray-700">Email</label>
                                <input type="email" name="email" id="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" value="{{ old('email') }}">
                            </div>
                             <div>
                                <label for="telefone" class="block font-medium text-sm text-gray-700">Telefone</label>
                                <input type="text" name="telefone" id="telefone" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" value="{{ old('telefone') }}">
                            </div>
                             <div>
                                <label for="ramo_atividade" class="block font-medium text-sm text-gray-700">Ramo de Atividade</label>
                                <input type="text" name="ramo_atividade" id="ramo_atividade" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" value="{{ old('ramo_atividade') }}">
                            </div>
                            <div>
                                <label for="horas_contratadas" class="block font-medium text-sm text-gray-700">Horas Contratadas</label>
                                <input type="number" name="horas_contratadas" id="horas_contratadas" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" value="{{ old('horas_contratadas') }}">
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('empresas.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">Cancelar</a>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Salvar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
