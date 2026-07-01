<?php
require_once 'middleware.php';
require_once 'api/db.php';
$db = Database::getConnection();

$message = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if ($new_password !== $confirm_password) {
        $error = "As novas senhas não coincidem.";
    } else {
        $stmt = $db->prepare("SELECT password FROM users WHERE id = :id");
        $stmt->execute([':id' => $_SESSION['user_id']]);
        $user = $stmt->fetch();
        
        if ($user && ($user['password'] === $current_password || password_verify($current_password, $user['password']))) {
            $stmt = $db->prepare("UPDATE users SET password = :password WHERE id = :id");
            $stmt->execute([':password' => $new_password, ':id' => $_SESSION['user_id']]);
            $message = "Senha alterada com sucesso!";
        } else {
            $error = "Senha atual incorreta.";
        }
    }
}

include 'layout/header.php';
?>

<div class="max-w-md mx-auto bg-white rounded-3xl shadow-xl overflow-hidden border border-gray-100 mt-10">
    <div class="p-8 pb-6 text-center border-b border-gray-50">
        <h2 class="text-2xl font-black text-gray-900 tracking-tight">Alterar Senha</h2>
        <p class="text-sm text-gray-500 mt-1">Atualize sua senha de acesso</p>
    </div>
    
    <form method="POST" action="alterar_senha.php" class="p-8 space-y-5">
        <?php if($error): ?>
            <div class="bg-red-50 text-red-600 p-3 rounded-xl text-sm font-semibold text-center border border-red-100">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        <?php if($message): ?>
            <div class="bg-green-50 text-green-600 p-3 rounded-xl text-sm font-semibold text-center border border-green-100">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        
        <div>
            <label class="block text-[11px] font-black text-slate-400 uppercase tracking-wider mb-1">Senha Atual *</label>
            <input type="password" name="current_password" required class="w-full rounded-xl border-0 bg-slate-50 px-4 py-3.5 text-sm ring-1 ring-inset ring-slate-200 focus:ring-2 focus:ring-inset focus:ring-blue-600 transition-all">
        </div>
        <div>
            <label class="block text-[11px] font-black text-slate-400 uppercase tracking-wider mb-1">Nova Senha *</label>
            <input type="password" name="new_password" required class="w-full rounded-xl border-0 bg-slate-50 px-4 py-3.5 text-sm ring-1 ring-inset ring-slate-200 focus:ring-2 focus:ring-inset focus:ring-blue-600 transition-all">
        </div>
        <div>
            <label class="block text-[11px] font-black text-slate-400 uppercase tracking-wider mb-1">Confirmar Nova Senha *</label>
            <input type="password" name="confirm_password" required class="w-full rounded-xl border-0 bg-slate-50 px-4 py-3.5 text-sm ring-1 ring-inset ring-slate-200 focus:ring-2 focus:ring-inset focus:ring-blue-600 transition-all">
        </div>
        
        <div class="pt-2">
            <button type="submit" class="w-full bg-[#2563eb] hover:bg-[#1d4ed8] text-white rounded-xl py-3.5 font-bold text-sm shadow-lg shadow-[#2563eb]/30 transition-all active:scale-95">
                Salvar Nova Senha
            </button>
        </div>
    </form>
</div>

<?php include 'layout/footer.php'; ?>
