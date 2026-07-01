<?php
require_once 'api/db.php';
$db = Database::getConnection();

// Assegura que a tabela existe
try {
    $db->exec("CREATE TABLE IF NOT EXISTS settings (
        `key` VARCHAR(50) PRIMARY KEY,
        `value` VARCHAR(255) NOT NULL
    )");
} catch (PDOException $e) {}

// Carregar Configurações de FTP
$ftp_config = [];
try {
    $stmt = $db->query("SELECT * FROM settings");
    $raw_settings = $stmt->fetchAll();
    foreach ($raw_settings as $s) {
        $ftp_config[$s['key']] = $s['value'];
    }
} catch (PDOException $e) {}

$page_title = 'Deploy FTP';
require_once 'layout/header.php';

$is_ready = !empty($ftp_config['ftp_host']) && !empty($ftp_config['ftp_user']) && !empty($ftp_config['ftp_pass']);
?>

<div class="mb-8">
    <h2 class="text-3xl font-black text-gray-900 tracking-tight">Deploy FTP</h2>
    <p class="text-sm text-gray-500 font-medium">Sincronize sua versão local com o servidor de produção.</p>
</div>

<?php if (!$is_ready): ?>
    <div class="bg-gray-50 border border-gray-200 p-8 rounded-3xl text-center">
        <div class="w-16 h-16 bg-[#2563eb]/10 text-[#2563eb] rounded-2xl flex items-center justify-center mx-auto mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        </div>
        <h3 class="text-xl font-bold text-gray-800 mb-2">Configurações Incompletas</h3>
        <p class="text-gray-500 mb-6 text-sm max-w-md mx-auto">As credenciais do servidor FTP não foram encontradas. É necessário adicioná-las na tabela <code>settings</code> do banco de dados (chaves: ftp_host, ftp_user, ftp_pass, ftp_path).</p>
    </div>
<?php else: ?>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm">
                <h4 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#2563eb]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 7h-9l-1-4H3c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h17c1.1 0 2-.9 2-2V9c0-1.1-.9-2-2-2z"/></svg>
                    Resumo do Destino
                </h4>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-400">Servidor:</span>
                        <span class="font-medium text-gray-700"><?php echo $ftp_config['ftp_host']; ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Pasta:</span>
                        <span class="font-medium text-gray-700"><?php echo $ftp_config['ftp_path']; ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Usuário:</span>
                        <span class="font-medium text-gray-700"><?php echo $ftp_config['ftp_user']; ?></span>
                    </div>
                </div>
                <hr class="my-6 border-gray-50">
                <form id="deploy-form" class="space-y-4">
                    <label class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl cursor-pointer">
                        <input type="checkbox" name="include_db" class="w-5 h-5 accent-[#2563eb]">
                        <span class="text-sm font-medium text-gray-700">Enviar Banco de Dados Local</span>
                    </label>
                    <button type="submit" id="start-deploy" class="w-full bg-[#2563eb] text-white px-6 py-4 rounded-xl font-bold hover:bg-[#1d4ed8] shadow-lg shadow-[#2563eb]/30 transition-all flex items-center justify-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/><polyline points="16 16 12 12 8 16"/></svg>
                        Iniciar Sincronização
                    </button>
                </form>
            </div>
            
            <div class="p-4 bg-red-50 rounded-2xl border border-red-100">
                <p class="text-[10px] text-red-700 leading-relaxed uppercase font-bold mb-1 tracking-wider">Atenção</p>
                <p class="text-xs text-red-600 opacity-80">Arquivos no servidor com o mesmo nome serão substituídos silenciosamente pela versão local mais recente.</p>
            </div>
        </div>

        <div class="lg:col-span-2">
            <div class="bg-gray-900 rounded-3xl border border-gray-800 shadow-xl overflow-hidden flex flex-col h-[600px]">
                <div class="px-6 py-4 border-b border-gray-800 bg-gray-800/50 flex items-center justify-between">
                    <div class="flex gap-1.5">
                        <div class="w-3 h-3 rounded-full bg-red-400"></div>
                        <div class="w-3 h-3 rounded-full bg-[#2563eb]"></div>
                        <div class="w-3 h-3 rounded-full bg-[#2563eb]"></div>
                    </div>
                    <span class="text-[10px] text-gray-500 font-bold uppercase tracking-widest">WebEstoque - Deploy Terminal</span>
                </div>
                <div id="deploy-log" class="flex-1 p-6 font-mono text-sm overflow-y-auto space-y-1.5 custom-scrollbar text-gray-300">
                    <p class="text-gray-500 italic">Aguardando comando...</p>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
    const log = document.getElementById('deploy-log');
    const form = document.getElementById('deploy-form');
    const btn = document.getElementById('start-deploy');

    function appendLog(category, message) {
        if (!log) return;
        const p = document.createElement('p');
        const time = new Date().toLocaleTimeString();
        let color = "text-gray-400";
        if (category === 'success') color = "text-[#2563eb]";
        if (category === 'error') color = "text-red-400";
        if (category === 'info') color = "text-amber-400";
        
        p.className = `text-[13px] ${color}`;
        p.innerHTML = `<span class="text-[11px] opacity-40 mr-2">[${time}]</span> ${message}`;
        log.appendChild(p);
        log.scrollTop = log.scrollHeight;
    }

    if (form) {
        const dbCheckbox = form.querySelector('[name="include_db"]');
        if (dbCheckbox) {
            dbCheckbox.addEventListener('change', async function() {
                if (this.checked) {
                    const confirmed = await WebEstoque.confirm(
                        'ATENÇÃO: O banco de dados em PRODUÇÃO será completamente substituído pelo banco local. Esta ação é irreversível e pode causar perda de dados se o servidor remoto tiver informações novas. Deseja realmente prosseguir?',
                        'Substituir Banco de Dados',
                        'danger'
                    );
                    if (!confirmed) {
                        this.checked = false;
                    }
                }
            });
        }

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const includeDb = form.querySelector('[name="include_db"]').checked;
            
            const confirmed = await WebEstoque.confirm('Esta ação irá substituir os arquivos no servidor remoto. Deseja prosseguir?', 'Atenção: Deploy em Produção', 'danger');
            if (!confirmed) return;

            btn.disabled = true;
            btn.innerHTML = 'Sincronizando...';
            log.innerHTML = '';
            appendLog('info', 'Iniciando processo de deploy...');

            try {
                const eventSource = new EventSource(`api/run-deploy.php?include_db=${includeDb ? 1 : 0}`);
                
                eventSource.onmessage = (event) => {
                    const data = JSON.parse(event.data);
                    if (data.type === 'progress') {
                        appendLog(data.status, `Enviando: ${data.file}`);
                    } else if (data.type === 'finished') {
                        appendLog('success', 'Sincronização concluída com sucesso!');
                        eventSource.close();
                        btn.disabled = false;
                        btn.innerHTML = 'Sincronização Concluída!';
                        setTimeout(() => {
                            btn.innerHTML = 'Iniciar Sincronização';
                        }, 3000);
                    } else if (data.type === 'error') {
                        appendLog('error', `Erro: ${data.message}`);
                        eventSource.close();
                        btn.disabled = false;
                        btn.innerHTML = 'Tentar Novamente';
                    }
                };

                eventSource.onerror = () => {
                    appendLog('error', 'Erro na conexão com o servidor de deploy.');
                    eventSource.close();
                    btn.disabled = false;
                };

            } catch (error) {
                appendLog('error', 'Falha ao iniciar o serviço de deploy.');
                btn.disabled = false;
            }
        });
    }
</script>

<style>
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: rgba(0,0,0,0.1);
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: rgba(255,255,255,0.05);
        border-radius: 10px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: rgba(255,255,255,0.1);
    }
</style>

<?php require_once 'layout/footer.php'; ?>
