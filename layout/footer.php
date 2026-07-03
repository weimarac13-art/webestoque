        </div>
    </main>

    <!-- Mobile Bottom Navigation -->
    <div x-data="{ showCadastroMenu: false }" class="lg:hidden">
        <!-- Overlay -->
        <div x-show="showCadastroMenu" 
             x-transition.opacity 
             class="fixed inset-0 bg-gray-900/40 z-20 backdrop-blur-sm" 
             @click="showCadastroMenu = false"
             style="display: none;"></div>
             
        <!-- Slide-up Menu -->
        <div x-show="showCadastroMenu" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="translate-y-full"
             x-transition:enter-end="translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="translate-y-0"
             x-transition:leave-end="translate-y-full"
             class="fixed bottom-[72px] left-0 right-0 bg-white rounded-t-[2rem] shadow-[0_-10px_40px_rgba(0,0,0,0.1)] z-30 p-8 border-t border-gray-100"
             style="display: none;">
            
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-sm font-black text-gray-900 uppercase tracking-widest">O que deseja cadastrar?</h3>
                <button @click="showCadastroMenu = false" class="p-2 bg-gray-50 rounded-full text-gray-400 hover:text-gray-900 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <div class="grid grid-cols-3 gap-4">
                <a href="agencias.php" class="flex flex-col items-center justify-center p-4 bg-gray-50 border border-gray-100 rounded-3xl text-gray-500 hover:text-[#2563eb] hover:bg-blue-50 hover:border-blue-100 transition-all active:scale-95 group">
                    <div class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center mb-3 shadow-sm group-hover:bg-[#2563eb] group-hover:text-white transition-colors text-gray-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    </div>
                    <span class="text-xs font-bold text-gray-700 group-hover:text-[#2563eb]">Agência</span>
                </a>
                <a href="tecnicos.php" class="flex flex-col items-center justify-center p-4 bg-gray-50 border border-gray-100 rounded-3xl text-gray-500 hover:text-[#2563eb] hover:bg-blue-50 hover:border-blue-100 transition-all active:scale-95 group">
                    <div class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center mb-3 shadow-sm group-hover:bg-[#2563eb] group-hover:text-white transition-colors text-gray-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </div>
                    <span class="text-xs font-bold text-gray-700 group-hover:text-[#2563eb]">Técnico</span>
                </a>
                <a href="produtos.php" class="flex flex-col items-center justify-center p-4 bg-gray-50 border border-gray-100 rounded-3xl text-gray-500 hover:text-[#2563eb] hover:bg-blue-50 hover:border-blue-100 transition-all active:scale-95 group">
                    <div class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center mb-3 shadow-sm group-hover:bg-[#2563eb] group-hover:text-white transition-colors text-gray-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <rect x="2" y="8" width="14" height="10" rx="2" stroke-width="2" />
                            <line x1="6" y1="12" x2="6.01" y2="12" stroke-width="2" stroke-linecap="round" />
                            <line x1="10" y1="12" x2="10.01" y2="12" stroke-width="2" stroke-linecap="round" />
                            <line x1="14" y1="12" x2="14.01" y2="12" stroke-width="2" stroke-linecap="round" />
                            <line x1="6" y1="15" x2="14" y2="15" stroke-width="2" stroke-linecap="round" />
                            <rect x="18" y="10" width="4" height="8" rx="2" stroke-width="2" />
                            <line x1="18" y1="13" x2="22" y2="13" stroke-width="2" />
                        </svg>
                    </div>
                    <span class="text-xs font-bold text-gray-700 group-hover:text-[#2563eb]">Peça</span>
                </a>
            </div>
        </div>

        <nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 z-40 flex justify-around items-center h-[72px] pb-safe shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]">
            <a href="index.php" class="flex flex-col items-center justify-center w-full h-full text-gray-400 hover:text-[#2563eb] transition-colors">
                <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                <span class="text-[10px] font-bold uppercase tracking-wider">Início</span>
            </a>
            <button @click="showCadastroMenu = !showCadastroMenu" class="flex flex-col items-center justify-center w-full h-full text-gray-400 hover:text-[#2563eb] transition-colors relative outline-none">
                <div class="absolute -top-6 bg-gradient-to-tr from-[#2563eb] to-[#3b82f6] text-white p-3.5 rounded-2xl shadow-xl shadow-[#2563eb]/30 border-4 border-[#f8fafc] transform transition-all duration-300" :class="showCadastroMenu ? 'scale-95 shadow-none bg-none bg-[#1e40af] border-gray-100' : 'active:scale-95'">
                    <svg class="w-6 h-6 transition-transform duration-300" :class="showCadastroMenu ? 'rotate-45' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                </div>
                <span class="text-[10px] font-bold uppercase tracking-wider mt-7 transition-colors duration-300" :class="showCadastroMenu ? 'text-[#1e40af]' : 'text-[#2563eb]'">Cadastrar</span>
            </button>
            <a href="logout.php" class="flex flex-col items-center justify-center w-full h-full text-red-500 hover:text-red-600 transition-colors">
                <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                <span class="text-[10px] font-bold uppercase tracking-wider">Sair</span>
            </a>
        </nav>
    </div>
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

        // PWA Service Worker Registration
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('sw.js').catch(error => {
                    console.log('SW Registration Failed: ', error);
                });
            });
        }
    </script>
</body>
</html>
