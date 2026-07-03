<?php
require_once 'middleware.php';
require_once 'api/db.php';
$db = Database::getConnection();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action']) && $_POST['action'] == 'save') {
        $id = $_POST['id'] ?? null;
        $codigo = substr(trim($_POST['codigo'] ?? ''), 0, 4);
        $nome = trim($_POST['nome'] ?? '');
        
        if ($id) {
            $stmt = $db->prepare("UPDATE agencies SET codigo = :codigo, nome = :nome WHERE id = :id");
            $stmt->execute([':id' => $id, ':codigo' => $codigo, ':nome' => $nome]);
            require_once 'api/logger.php';
            logAction($db, 'Editar', "Agência " . $nome . " editada.");
        } else {
            $id = 'ag-' . time();
            $stmt = $db->prepare("INSERT INTO agencies (id, codigo, nome, createdAt) VALUES (:id, :codigo, :nome, :createdAt)");
            $stmt->execute([':id' => $id, ':codigo' => $codigo, ':nome' => $nome, ':createdAt' => date('Y-m-d H:i:s')]);
            require_once 'api/logger.php';
            logAction($db, 'Novo', "Agência " . $nome . " cadastrada.");
        }
        header("Location: agencias.php?saved=1");
        exit;
    }
    
    if (isset($_POST['action']) && $_POST['action'] == 'delete') {
        $id = $_POST['id'] ?? null;
        if ($id) {
            $stmt = $db->prepare("DELETE FROM agencies WHERE id = :id");
            $stmt->execute([':id' => $id]);
            require_once 'api/logger.php';
            logAction($db, 'Excluir', "Agência ID " . $id . " excluída.");
        }
        header("Location: agencias.php");
        exit;
    }
}

$stmt = $db->query("SELECT * FROM agencies ORDER BY nome ASC");
$agencies = $stmt->fetchAll();

$showPrompt = isset($_GET['saved']) ? 'true' : 'false';

include 'layout/header.php';
?>

<div class="space-y-5" x-data="{ 
    modalOpen: false, 
    showPrompt: <?= $showPrompt ?>, 
    editingAgency: null, 
    formCodigo: '', 
    formNome: '', 
    formId: '',
    codeExists: false,
    agenciesList: <?= htmlspecialchars(json_encode(array_map(function($a) { return ['id' => $a['id'], 'codigo' => $a['codigo']]; }, $agencies)), ENT_QUOTES) ?>,
    checkCode() {
        let cod = this.formCodigo.trim().toUpperCase();
        this.codeExists = this.agenciesList.some(a => a.codigo.toUpperCase() === cod && a.id !== this.formId);
        if(cod.length === 4 && !this.codeExists) {
            setTimeout(() => document.getElementById('ag_nome').focus(), 10);
        }
    }
}">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900">Agências & Destinos</h1>
            <p class="text-xs text-gray-500">Controle de unidades de destino (Estoque CAIXA).</p>
        </div>
        <button 
            @click="modalOpen = true; editingAgency = null; formCodigo = ''; formNome = ''; formId = '';"
            class="flex items-center justify-center gap-1.5 px-4 h-11 rounded-xl bg-[#2563eb] text-xs font-extrabold text-white hover:bg-[#1d4ed8] active:scale-95 transition-all shadow-md shadow-[#2563eb]/30"
        >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
            Cadastrar
        </button>
    </div>

    <!-- List -->
    <div>
        <div class="mb-4 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-gray-800 text-sm tracking-wide uppercase">Agências Cadastradas</h3>
                <p class="text-xs text-gray-500 mt-0.5">Diretório oficial de agências do sistema.</p>
            </div>
            <span class="text-xs font-bold text-gray-400 bg-white px-3 py-1.5 border border-gray-100 rounded-xl shadow-sm">
                <?= count($agencies) ?> cadastrada(s)
            </span>
        </div>

        <?php
require_once 'middleware.php'; if (count($agencies) == 0): ?>
            <div class="p-12 text-center bg-white rounded-3xl border border-gray-100 shadow-sm">
                <p class="text-gray-400 text-sm font-medium">Nenhuma agência cadastrada ainda.</p>
            </div>
        <?php
require_once 'middleware.php'; else: ?>
            <div class="space-y-3">
                <?php
require_once 'middleware.php'; foreach ($agencies as $ag): ?>
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex items-center justify-between gap-4 hover:shadow-md hover:border-blue-100 hover:-translate-y-0.5 transition-all duration-300 group relative overflow-hidden">
                        
                        <!-- Subtle background gradient on hover -->
                        <div class="absolute inset-0 bg-gradient-to-r from-blue-50/0 via-blue-50/0 to-blue-50/30 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none"></div>

                        <div class="flex items-center gap-4 flex-1 relative z-10">
                            <div class="h-12 w-12 bg-gray-50 rounded-2xl flex items-center justify-center font-black text-gray-400 text-sm group-hover:bg-blue-50 group-hover:text-[#2563eb] group-hover:scale-105 transition-all duration-300">
                                <?= htmlspecialchars($ag['codigo']) ?>
                            </div>
                            <div>
                                <h4 class="text-base font-extrabold text-gray-900 group-hover:text-[#2563eb] transition-colors"><?= htmlspecialchars($ag['nome']) ?></h4>
                                <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mt-0.5">
                                    Adicionada em <?= date('d/m/Y', strtotime($ag['createdAt'])) ?>
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 relative z-10 opacity-50 group-hover:opacity-100 transition-opacity">
                            <button
                                @click="modalOpen = true; formId = '<?= $ag['id'] ?>'; formCodigo = '<?= htmlspecialchars($ag['codigo'], ENT_QUOTES) ?>'; formNome = '<?= htmlspecialchars($ag['nome'], ENT_QUOTES) ?>';"
                                class="p-2.5 rounded-xl text-gray-400 hover:text-[#2563eb] hover:bg-blue-50 transition-colors"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                            </button>
                            <form method="POST" action="agencias.php" onsubmit="event.preventDefault(); WebEstoque.confirm('Excluir esta agência?', 'Excluir Agência', 'danger').then(c => { if(c) this.submit(); });" class="inline">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $ag['id'] ?>">
                                <button type="submit" class="p-2.5 rounded-xl text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php
require_once 'middleware.php'; endforeach; ?>
            </div>
        <?php
require_once 'middleware.php'; endif; ?>
    </div>

    <!-- Modal Form -->
    <div x-show="modalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
        <div class="absolute inset-0 bg-[#111625]/60 backdrop-blur-sm" @click="modalOpen = false"></div>
        
        <div class="relative w-full max-w-md bg-white rounded-3xl shadow-2xl overflow-hidden border border-gray-100 z-10"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">
             
            <div class="flex items-center justify-between px-8 pt-8 pb-2">
                <div>
                    <h3 class="text-2xl font-black text-gray-900 tracking-tight" x-text="formId ? 'Editar Agência' : 'Cadastrar Agência'"></h3>
                </div>
                <button @click="modalOpen = false" type="button" class="p-1.5 rounded-full hover:bg-gray-200 text-gray-400 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <form method="POST" action="agencias.php" class="px-8 pb-8 pt-2 space-y-5" @submit="if(codeExists) { $event.preventDefault(); WebEstoque.toast('Código de agência já existe!', 'error'); }">
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="id" x-model="formId">
                
                <div>
                    <label class="block text-[11px] font-black text-slate-400 uppercase tracking-wider mb-1">Cód (4 chars) *</label>
                    <input type="text" inputmode="numeric" id="ag_codigo" name="codigo" required maxlength="4" x-model="formCodigo" @input="formCodigo = formCodigo.replace(/[^0-9]/g, ''); checkCode()" class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm uppercase outline-none focus:border-gray-500 focus:ring-1 focus:ring-gray-500/30" :class="{'border-red-400 ring-1 ring-red-400': codeExists}">
                    <p x-show="codeExists" class="text-xs text-red-500 font-bold mt-1" style="display:none;">Este código já está cadastrado!</p>
                </div>
                
                <div>
                    <label class="block text-[11px] font-black text-slate-400 uppercase tracking-wider mb-1">Nome *</label>
                    <input type="text" id="ag_nome" name="nome" required x-model="formNome" @input="formNome = formNome.toUpperCase()" class="w-full rounded-xl border-0 bg-slate-50 px-4 py-3.5 text-sm ring-1 ring-inset ring-slate-200 focus:ring-2 focus:ring-inset focus:ring-blue-600 transition-all uppercase">
                </div>
                
                <div class="flex gap-3 pt-2">
                    <button type="button" @click="modalOpen = false" class="flex-1 rounded-xl bg-white text-slate-700 ring-1 ring-inset ring-slate-300 py-3 text-sm font-bold hover:bg-slate-50 hover:text-slate-900 transition">Cancelar</button>
                    <button type="submit" :disabled="codeExists" class="flex-1 rounded-xl bg-[#2563eb] py-3 text-sm font-semibold text-white shadow-md shadow-[#2563eb]/30 hover:bg-[#1d4ed8] transition disabled:opacity-50 disabled:cursor-not-allowed">Salvar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Success Prompt Modal -->
    <div x-show="showPrompt" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
        <div class="absolute inset-0 bg-[#111625]/60 backdrop-blur-sm" @click="showPrompt = false"></div>
        <div class="relative w-full max-w-sm bg-white rounded-3xl shadow-2xl p-6 text-center z-10"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            <div class="w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4 text-emerald-500">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
            </div>
            <h3 class="text-xl font-black text-gray-900 mb-2">Salvo com Sucesso!</h3>
            <p class="text-sm text-gray-500 mb-6">A agência foi registrada. Deseja cadastrar uma nova agência agora?</p>
            
            <div class="flex gap-3">
                <button @click="showPrompt = false" class="flex-1 rounded-xl bg-white text-slate-700 ring-1 ring-inset ring-slate-300 py-3 text-sm font-bold hover:bg-slate-50 hover:text-slate-900 transition">Não, Voltar</button>
                <button @click="showPrompt = false; modalOpen = true; formCodigo = ''; formNome = ''; formId = '';" class="flex-1 rounded-xl bg-[#2563eb] py-3 text-sm font-semibold text-white shadow-md hover:bg-[#1d4ed8] transition">Sim, Cadastrar</button>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'middleware.php'; include 'layout/footer.php'; ?>
