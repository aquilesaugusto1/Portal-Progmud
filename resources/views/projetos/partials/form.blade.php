<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div>
        <label for="nome_projeto" class="block font-medium text-sm text-gray-700">Nome do Projeto</label>
        <input type="text" name="nome_projeto" id="nome_projeto" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" value="{{ old('nome_projeto', isset($projeto) ? $projeto->nome_projeto : '') }}" required>
    </div>

    <div>
        <label for="empresa_parceira_id" class="block font-medium text-sm text-gray-700">Cliente (Empresa Parceira)</label>
        <select name="empresa_parceira_id" id="empresa_parceira_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
            <option value="">Selecione um cliente</option>
            @foreach ($empresas as $empresa)
                <option value="{{ $empresa->id }}" {{ old('empresa_parceira_id', isset($projeto) ? $projeto->empresa_parceira_id : '') == $empresa->id ? 'selected' : '' }}>
                    {{ $empresa->nome_empresa }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="tipo" class="block font-medium text-sm text-gray-700">Tipo de Projeto</label>
        <select name="tipo" id="tipo" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
            <option value="ams" {{ old('tipo', isset($projeto) ? $projeto->tipo : '') == 'ams' ? 'selected' : '' }}>AMS</option>
            <option value="act" {{ old('tipo', isset($projeto) ? $projeto->tipo : '') == 'act' ? 'selected' : '' }}>ACT</option>
            <option value="act+" {{ old('tipo', isset($projeto) ? $projeto->tipo : '') == 'act+' ? 'selected' : '' }}>ACT+</option>
        </select>
    </div>

    <div id="tech-leads-container" class="{{ old('tipo', isset($projeto) ? $projeto->tipo : 'ams') === 'act+' ? '' : 'hidden' }}">
        <label for="tech_leads" class="block font-medium text-sm text-gray-700">Tech Leads Responsáveis</label>
        <select name="tech_leads[]" id="tech_leads" multiple class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
             @foreach ($techLeads as $techLead)
                <option value="{{ $techLead->id }}" {{ in_array($techLead->id, old('tech_leads', isset($projeto) ? $projeto->techLeads->pluck('id')->toArray() : [])) ? 'selected' : '' }}>
                    {{ $techLead->nome }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="md:col-span-2">
        <label for="consultores" class="block font-medium text-sm text-gray-700">Consultores do Projeto</label>
        <select name="consultores[]" id="consultores" multiple class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            @foreach ($consultores as $consultor)
                <option value="{{ $consultor->id }}" {{ in_array($consultor->id, old('consultores', isset($projeto) ? $projeto->consultores->pluck('id')->toArray() : [])) ? 'selected' : '' }}>
                    {{ $consultor->nome }}
                </option>
            @endforeach
        </select>
    </div>
</div>

<div class="flex items-center justify-end mt-6">
    <a href="{{ route('projetos.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">Cancelar</a>
    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
        {{ isset($projeto) ? 'Atualizar' : 'Salvar' }}
    </button>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tipoSelect = document.getElementById('tipo');
        const techLeadsContainer = document.getElementById('tech-leads-container');

        tipoSelect.addEventListener('change', function () {
            if (this.value === 'act+') {
                techLeadsContainer.classList.remove('hidden');
            } else {
                techLeadsContainer.classList.add('hidden');
            }
        });
    });
</script>
