<x-app-layout>
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 md:p-8 bg-white border-b border-gray-200">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6">Editar CP TOTVS</h2>
                    <form method="POST" action="{{ route('cp-totvs.update', $cp) }}">
                        @csrf
                        @method('PUT')
                        @include('cp-totvs._form')
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>