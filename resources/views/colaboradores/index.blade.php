<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-4">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Colaboradores
                </h2>
                <a href="{{ route('colaboradores.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md shadow-sm">
                    Novo Colaborador
                </a>
            </div>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="relative px-6 py-3"><span class="sr-only">Ações</span></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($colaboradores as $colaborador)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $colaborador->nome }} {{ $colaborador->sobrenome }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $colaborador->email }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $colaborador->status === 'Ativo' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $colaborador->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('colaboradores.show', $colaborador) }}" class="text-indigo-600 hover:text-indigo-900">Ver</a>
                                        <a href="{{ route('colaboradores.edit', $colaborador) }}" class="ml-4 text-indigo-600 hover:text-indigo-900">Editar</a>
                                        <form action="{{ route('colaboradores.toggleStatus', $colaborador) }}" method="POST" class="inline ml-4">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="{{ $colaborador->status === 'Ativo' ? 'text-red-600 hover:text-red-900' : 'text-green-600 hover:text-green-900' }}" onclick="return confirm('Tem certeza que deseja alterar o status deste colaborador?')">
                                                {{ $colaborador->status === 'Ativo' ? 'Desabilitar' : 'Habilitar' }}
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">Nenhum colaborador encontrado.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="mt-4">
                        {{ $colaboradores->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>