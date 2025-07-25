{{-- resources/views/sugestoes/index.blade.php --}}
<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-4">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Sugestões de Melhoria</h2>
                <a href="{{ route('sugestoes.create') }}" class="bg-indigo-600 text-white font-bold py-2 px-4 rounded hover:bg-indigo-700">Nova Sugestão</a>
            </div>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200 space-y-4">
                    @forelse($sugestoes as $sugestao)
                        <div class="p-4 border rounded-lg">
                            <div class="flex justify-between items-start">
                                <h3 class="font-bold text-lg text-slate-800">{{ $sugestao->titulo }}</h3>
                                <span class="text-xs font-semibold px-2 py-1 rounded-full {{ $sugestao->status == 'Aberta' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">{{ $sugestao->status }}</span>
                            </div>
                            <p class="text-sm text-slate-500">Enviada por {{ $sugestao->usuario->nome }} em {{ $sugestao->created_at->format('d/m/Y') }}</p>
                            <p class="mt-2 text-slate-700">{{ $sugestao->descricao }}</p>
                        </div>
                    @empty
                        <p class="text-center text-slate-500">Nenhuma sugestão enviada ainda.</p>
                    @endforelse
                    <div class="mt-4">{{ $sugestoes->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
