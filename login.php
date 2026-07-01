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
                <div class="w-20 h-20 bg-gradient-to-br from-[#2563eb] to-[#1e3a8a] rounded-2xl mx-auto flex items-center justify-center shadow-lg mb-4">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                </div>
                <h1 class="text-3xl font-black text-gray-900 tracking-tight">WebEstoque</h1>
                <p class="text-sm text-gray-500 mt-2">Faça login para continuar</p>
            </div>
            
            <form method="POST" action="login.php" class="p-10 pt-0 space-y-5">
                <?php if($error): ?>
                    <div class="bg-red-50 text-red-600 p-4 rounded-xl text-sm font-semibold text-center border border-red-100">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>
                
                <div>
                    <input type="text" name="username" required placeholder="Seu usuário" class="w-full bg-gray-50 rounded-xl px-5 py-4 text-sm font-semibold outline-none focus:bg-white focus:ring-2 focus:ring-[#2563eb]/50 transition-all border border-transparent focus:border-[#2563eb]/30">
                </div>
                
                <div>
                    <input type="password" name="password" required placeholder="Sua senha" class="w-full bg-gray-50 rounded-xl px-5 py-4 text-sm font-semibold outline-none focus:bg-white focus:ring-2 focus:ring-[#2563eb]/50 transition-all border border-transparent focus:border-[#2563eb]/30">
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
