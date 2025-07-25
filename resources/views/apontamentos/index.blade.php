<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div id="calendar"></div>
        </div>
    </div>

    <div id="apontamentoModal" class="fixed z-10 inset-0 overflow-y-auto hidden">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form id="apontamentoForm" enctype="multipart/form-data">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Lançar Apontamento</h3>
                        <div class="mt-4 space-y-4">
                            <input type="hidden" id="agenda_id" name="agenda_id">
                            <div id="info_rejeicao" class="hidden p-3 bg-red-50 border-l-4 border-red-400 text-red-700">
                                <p class="font-bold text-sm">Motivo da Rejeição:</p>
                                <p class="text-sm" id="motivo_rejeicao_text"></p>
                            </div>
                            <div>
                                <p><strong>Consultor:</strong> <span id="modal_consultor"></span></p>
                                <p><strong>Assunto:</strong> <span id="modal_assunto"></span></p>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="hora_inicio" class="block text-sm font-medium text-gray-700">De</label>
                                    <input type="time" name="hora_inicio" id="hora_inicio" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                </div>
                                <div>
                                    <label for="hora_fim" class="block text-sm font-medium text-gray-700">Até</label>
                                    <input type="time" name="hora_fim" id="hora_fim" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                </div>
                            </div>
                            <div>
                                <label for="descricao" class="block text-sm font-medium text-gray-700">Descrição das Atividades</label>
                                <textarea id="descricao" name="descricao" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required></textarea>
                            </div>
                            <div>
                                <label for="anexo" class="block text-sm font-medium text-gray-700">Anexo (Opcional)</label>
                                <input type="file" name="anexo" id="anexo" class="mt-1 block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                <div id="anexo_existente" class="mt-2"></div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" id="saveButton" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 sm:ml-3 sm:w-auto sm:text-sm">Salvar</button>
                        <button type="button" id="closeModalButton" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    @push('scripts')
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('calendar');
            const modal = document.getElementById('apontamentoModal');
            const form = document.getElementById('apontamentoForm');
            const closeModalButton = document.getElementById('closeModalButton');
            
            const calendar = new FullCalendar.Calendar(calendarEl, {
                headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,timeGridDay' },
                initialView: 'dayGridMonth',
                locale: 'pt-br',
                events: '{{ route("api.agendas") }}',
                eventClick: function(info) {
                    const props = info.event.extendedProps;
                    
                    form.reset();
                    document.getElementById('agenda_id').value = info.event.id;
                    document.getElementById('modal_consultor').textContent = props.consultor;
                    document.getElementById('modal_assunto').textContent = props.assunto;
                    document.getElementById('hora_inicio').value = props.hora_inicio;
                    document.getElementById('hora_fim').value = props.hora_fim;
                    document.getElementById('descricao').value = props.descricao;
                    document.getElementById('anexo_existente').innerHTML = props.anexo_url ? `<a href="${props.anexo_url}" target="_blank" class="text-indigo-600 hover:underline">Ver anexo atual</a>` : '';
                    
                    const infoRejeicao = document.getElementById('info_rejeicao');
                    if (props.status === 'Rejeitado' && props.motivo_rejeicao) {
                        document.getElementById('motivo_rejeicao_text').textContent = props.motivo_rejeicao;
                        infoRejeicao.classList.remove('hidden');
                    } else {
                        infoRejeicao.classList.add('hidden');
                    }

                    const saveButton = document.getElementById('saveButton');
                    const inputs = form.querySelectorAll('input, textarea, button');
                    if (props.status === 'Aprovado') {
                        inputs.forEach(el => el.disabled = true);
                        saveButton.textContent = 'Aprovado';
                    } else {
                        inputs.forEach(el => el.disabled = false);
                        saveButton.textContent = 'Salvar';
                    }

                    modal.classList.remove('hidden');
                }
            });

            calendar.render();

            closeModalButton.addEventListener('click', () => modal.classList.add('hidden'));

            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(form);

                fetch('{{ route("apontamentos.storeOrUpdate") }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    body: formData
                })
                .then(response => response.json())
                .then(result => {
                    alert(result.message);
                    if(result.message.includes('sucesso')) {
                        modal.classList.add('hidden');
                        calendar.refetchEvents();
                    }
                })
                .catch(error => { console.error('Error:', error); alert('Ocorreu um erro.'); });
            });
        });
    </script>
    @endpush
</x-app-layout>
