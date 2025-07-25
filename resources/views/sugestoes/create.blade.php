{{-- resources/views/sugestoes/create.blade.php --}}
<x-app-layout>
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-6">Enviar Sugestão de Melhoria</h2>
                    <form action="{{ route('sugestoes.store') }}" method="POST">
                        @csrf
                        <div class="space-y-6">
                            <div>
                                <label for="titulo" class="block font-medium text-sm text-gray-700">Título</label>
                                <input type="text" name="titulo" id="titulo" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                            </div>
                            <div>
                                <label for="descricao" class="block font-medium text-sm text-gray-700">Descrição</label>
                                <textarea name="descricao" id="descricao" rows="5" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required></textarea>
                            </div>
                        </div>
                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('sugestoes.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">Cancelar</a>
                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">Enviar Sugestão</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
