<x-app-layout>
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-6">
                        Gerar Relatório de Apontamentos
                    </h2>
                    <form action="{{ route('relatorios.gerar') }}" method="POST" id="relatorio-form">
                        @csrf
                        <input type="hidden" name="formato" id="formato" value="html">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="data_inicio" class="block font-medium text-sm text-gray-700">Data de Início</label>
                                <input type="date" name="data_inicio" id="data_inicio" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                            </div>
                            <div>
                                <label for="data_fim" class="block font-medium text-sm text-gray-700">Data de Fim</label>
                                <input type="date" name="data_fim" id="data_fim" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                            </div>
                            <div>
                                <label for="consultor_id" class="block font-medium text-sm text-gray-700">Filtrar por Consultor (Opcional)</label>
                                <select name="consultor_id" id="consultor_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                    <option value="">Todos os consultores</option>
                                    @foreach ($consultores as $consultor)
                                        <option value="{{ $consultor->id }}">{{ $consultor->nome }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="empresa_id" class="block font-medium text-sm text-gray-700">Filtrar por Cliente (Opcional)</label>
                                <select name="empresa_id" id="empresa_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                    <option value="">Todos os clientes</option>
                                    @foreach ($empresas as $empresa)
                                        <option value="{{ $empresa->id }}">{{ $empresa->nome_empresa }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="flex items-center justify-end mt-6 space-x-4">
                            <button type="button" onclick="setFormato('html')" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Gerar Relatório
                            </button>
                            <button type="button" onclick="setFormato('pdf')" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                                Baixar PDF
                            </button>
                             <button type="button" onclick="setFormato('excel')" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                Baixar Excel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        function setFormato(formato) {
            document.getElementById('formato').value = formato;
            document.getElementById('relatorio-form').submit();
        }
    </script>
</x-app-layout>