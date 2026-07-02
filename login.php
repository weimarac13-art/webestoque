<?php
session_start();

// Already logged in?
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header("Location: index.php");
    exit;
}

$error = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once 'api/db.php';
    $db = Database::getConnection();
    
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $stmt = $db->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch();
    
    if ($user && ($user['password'] === $password || password_verify($password, $user['password']))) {
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = $user['username'];
        $_SESSION['nome'] = $user['nome'];
        $_SESSION['perfil'] = $user['perfil'];
        $_SESSION['user_id'] = $user['id'];
        header("Location: index.php");
        exit;
    } else {
        $error = "Usuário ou senha inválidos!";
    }
}
?><!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - WebEstoque</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }</style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-3xl shadow-xl overflow-hidden border border-gray-100">
            <div class="p-10 pb-6 text-center">
                <div class="w-24 h-24 bg-gradient-to-br from-[#2563eb] to-[#1e3a8a] rounded-3xl mx-auto flex items-center justify-center shadow-lg mb-4">
                    <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
                <h1 class="text-3xl font-black text-gray-900 tracking-tight mb-0.5">WebEstoque</h1>
                <span class="text-[10px] text-gray-400 font-bold uppercase tracking-widest block">Controle de Estoque</span>
                <p class="text-sm text-gray-500 mt-4">Faça login para continuar</p>
            </div>
            
            <form method="POST" action="login.php" class="p-10 pt-0 space-y-5">
                <?php if($error): ?>
                    <div class="bg-red-50 text-red-600 p-4 rounded-xl text-sm font-semibold text-center border border-red-100">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>
                
                <div class="space-y-1.5">
                    <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest pl-1">Usuário</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none text-[#2563eb]">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                        <input type="text" name="username" required placeholder="Seu usuário" class="w-full bg-slate-50/50 rounded-2xl pl-12 pr-5 py-4 text-sm font-semibold text-slate-700 outline-none focus:bg-white focus:ring-2 focus:ring-[#2563eb]/50 transition-all border border-slate-200 focus:border-[#2563eb]/30 placeholder:text-slate-300">
                    </div>
                </div>
                
                <div class="space-y-1.5">
                    <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest pl-1">Senha</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none text-[#2563eb]">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        </div>
                        <input type="password" name="password" required placeholder="••••••••" class="w-full bg-slate-50/50 rounded-2xl pl-12 pr-5 py-4 text-sm font-semibold text-slate-700 outline-none focus:bg-white focus:ring-2 focus:ring-[#2563eb]/50 transition-all border border-slate-200 focus:border-[#2563eb]/30 placeholder:text-slate-300">
                    </div>
                </div>
                
                <div class="pt-2">
                    <button type="submit" class="w-full bg-[#2563eb] hover:bg-[#1d4ed8] text-white rounded-xl py-4 font-bold text-sm shadow-lg shadow-[#2563eb]/30 transition-all active:scale-95">
                        Entrar no Sistema
                    </button>
                </div>
                
                <div class="text-center mt-6">
                    <p class="text-xs text-gray-400 font-medium">Desenvolvido por Weimar Almeida</p>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
