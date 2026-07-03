<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$action = $_GET['action'] ?? '';

// ============================================================
// AÇÃO: DOWNLOAD (Backup Completo dos Arquivos)
// ============================================================
if ($action === 'download') {
    $rootPath = str_replace('\\', '/', realpath(__DIR__));
    $rarName  = 'webestoque_backup_' . date('Ymd_His') . '.rar';
    $rarPath  = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $rarName;

    ini_set('memory_limit', '512M');
    set_time_limit(600);

    // Tentativa 1: ZipArchive (PHP nativo)
    if (class_exists('ZipArchive')) {
        $zipPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'webestoque_tmp_' . time() . '.zip';
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($rootPath, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::LEAVES_ONLY
            );
            foreach ($files as $file) {
                if (!$file->isDir()) {
                    $filePath     = str_replace('\\', '/', $file->getRealPath());
                    $relativePath = str_replace($rootPath . '/', '', $filePath);
                    if (
                        strpos($filePath, '.bak')        === false &&
                        strpos($filePath, '/.git/')      === false &&
                        strpos($filePath, '/node_modules/') === false &&
                        strpos($filePath, '/.claude/')   === false &&
                        strpos($filePath, '/.gemini/')   === false &&
                        !preg_match('/\.(zip|rar)$/', basename($filePath))
                    ) {
                        $zip->addFile($filePath, $relativePath);
                    }
                }
            }
            $zip->close();
            // Renomear para .rar (o conteúdo é zip, mas tar consegue extrair)
            if (file_exists($zipPath) && filesize($zipPath) > 0) {
                rename($zipPath, $rarPath);
            }
        }
    }

    // Tentativa 2: tar (nativo Windows 10/11)
    if (!file_exists($rarPath) || filesize($rarPath) === 0) {
        $destWin = str_replace('/', '\\', $rarPath);
        $cmd = "tar -a -c -f \"$destWin\" --exclude=node_modules --exclude=.git --exclude=.claude --exclude=.gemini --exclude=\"*.zip\" --exclude=\"*.rar\" --exclude=\"*.bak\" .";
        exec($cmd, $out, $ret);
    }

    if (file_exists($rarPath) && filesize($rarPath) > 0) {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $rarName . '"');
        header('Content-Length: ' . filesize($rarPath));
        header('Cache-Control: no-cache');
        readfile($rarPath);
        unlink($rarPath);
        exit;
    }

    http_response_code(500);
    die(json_encode(['error' => 'Não foi possível gerar o backup dos arquivos.']));
}

// ============================================================
// AÇÃO: RESTORE (Restauração via AJAX)
// ============================================================
if ($action === 'restore' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    ini_set('upload_max_filesize', '512M');
    ini_set('post_max_size', '512M');
    ini_set('memory_limit', '512M');
    set_time_limit(600);

    if (!isset($_FILES['restore_file']) || $_FILES['restore_file']['error'] !== UPLOAD_ERR_OK) {
        $errMsg = [
            UPLOAD_ERR_INI_SIZE   => 'Arquivo muito grande (limite do servidor).',
            UPLOAD_ERR_FORM_SIZE  => 'Arquivo muito grande (limite do formulário).',
            UPLOAD_ERR_NO_FILE    => 'Nenhum arquivo enviado.',
        ];
        $code = $_FILES['restore_file']['error'] ?? UPLOAD_ERR_NO_FILE;
        echo json_encode(['error' => $errMsg[$code] ?? 'Erro no upload do arquivo.']);
        exit;
    }

    $tmpFile  = $_FILES['restore_file']['tmp_name'];
    $origName = $_FILES['restore_file']['name'];
    $destDir  = realpath(__DIR__);

    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

    // Mover o arquivo para um local temporário permanente
    $tmpPermanent = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'webestoque_restore_' . time() . '.' . $ext;
    move_uploaded_file($tmpFile, $tmpPermanent);

    $success = false;
    $log     = [];

    if (in_array($ext, ['rar', 'zip', 'tar'])) {
        // Extrair usando tar (Windows nativo)
        $destWin = str_replace('/', '\\', $destDir);
        $srcWin  = str_replace('/', '\\', $tmpPermanent);
        $cmd = "tar -x -f \"$srcWin\" -C \"$destWin\" 2>&1";
        exec($cmd, $log, $ret);
        $success = ($ret === 0);
    } 

    @unlink($tmpPermanent);

    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Arquivos do sistema restaurados com sucesso!']);
    } else {
        echo json_encode([
            'error' => 'Falha ao extrair o backup.',
            'log'   => implode("\n", $log)
        ]);
    }
    exit;
}

$page_title = 'Backup e Restauração';
require_once 'layout/header.php';
?>

<div class="max-w-4xl mx-auto space-y-6" x-data="{ showSuccess: false }" @backup-success.window="showSuccess = true">
    <div class="mb-8">
        <h2 class="text-3xl font-black text-gray-900 tracking-tight">Backup e Restauração</h2>
        <p class="text-sm text-gray-500 font-medium">Salve uma cópia completa dos arquivos do sistema ou restaure a partir de um backup anterior.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
        <!-- Download Backup -->
        <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm flex flex-col group relative overflow-hidden">
            <div class="w-14 h-14 bg-[#2563eb]/10 text-[#2563eb] rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Backup de Arquivos</h3>
            <p class="text-gray-500 text-sm mb-8 leading-relaxed flex-1">Gera um arquivo compactado com todo o código fonte do sistema (não inclui o banco de dados MySQL).</p>

            <div id="export-progress" class="hidden mb-4">
                <div class="flex justify-between text-[10px] font-bold text-gray-400 uppercase mb-1">
                    <span id="export-status">Preparando arquivos...</span>
                    <span id="export-pct">0%</span>
                </div>
                <div class="w-full bg-gray-100 h-1.5 rounded-full overflow-hidden">
                    <div id="export-bar" class="bg-[#2563eb] h-full transition-all duration-300" style="width: 0%"></div>
                </div>
            </div>

            <button onclick="startExport()" id="btn-export" class="inline-flex items-center justify-center w-full bg-[#2563eb] text-white py-4 rounded-xl font-bold hover:bg-[#1d4ed8] transition-all shadow-lg shadow-[#2563eb]/30">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                Compactar e Salvar Backup
            </button>
        </div>

        <!-- Restore Backup -->
        <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm flex flex-col group relative overflow-hidden">
            <div class="w-14 h-14 bg-gray-100 text-gray-600 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Restaurar Sistema</h3>
            <p class="text-gray-500 text-sm mb-8 leading-relaxed flex-1">Selecione um arquivo de backup gerado por este sistema. <strong class="text-red-500">Os arquivos locais serão substituídos.</strong></p>

            <div id="restore-progress" class="hidden mb-4">
                <div class="flex justify-between text-[10px] font-bold text-gray-400 uppercase mb-1">
                    <span id="restore-status">Enviando arquivo...</span>
                    <span id="restore-pct">0%</span>
                </div>
                <div class="w-full bg-gray-100 h-1.5 rounded-full overflow-hidden">
                    <div id="restore-bar" class="bg-gray-600 h-full transition-all duration-300" style="width: 0%"></div>
                </div>
            </div>

            <input type="file" accept=".rar,.zip" class="hidden" id="restore-input" onchange="startRestore(this)">
            <button type="button" onclick="document.getElementById('restore-input').click()" id="btn-restore" class="w-full bg-gray-800 text-white py-4 rounded-xl font-bold hover:bg-gray-900 transition-all shadow-lg shadow-gray-200 flex items-center justify-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Selecionar Arquivo de Backup
            </button>
        </div>
    </div>

    <!-- Success Modal Elegante -->
    <div x-show="showSuccess" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
        <div class="absolute inset-0 bg-[#111625]/70 backdrop-blur-md" @click="showSuccess = false"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"></div>
             
        <div class="relative w-full max-w-sm bg-white rounded-[2rem] shadow-2xl p-8 text-center z-10"
             x-transition:enter="transition ease-out duration-300 delay-100"
             x-transition:enter-start="opacity-0 scale-90 translate-y-8"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-90 translate-y-4">
            
            <div class="mx-auto w-24 h-24 bg-emerald-50 rounded-full flex items-center justify-center text-emerald-500 mb-6 relative">
                <div class="absolute inset-0 bg-emerald-100 rounded-full animate-ping opacity-25"></div>
                <svg class="w-12 h-12 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            
            <h3 class="text-2xl font-black text-gray-900 mb-3 tracking-tight">Backup Concluído!</h3>
            <p class="text-sm text-gray-500 mb-8 leading-relaxed font-medium px-2">Seus arquivos foram compactados e baixados com sucesso. Guarde-os em um local seguro.</p>
            
            <button @click="showSuccess = false" class="w-full rounded-2xl bg-[#2563eb] py-4 text-sm font-bold text-white shadow-lg shadow-[#2563eb]/30 hover:bg-[#1d4ed8] hover:shadow-xl hover:-translate-y-0.5 transition-all">
                Entendido
            </button>
        </div>
    </div>
</div>

<script>


async function startExport() {
    const btn    = document.getElementById('btn-export');
    const prog   = document.getElementById('export-progress');
    const bar    = document.getElementById('export-bar');
    const pct    = document.getElementById('export-pct');
    const status = document.getElementById('export-status');

    const setProgress = (v, msg) => {
        bar.style.width = v + '%';
        pct.textContent = v + '%';
        if (msg) status.textContent = msg;
    };

    btn.disabled = true;
    btn.classList.add('opacity-50');
    prog.classList.remove('hidden');
    setProgress(5, 'Aguardando escolha do local...');

    try {
        const fileName = `webestoque_backup_${new Date().toISOString().slice(0,10)}.rar`;

        let fileHandle = null;
        if ('showSaveFilePicker' in window) {
            try {
                fileHandle = await window.showSaveFilePicker({
                    suggestedName: fileName,
                    types: [{ description: 'Arquivo de Backup RAR', accept: { 'application/octet-stream': ['.rar'] } }],
                });
            } catch (e) {
                if (e.name === 'AbortError') {
                    setProgress(0, 'Operação cancelada.');
                    return;
                }
                fileHandle = null;
            }
        }

        setProgress(15, 'Gerando backup no servidor...');

        const response = await fetch('backup.php?action=download');
        if (!response.ok) throw new Error('Erro ao gerar backup no servidor.');

        setProgress(40, 'Recebendo arquivo compactado...');

        const contentLength = response.headers.get('Content-Length');
        const reader = response.body.getReader();
        const chunks = [];
        let received = 0;

        while (true) {
            const { done, value } = await reader.read();
            if (done) break;
            chunks.push(value);
            received += value.length;
            if (contentLength) {
                const p = 40 + Math.round((received / parseInt(contentLength)) * 40);
                setProgress(Math.min(p, 80), 'Recebendo arquivo compactado...');
            }
        }

        const blob = new Blob(chunks);
        setProgress(85, 'Salvando arquivo...');

        if (fileHandle) {
            const writable = await fileHandle.createWritable();
            await writable.write(blob);
            await writable.close();
            setProgress(100, 'Backup salvo com sucesso!');
            window.dispatchEvent(new CustomEvent('backup-success'));
        } else {
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url; a.download = fileName;
            document.body.appendChild(a); a.click(); document.body.removeChild(a);
            setTimeout(() => URL.revokeObjectURL(url), 5000);
            setProgress(100, 'Download concluído!');
            window.dispatchEvent(new CustomEvent('backup-success'));
        }
    } catch (err) {
        console.error(err);
        WebEstoque.toast('Erro ao gerar backup: ' + err.message, 'error');
        setProgress(0, 'Erro ao gerar backup.');
    } finally {
        setTimeout(() => {
            prog.classList.add('hidden');
            btn.disabled = false;
            btn.classList.remove('opacity-50');
            bar.style.width = '0%';
            pct.textContent = '0%';
        }, 3000);
    }
}

async function startRestore(input) {
    const file = input.files[0];
    if (!file) return;
    input.value = '';

    const confirmed = await WebEstoque.confirm(
        `Todos os arquivos atuais do sistema serão substituídos.\nDeseja continuar?`
    );

    if (!confirmed) return;

    const btn    = document.getElementById('btn-restore');
    const prog   = document.getElementById('restore-progress');
    const bar    = document.getElementById('restore-bar');
    const pct    = document.getElementById('restore-pct');
    const status = document.getElementById('restore-status');

    btn.disabled = true;
    btn.classList.add('opacity-50');
    prog.classList.remove('hidden');

    const setProgress = (v, msg) => {
        bar.style.width = v + '%';
        pct.textContent = v + '%';
        if (msg) status.textContent = msg;
    };

    try {
        setProgress(5, 'Enviando arquivo para o servidor...');

        const formData = new FormData();
        formData.append('restore_file', file);

        const result = await new Promise((resolve, reject) => {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'backup.php?action=restore');

            xhr.upload.onprogress = (e) => {
                if (e.lengthComputable) {
                    const p = Math.round((e.loaded / e.total) * 70);
                    setProgress(p, 'Enviando arquivo para o servidor...');
                }
            };

            xhr.onload = () => {
                try { resolve(JSON.parse(xhr.responseText)); }
                catch(e) { reject(new Error('Resposta inválida do servidor.')); }
            };
            xhr.onerror = () => reject(new Error('Falha na comunicação com o servidor.'));
            xhr.send(formData);
        });

        setProgress(80, 'Extraindo arquivos do backup...');
        await new Promise(r => setTimeout(r, 500));
        setProgress(95, 'Finalizando restauração...');
        await new Promise(r => setTimeout(r, 500));

        if (result.success) {
            setProgress(100, 'Sistema restaurado com sucesso!');
            WebEstoque.toast('Sistema restaurado! A página será recarregada.', 'success');
            setTimeout(() => window.location.reload(), 2500);
        } else {
            throw new Error(result.error || 'Erro desconhecido ao restaurar.');
        }
    } catch (err) {
        console.error(err);
        WebEstoque.toast('Erro ao restaurar: ' + err.message, 'error');
        setProgress(0, 'Erro na restauração.');
        btn.disabled = false;
        btn.classList.remove('opacity-50');
        setTimeout(() => prog.classList.add('hidden'), 3000);
    }
}
</script>

<?php require_once 'layout/footer.php'; ?>
