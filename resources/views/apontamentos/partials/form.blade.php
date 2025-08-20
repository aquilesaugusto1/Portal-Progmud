@if (isset($apontamento))
    {{-- Modo Edição (sem alterações, pois o foco é na criação) --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label class="block font-medium text-sm text-gray-700">Agenda</label>
            <p class="mt-1 p-2 bg-gray-100 rounded-md">{{ $apontamento->agenda->data->format('d/m/Y') }} - {{ $apontamento->agenda->assunto }}</p>
        </div>
        <div>
            <label for="data_apontamento" class="block font-medium text-sm text-gray-700">Data do Apontamento</label>
            <input type="date" name="data_apontamento" id="data_apontamento" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" value="{{ old('data_apontamento', $apontamento->data_apontamento->format('Y-m-d')) }}" required>
        </div>
        <div>
            <label for="hora_inicio" class="block font-medium text-sm text-gray-700">Hora Início</label>
            <input type="time" name="hora_inicio" id="hora_inicio" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" value="{{ old('hora_inicio', $apontamento->hora_inicio) }}" required>
        </div>
        <div>
            <label for="hora_fim" class="block font-medium text-sm text-gray-700">Hora Fim</label>
            <input type="time" name="hora_fim" id="hora_fim" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" value="{{ old('hora_fim', $apontamento->hora_fim) }}" required>
        </div>
        <div class="md:col-span-2">
            <label for="descricao" class="block font-medium text-sm text-gray-700">Descrição das Atividades</label>
            <textarea name="descricao" id="descricao" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>{{ old('descricao', $apontamento->descricao) }}</textarea>
        </div>
    </div>
@else
    {{-- Modo Criação --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="md:col-span-2">
            <label for="agenda_id" class="block font-medium text-sm text-gray-700">Agenda Realizada</label>
            <select name="agenda_id" id="agenda_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                <option value="">Selecione uma agenda</option>
                @foreach($agendas as $agenda)
                    <option value="{{ $agenda->id }}" {{ old('agenda_id') == $agenda->id ? 'selected' : '' }}>
                        {{ $agenda->data->format('d/m/Y') }} - {{ $agenda->contrato->empresaParceira->nome_empresa }} - {{ $agenda->assunto }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="hora_inicio" class="block font-medium text-sm text-gray-700">Hora de Início</label>
            <input type="time" name="hora_inicio" id="hora_inicio" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm bg-gray-100" value="{{ old('hora_inicio') }}" required readonly>
        </div>
        <div>
            <label for="hora_fim" class="block font-medium text-sm text-gray-700">Hora de Fim</label>
            <input type="time" name="hora_fim" id="hora_fim" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm bg-gray-100" value="{{ old('hora_fim') }}" required readonly>
        </div>
        <div class="md:col-span-2">
            <label for="descricao" class="block font-medium text-sm text-gray-700">Descrição das Atividades</label>
            <textarea name="descricao" id="descricao" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>{{ old('descricao') }}</textarea>
        </div>
    </div>
@endif

<div class="flex items-center justify-end mt-6">
    <a href="{{ route('apontamentos.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">Cancelar</a>
    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
        {{ isset($apontamento) ? 'Atualizar' : 'Salvar' }}
    </button>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const agendaSelect = document.getElementById('agenda_id');
    const horaInicioInput = document.getElementById('hora_inicio');
    const horaFimInput = document.getElementById('hora_fim');

    function preencherHoras(agendaId) {
        if (!agendaId) {
            horaInicioInput.value = '';
            horaFimInput.value = '';
            return;
        }

        fetch(`/api/agendas/${agendaId}/details`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Agenda não encontrada');
                }
                return response.json();
            })
            .then(data => {
                horaInicioInput.value = data.hora_inicio;
                horaFimInput.value = data.hora_fim;
            })
            .catch(error => {
                console.error('Erro ao buscar detalhes da agenda:', error);
                horaInicioInput.value = '';
                horaFimInput.value = '';
            });
    }

    agendaSelect.addEventListener('change', function () {
        preencherHoras(this.value);
    });

    // Se houver um valor selecionado ao carregar a página (em caso de erro de validação com old())
    if (agendaSelect.value) {
        preencherHoras(agendaSelect.value);
    }
});
</script>
@endpush
