        </div>
    </main>

    <!-- Custom Modal -->
    <div id="webestoque-modal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-gray-900/40 backdrop-blur-sm opacity-0 pointer-events-none transition-all duration-300">
        <div id="webestoque-modal-card" class="bg-white rounded-3xl shadow-2xl border border-gray-100 w-full max-w-md transform scale-95 transition-all duration-300">
            <div class="p-8">
                <div id="webestoque-modal-icon" class="w-16 h-16 rounded-2xl mb-6 flex items-center justify-center"></div>
                <h3 id="webestoque-modal-title" class="text-2xl font-bold text-gray-900 mb-2"></h3>
                <p id="webestoque-modal-message" class="text-gray-500 font-medium leading-relaxed"></p>
                
                <div class="mt-8 flex gap-3" id="webestoque-modal-buttons">
                    <button id="webestoque-modal-cancel" class="flex-1 px-6 py-3 text-gray-600 font-bold hover:bg-gray-50 rounded-xl transition-all">Cancelar</button>
                    <button id="webestoque-modal-confirm" class="flex-1 px-6 py-3 bg-[#2563eb] text-white font-bold hover:bg-[#1d4ed8] rounded-xl shadow-lg shadow-[#2563eb]/30 transition-all"></button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const WebEstoque = {
            modal: document.getElementById('webestoque-modal'),
            card: document.getElementById('webestoque-modal-card'),
            title: document.getElementById('webestoque-modal-title'),
            message: document.getElementById('webestoque-modal-message'),
            icon: document.getElementById('webestoque-modal-icon'),
            btnConfirm: document.getElementById('webestoque-modal-confirm'),
            btnCancel: document.getElementById('webestoque-modal-cancel'),
            
            show(options) {
                this.title.textContent = options.title || 'Aviso';
                this.message.textContent = options.message || '';
                this.btnConfirm.textContent = options.confirmText || 'Confirmar';
                this.btnCancel.textContent = options.cancelText || 'Cancelar';
                this.btnCancel.style.display = options.showCancel ? 'block' : 'none';
                
                const type = options.type || 'warning';
                if (type === 'danger') {
                    this.icon.className = 'w-16 h-16 rounded-2xl mb-6 flex items-center justify-center bg-red-50 text-red-500';
                    this.icon.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>';
                    this.btnConfirm.className = 'flex-1 px-6 py-3 bg-red-500 text-white font-bold hover:bg-red-600 rounded-xl shadow-lg shadow-red-200 transition-all';
                } else if (type === 'success') {
                    this.icon.className = 'w-16 h-16 rounded-2xl mb-6 flex items-center justify-center bg-emerald-50 text-emerald-500';
                    this.icon.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>';
                    this.btnConfirm.className = 'flex-1 px-6 py-3 bg-emerald-500 text-white font-bold hover:bg-emerald-600 rounded-xl shadow-lg shadow-emerald-200 transition-all';
                } else {
                    this.icon.className = 'w-16 h-16 rounded-2xl mb-6 flex items-center justify-center bg-[#2563eb]/10 text-[#2563eb]';
                    this.icon.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>';
                    this.btnConfirm.className = 'flex-1 px-6 py-3 bg-[#2563eb] text-white font-bold hover:bg-[#1d4ed8] rounded-xl shadow-lg shadow-[#2563eb]/30 transition-all';
                }

                this.modal.classList.remove('opacity-0', 'pointer-events-none');
                this.card.classList.remove('scale-95');
                
                return new Promise((resolve) => {
                    const handleConfirm = () => {
                        this.hide();
                        resolve(true);
                        this.btnConfirm.removeEventListener('click', handleConfirm);
                        this.btnCancel.removeEventListener('click', handleCancel);
                    };
                    const handleCancel = () => {
                        this.hide();
                        resolve(false);
                        this.btnCancel.removeEventListener('click', handleCancel);
                        this.btnConfirm.removeEventListener('click', handleConfirm);
                    };
                    this.btnConfirm.addEventListener('click', handleConfirm);
                    this.btnCancel.addEventListener('click', handleCancel);
                });
            },
            
            hide() {
                this.modal.classList.add('opacity-0', 'pointer-events-none');
                this.card.classList.add('scale-95');
            },

            async confirm(message, title = 'Confirmação', type = 'warning') {
                return await this.show({
                    title,
                    message,
                    type,
                    showCancel: true,
                    confirmText: 'Sim, continuar'
                });
            },

            async alert(message, title = 'Aviso') {
                return await this.show({
                    title,
                    message,
                    type: 'info',
                    showCancel: false,
                    confirmText: 'Entendido'
                });
            },

            toast(message, type = 'success') {
                const toast = document.createElement('div');
                toast.className = `fixed bottom-8 left-1/2 -translate-x-1/2 px-6 py-3 rounded-2xl shadow-2xl z-[9999] font-bold text-white transition-all duration-500 translate-y-10 opacity-0 ${type==='success'?'bg-emerald-500':'bg-red-500'}`;
                toast.textContent = message;
                document.body.appendChild(toast);
                setTimeout(() => {
                    toast.classList.remove('translate-y-10', 'opacity-0');
                }, 10);
                setTimeout(() => {
                    toast.classList.add('translate-y-10', 'opacity-0');
                    setTimeout(() => toast.remove(), 500);
                }, 3000);
            }
        };
    </script>
</body>
</html>
