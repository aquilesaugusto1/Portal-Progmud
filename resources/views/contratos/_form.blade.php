@if ($errors->any())
    <div class="mb-6 rounded-md bg-red-50 p-4 border border-red-200">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" /></svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800">Foram encontrados {{ count($errors->all()) }} erros:</h3>
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

<div class="space-y-8">
    <!-- Seção 1: Informações Principais -->
    <div class="p-6 bg-white border border-slate-200 rounded-lg shadow-sm">
        <h3 class="text-lg font-semibold text-slate-800 mb-4">Informações Principais</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <x-input-label for="cliente_id" value="Cliente *" />
                <select name="cliente_id" id="cliente_id" class="form-select mt-1 block w-full" required>
                    <option value="">Selecione um cliente</option>
                    @foreach($clientes as $cliente)
                        <option value="{{ $cliente->id }}" @selected(old('cliente_id', $contrato->cliente_id ?? '') == $cliente->id)>{{ $cliente->nome_empresa }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <x-input-label for="numero_contrato" value="Número do Contrato" />
                <x-text-input id="numero_contrato" name="numero_contrato" type="text" class="mt-1 block w-full" :value="old('numero_contrato', $contrato->numero_contrato ?? '')" />
            </div>
            <div>
                <x-input-label for="tipo_contrato" value="Tipo de Contrato *" />
                <select name="tipo_contrato" id="tipo_contrato" class="form-select mt-1 block w-full" required>
                    <option value="">Selecione um tipo</option>
                    @foreach(['ACT+', 'ACT', 'AMS', 'Projetos', 'Emergencial'] as $tipo)
                        <option value="{{ $tipo }}" @selected(old('tipo_contrato', $contrato->tipo_contrato ?? '') == $tipo)>{{ $tipo }}</option>
                    @endforeach
                </select>
            </div>
             <div>
                <x-input-label for="status" value="Status *" />
                <select name="status" id="status" class="form-select mt-1 block w-full" required>
                    <option value="Ativo" @selected(old('status', $contrato->status ?? 'Ativo') == 'Ativo')>Ativo</option>
                    <option value="Inativo" @selected(old('status', $contrato->status ?? '') == 'Inativo')>Inativo</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Seção 2: Prazos e Responsáveis -->
    <div class="p-6 bg-white border border-slate-200 rounded-lg shadow-sm">
        <h3 class="text-lg font-semibold text-slate-800 mb-4">Prazos e Responsáveis</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <x-input-label for="data_inicio" value="Data de Início *" />
                <x-text-input id="data_inicio" name="data_inicio" type="date" class="mt-1 block w-full" :value="old('data_inicio', isset($contrato) ? $contrato->data_inicio->format('Y-m-d') : '')" required />
            </div>
            <div>
                <x-input-label for="data_termino" value="Data de Término" />
                <x-text-input id="data_termino" name="data_termino" type="date" class="mt-1 block w-full" :value="old('data_termino', isset($contrato) && $contrato->data_termino ? $contrato->data_termino->format('Y-m-d') : '')" />
            </div>
            <div>
                <x-input-label for="coordenador_id" value="Coordenador" />
                <select name="coordenador_id" id="coordenador_id" class="form-select mt-1 block w-full">
                    <option value="">Selecione um coordenador</option>
                    @foreach($coordenadores as $coordenador)
                        <option value="{{ $coordenador->id }}" @selected(old('coordenador_id', $contrato->coordenador_id ?? '') == $coordenador->id)>{{ $coordenador->nome }}</option>
                    @endforeach
                </select>
            </div>
            <div id="tech_lead_container" class="{{ old('tipo_contrato', $contrato->tipo_contrato ?? '') === 'ACT+' ? '' : 'hidden' }}">
                <x-input-label for="tech_lead_id" value="Tech Lead *" />
                <select name="tech_lead_id" id="tech_lead_id" class="form-select mt-1 block w-full">
                    <option value="">Selecione um Tech Lead</option>
                    @foreach($techLeads as $techLead)
                        <option value="{{ $techLead->id }}" @selected(old('tech_lead_id', $contrato->tech_lead_id ?? '') == $techLead->id)>{{ $techLead->nome }}</option>
                    @endforeach
                </select>
            </div>
             <div class="md:col-span-2">
                <x-input-label for="contato_principal" value="Contato Principal no Cliente" />
                <x-text-input id="contato_principal" name="contato_principal" type="text" class="mt-1 block w-full" :value="old('contato_principal', $contrato->contato_principal ?? '')" />
            </div>
        </div>
    </div>

    <!-- Seção 3: Escopo e Detalhes Financeiros -->
    <div class="p-6 bg-white border border-slate-200 rounded-lg shadow-sm">
        <h3 class="text-lg font-semibold text-slate-800 mb-4">Escopo e Detalhes Financeiros</h3>
        <div>
            <x-input-label value="Produtos Contratados *" />
            <div class="mt-2 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
                @foreach(['Protheus', 'RM', 'Fluig', 'Datasul', 'Outro'] as $produto)
                    <label class="flex items-center">
                        <input type="checkbox" name="produtos[]" value="{{ $produto }}" class="form-checkbox rounded" @checked(in_array($produto, old('produtos', $contrato->produtos ?? [])))>
                        <span class="ml-2 text-sm text-slate-600">{{ $produto }}</span>
                    </label>
                @endforeach
            </div>
        </div>
        <div id="especifique_outro_container" class="mt-4 {{ in_array('Outro', old('produtos', $contrato->produtos ?? [])) ? '' : 'hidden' }}">
            <x-input-label for="especifique_outro" value="Especifique o Outro Produto" />
            <x-text-input id="especifique_outro" name="especifique_outro" type="text" class="mt-1 block w-full" :value="old('especifique_outro', $contrato->especifique_outro ?? '')" />
        </div>
        <hr class="my-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
             <div>
                <x-input-label for="baseline_horas_mes" value="Baseline (Horas/mês)" />
                <x-text-input id="baseline_horas_mes" name="baseline_horas_mes" type="number" step="0.01" class="mt-1 block w-full" :value="old('baseline_horas_mes', $contrato->baseline_horas_mes ?? '')" />
            </div>
            <div class="pt-6">
                <label class="flex items-center">
                    <input type="checkbox" name="permite_antecipar_baseline" id="permite_antecipar_baseline" value="1" class="form-checkbox rounded" @checked(old('permite_antecipar_baseline', $contrato->permite_antecipar_baseline ?? false))>
                    <span class="ml-2 text-sm text-slate-600">Permite antecipar baseline?</span>
                </label>
            </div>
        </div>
    </div>
</div>

<div class="flex items-center justify-end mt-8 pt-5 border-t border-slate-200">
    <a href="{{ route('contratos.index') }}" class="btn bg-white border-slate-200 hover:border-slate-300 text-slate-600">Cancelar</a>
    <x-primary-button class="ml-4">{{ isset($contrato) ? 'Atualizar Contrato' : 'Salvar Contrato' }}</x-primary-button>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tipoContratoSelect = document.getElementById('tipo_contrato');
        const techLeadContainer = document.getElementById('tech_lead_container');
        const produtosCheckboxes = document.querySelectorAll('input[name="produtos[]"]');
        const especifiqueOutroContainer = document.getElementById('especifique_outro_container');
        const outroCheckbox = document.querySelector('input[value="Outro"]');

        function toggleTechLead() {
            if (tipoContratoSelect.value === 'ACT+') {
                techLeadContainer.classList.remove('hidden');
            } else {
                techLeadContainer.classList.add('hidden');
            }
        }

        function toggleEspecifiqueOutro() {
            if (outroCheckbox.checked) {
                especifiqueOutroContainer.classList.remove('hidden');
            } else {
                especifiqueOutroContainer.classList.add('hidden');
            }
        }

        tipoContratoSelect.addEventListener('change', toggleTechLead);
        produtosCheckboxes.forEach(checkbox => {
            if (checkbox.value === 'Outro') {
                checkbox.addEventListener('change', toggleEspecifiqueOutro);
            }
        });
        
        // Initial checks on page load
        toggleTechLead();
        toggleEspecifiqueOutro();
    });
</script>
@endpush
