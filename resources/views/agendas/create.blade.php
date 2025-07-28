<x-app-layout>
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 md:p-8 bg-white border-b border-gray-200">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-6">
                        Nova Agenda
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

                    <form action="{{ route('agendas.store') }}" method="POST">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="contrato_id" class="block font-medium text-sm text-gray-700">Contrato *</label>
                                <select name="contrato_id" id="contrato_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                    <option value="">Selecione um contrato</option>
                                    @foreach($contratos as $contrato)
                                        <option value="{{ $contrato->id }}" @selected(old('contrato_id') == $contrato->id)>
                                            {{ $contrato->cliente->nome_empresa }} (#{{ $contrato->numero_contrato ?? $contrato->id }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="consultor_id" class="block font-medium text-sm text-gray-700">Consultor *</label>
                                <select name="consultor_id" id="consultor_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required disabled>
                                    <option value="">Selecione um contrato para ver os consultores</option>
                                </select>
                            </div>

                            <div class="md:col-span-2">
                                <label for="assunto" class="block font-medium text-sm text-gray-700">Assunto *</label>
                                <input type="text" name="assunto" id="assunto" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" value="{{ old('assunto') }}" required>
                            </div>
                            
                            <div>
                                <label for="data_hora" class="block font-medium text-sm text-gray-700">Data e Hora *</label>
                                <input type="datetime-local" name="data_hora" id="data_hora" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" value="{{ old('data_hora') }}" required>
                            </div>

                            <div>
                                <label for="status" class="block font-medium text-sm text-gray-700">Status *</label>
                                <select name="status" id="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                    <option value="Agendada" @selected(old('status', 'Agendada') == 'Agendada')>Agendada</option>
                                    <option value="Realizada" @selected(old('status') == 'Realizada')>Realizada</option>
                                    <option value="Cancelada" @selected(old('status') == 'Cancelada')>Cancelada</option>
                                </select>
                            </div>

                            <div class="md:col-span-2">
                                <label for="descricao" class="block font-medium text-sm text-gray-700">Descrição</label>
                                <textarea name="descricao" id="descricao" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('descricao') }}</textarea>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-8 pt-5 border-t border-slate-200">
                            <a href="{{ route('agendas.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">Cancelar</a>
                            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white font-semibold rounded-md hover:bg-indigo-700">Salvar Agenda</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const contratoSelect = document.getElementById('contrato_id');
    const consultorSelect = document.getElementById('consultor_id');
    const oldConsultorId = "{{ old('consultor_id') }}";

    function fetchConsultores(contratoId, selectedConsultorId = null) {
        consultorSelect.innerHTML = '<option value="">Carregando...</option>';
        consultorSelect.disabled = true;

        if (!contratoId) {
            consultorSelect.innerHTML = '<option value="">Selecione um contrato para ver os consultores</option>';
            return;
        }

        fetch(`/api/contratos/${contratoId}/consultores`)
            .then(response => response.json())
            .then(consultores => {
                consultorSelect.innerHTML = '<option value="">Selecione um consultor</option>';
                if (consultores.length > 0) {
                    consultores.forEach(consultor => {
                        const option = document.createElement('option');
                        option.value = consultor.id;
                        option.textContent = `${consultor.nome} ${consultor.sobrenome || ''}`.trim();
                        if (consultor.id == selectedConsultorId) {
                            option.selected = true;
                        }
                        consultorSelect.appendChild(option);
                    });
                    consultorSelect.disabled = false;
                } else {
                    consultorSelect.innerHTML = '<option value="">Nenhum consultor associado a este contrato</option>';
                }
            })
            .catch(error => {
                console.error('Erro ao buscar consultores:', error);
                consultorSelect.innerHTML = '<option value="">Erro ao carregar consultores</option>';
            });
    }

    contratoSelect.addEventListener('change', function () {
        fetchConsultores(this.value);
    });

    // If there's an old contract value (e.g., validation error), trigger the fetch
    if (contratoSelect.value) {
        fetchConsultores(contratoSelect.value, oldConsultorId);
    }
});
</script>
@endpush
</x-app-layout>
