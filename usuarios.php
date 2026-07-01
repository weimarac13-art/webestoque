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
        } else {
            $stmt = $db->prepare("INSERT INTO users (nome, username, password, perfil) VALUES (:n, :u, :p, :pf)");
            $stmt->execute([':n'=>$nome, ':u'=>$username, ':p'=>$password, ':pf'=>$perfil]);
        }
        header("Location: usuarios.php");
        exit;
    }
    
    if (isset($_POST['action']) && $_POST['action'] == 'delete') {
        $id = $_POST['id'] ?? null;
        if ($id) {
            $stmt = $db->prepare("DELETE FROM users WHERE id = :id");
            $stmt->execute([':id' => $id]);
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
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-gray-50 bg-gray-50/50 flex justify-between items-center">
            <div>
                <h3 class="font-bold text-gray-900">Usuários Cadastrados</h3>
            </div>
            <span class="text-xs font-bold text-gray-400 bg-white px-2 py-1 border border-gray-100 rounded-lg shadow-sm">
                <?= count($users) ?> cadastrado(s)
            </span>
        </div>

        <?php
require_once 'middleware.php'; if (count($users) == 0): ?>
            <div class="p-8 text-center text-gray-400 text-sm">
                Nenhum usuário cadastrado.
            </div>
        <?php
require_once 'middleware.php'; else: ?>
            <div class="divide-y divide-gray-100">
                <?php
require_once 'middleware.php'; foreach ($users as $u): ?>
                    <div class="p-5 hover:bg-gray-50 transition-colors flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 bg-gray-100 rounded-xl flex items-center justify-center font-bold text-gray-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            </div>
                            <div>
                                <h4 class="text-base font-bold text-gray-900"><?= htmlspecialchars($u['nome']) ?></h4>
                                <p class="text-xs text-gray-500">Usuário: <?= htmlspecialchars($u['username']) ?> • Perfil: <?= htmlspecialchars($u['perfil']) ?></p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button
                                @click="modalOpen = true; formId = '<?= $u['id'] ?>'; formNome = '<?= htmlspecialchars($u['nome'], ENT_QUOTES) ?>'; formUsername = '<?= htmlspecialchars($u['username'], ENT_QUOTES) ?>'; formPerfil = '<?= htmlspecialchars($u['perfil'], ENT_QUOTES) ?>';"
                                class="p-2 rounded-xl text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                            </button>
                            <form method="POST" action="usuarios.php" onsubmit="return confirm('Excluir este usuário?');" class="inline">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                <button type="submit" class="p-2 rounded-xl text-red-400 hover:text-red-600 hover:bg-red-50 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
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
