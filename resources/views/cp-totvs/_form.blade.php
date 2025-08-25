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

<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <x-input-label for="nome" value="Nome *" />
            <x-text-input id="nome" name="nome" type="text" class="mt-1 block w-full" :value="old('nome', $cp->nome ?? '')" required />
        </div>
        <div>
            <x-input-label for="email" value="Email *" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $cp->email ?? '')" required />
        </div>
        <div>
            <x-input-label for="telefone" value="Telefone" />
            <x-text-input id="telefone" name="telefone" type="text" class="mt-1 block w-full" :value="old('telefone', $cp->telefone ?? '')" />
        </div>
        <div>
            <x-input-label for="status" value="Status *" />
            <select name="status" id="status" class="form-select mt-1 block w-full" required>
                <option value="Ativo" @selected(old('status', $cp->status ?? 'Ativo') == 'Ativo')>Ativo</option>
                <option value="Inativo" @selected(old('status', $cp->status ?? '') == 'Inativo')>Inativo</option>
            </select>
        </div>
    </div>
</div>

<div class="flex items-center justify-end mt-8 pt-5 border-t border-slate-200">
    <a href="{{ route('cp-totvs.index') }}" class="btn bg-white border-slate-200 hover:border-slate-300 text-slate-600">Cancelar</a>
    <x-primary-button class="ml-4">{{ $cp->exists ? 'Atualizar' : 'Salvar' }}</x-primary-button>
</div>