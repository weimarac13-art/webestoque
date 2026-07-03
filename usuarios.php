<?php
require_once 'middleware.php';
require_once 'api/db.php';
$db = Database::getConnection();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action']) && $_POST['action'] == 'save') {
        $id = $_POST['id'] ?? null;
        $nome = trim($_POST['nome'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $perfil = trim($_POST['perfil'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if ($id) {
            if (!empty($password)) {
                $stmt = $db->prepare("UPDATE users SET nome=:n, username=:u, password=:p, perfil=:pf WHERE id=:id");
                $stmt->execute([':id'=>$id, ':n'=>$nome, ':u'=>$username, ':p'=>$password, ':pf'=>$perfil]);
            } else {
                $stmt = $db->prepare("UPDATE users SET nome=:n, username=:u, perfil=:pf WHERE id=:id");
                $stmt->execute([':id'=>$id, ':n'=>$nome, ':u'=>$username, ':pf'=>$perfil]);
            }
            require_once 'api/logger.php';
            logAction($db, 'Editar', "Usuário " . $nome . " editado.");
        } else {
            $stmt = $db->prepare("INSERT INTO users (nome, username, password, perfil) VALUES (:n, :u, :p, :pf)");
            $stmt->execute([':n'=>$nome, ':u'=>$username, ':p'=>$password, ':pf'=>$perfil]);
            require_once 'api/logger.php';
            logAction($db, 'Novo', "Usuário " . $nome . " cadastrado.");
        }
        header("Location: usuarios.php");
        exit;
    }
    
    if (isset($_POST['action']) && $_POST['action'] == 'delete') {
        $id = $_POST['id'] ?? null;
        if ($id) {
            $stmt = $db->prepare("DELETE FROM users WHERE id = :id");
            $stmt->execute([':id' => $id]);
            require_once 'api/logger.php';
            logAction($db, 'Excluir', "Usuário ID " . $id . " excluído.");
        }
        header("Location: usuarios.php");
        exit;
    }
}

$stmt = $db->query("SELECT * FROM users ORDER BY nome ASC");
$users = $stmt->fetchAll();

include 'layout/header.php';
?>

<div class="space-y-5" x-data="{ modalOpen: false, formId: '', formNome: '', formUsername: '', formPerfil: 'Técnico' }">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900">Usuários</h1>
            <p class="text-xs text-gray-500">Gestão de usuários de acesso ao sistema.</p>
        </div>
        <button 
            @click="modalOpen = true; formId = ''; formNome = ''; formUsername = ''; formPerfil = 'Técnico';"
            class="flex items-center justify-center gap-1.5 px-4 h-11 rounded-xl bg-[#2563eb] text-xs font-extrabold text-white hover:bg-[#1d4ed8] active:scale-95 transition-all shadow-md"
        >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
            Cadastrar Usuário
        </button>
    </div>

    <!-- List -->
    <div>
        <div class="mb-4 flex items-center justify-between">
            <h3 class="font-bold text-gray-800 text-sm tracking-wide uppercase">Usuários Cadastrados</h3>
            <span class="text-xs font-bold text-gray-400 bg-white px-3 py-1.5 border border-gray-100 rounded-xl shadow-sm">
                <?= count($users) ?> cadastrado(s)
            </span>
        </div>

        <?php if (count($users) == 0): ?>
            <div class="p-12 text-center bg-white rounded-3xl border border-gray-100 shadow-sm">
                <p class="text-gray-400 text-sm font-medium">Nenhum usuário cadastrado.</p>
            </div>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($users as $u): ?>
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex items-center justify-between gap-4 hover:shadow-md hover:border-blue-100 hover:-translate-y-0.5 transition-all duration-300 group relative overflow-hidden">
                        
                        <!-- Subtle background gradient on hover -->
                        <div class="absolute inset-0 bg-gradient-to-r from-blue-50/0 via-blue-50/0 to-blue-50/30 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none"></div>

                        <div class="flex items-center gap-4 flex-1 relative z-10">
                            <div class="h-12 w-12 bg-gray-50 rounded-2xl flex items-center justify-center text-gray-400 group-hover:bg-blue-50 group-hover:text-[#2563eb] group-hover:scale-105 transition-all duration-300">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            </div>
                            <div>
                                <h4 class="text-base font-extrabold text-gray-900 group-hover:text-[#2563eb] transition-colors"><?= htmlspecialchars($u['nome']) ?></h4>
                                <p class="text-xs text-gray-500 font-semibold mt-0.5">Usuário: <?= htmlspecialchars($u['username']) ?> • Perfil: 
                                    <span class="font-bold bg-gray-50 px-2 py-0.5 rounded-md inline-block text-blue-700"><?= htmlspecialchars($u['perfil']) ?></span>
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 relative z-10 opacity-50 group-hover:opacity-100 transition-opacity">
                            <button
                                @click="modalOpen = true; formId = '<?= $u['id'] ?>'; formNome = '<?= htmlspecialchars($u['nome'], ENT_QUOTES) ?>'; formUsername = '<?= htmlspecialchars($u['username'], ENT_QUOTES) ?>'; formPerfil = '<?= htmlspecialchars($u['perfil'], ENT_QUOTES) ?>';"
                                class="p-2.5 rounded-xl text-gray-400 hover:text-[#2563eb] hover:bg-blue-50 transition-colors"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                            </button>
                            <form method="POST" action="usuarios.php" onsubmit="event.preventDefault(); WebEstoque.confirm('Excluir este usuário?', 'Excluir Usuário', 'danger').then(c => { if(c) this.submit(); });" class="inline">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                <button type="submit" class="p-2.5 rounded-xl text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal Form -->
    <div x-show="modalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
        <div class="absolute inset-0 bg-[#111625]/60 backdrop-blur-sm" @click="modalOpen = false"></div>
        <div class="relative w-full max-w-md bg-white rounded-3xl shadow-2xl overflow-hidden border border-gray-100 z-10 max-h-[90vh] flex flex-col">
            <div class="flex items-center justify-between px-8 pt-8 pb-2">
                <div>
                    <h3 class="text-2xl font-black text-gray-900 tracking-tight" x-text="formId ? 'Editar Usuário' : 'Cadastrar Usuário'"></h3>
                </div>
                <button @click="modalOpen = false" type="button" class="p-1.5 rounded-full hover:bg-gray-200 text-gray-400 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <form method="POST" action="usuarios.php" class="px-8 pb-8 pt-2 space-y-5 overflow-y-auto flex-1">
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="id" x-model="formId">
                
                <div>
                    <label class="block text-[11px] font-black text-slate-400 uppercase tracking-wider mb-1">Nome *</label>
                    <input type="text" name="nome" required x-model="formNome" class="w-full rounded-xl border-0 bg-slate-50 px-4 py-3.5 text-sm ring-1 ring-inset ring-slate-200 focus:ring-2 focus:ring-inset focus:ring-blue-600 transition-all">
                </div>
                <div>
                    <label class="block text-[11px] font-black text-slate-400 uppercase tracking-wider mb-1">Usuário *</label>
                    <input type="text" name="username" required x-model="formUsername" class="w-full rounded-xl border-0 bg-slate-50 px-4 py-3.5 text-sm ring-1 ring-inset ring-slate-200 focus:ring-2 focus:ring-inset focus:ring-blue-600 transition-all">
                </div>
                <div>
                    <label class="block text-[11px] font-black text-slate-400 uppercase tracking-wider mb-1">Senha <span x-show="formId">(deixe em branco para manter)</span><span x-show="!formId">*</span></label>
                    <input type="password" name="password" x-bind:required="!formId" class="w-full rounded-xl border-0 bg-slate-50 px-4 py-3.5 text-sm ring-1 ring-inset ring-slate-200 focus:ring-2 focus:ring-inset focus:ring-blue-600 transition-all">
                </div>
                <div>
                    <label class="block text-[11px] font-black text-slate-400 uppercase tracking-wider mb-1">Perfil *</label>
                    <select name="perfil" required x-model="formPerfil" class="w-full rounded-xl border-0 bg-slate-50 px-4 py-3.5 text-sm ring-1 ring-inset ring-slate-200 focus:ring-2 focus:ring-inset focus:ring-blue-600 transition-all">
                        <option value="Administrador">Administrador - Acesso total</option>
                        <option value="Técnico">Técnico - Só Consulta</option>
                    </select>
                </div>
                
                <div class="flex gap-3 pt-4">
                    <button type="button" @click="modalOpen = false" class="flex-1 rounded-xl bg-white text-slate-700 ring-1 ring-inset ring-slate-300 py-3 text-sm font-bold hover:bg-slate-50 hover:text-slate-900 transition">Cancelar</button>
                    <button type="submit" class="flex-1 rounded-xl bg-[#2563eb] py-3 text-sm font-semibold text-white shadow-md hover:bg-[#1d4ed8] transition">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
require_once 'middleware.php'; include 'layout/footer.php'; ?>
