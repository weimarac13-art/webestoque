<?php
require_once 'middleware.php';
require_once 'api/db.php';
$db = Database::getConnection();

$firstName = isset($_SESSION['nome']) ? explode(' ', trim($_SESSION['nome']))[0] : (isset($_SESSION['username']) ? explode(' ', trim($_SESSION['username']))[0] : 'Usuário');

include 'layout/header.php';
?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-gray-900 tracking-tight">Olá, <?= htmlspecialchars(ucfirst($firstName)) ?>!</h1>
            <p class="text-sm text-gray-500 font-medium">Acesso rápido às funcionalidades do sistema.</p>
        </div>
    </div>

    <!-- Quick Actions Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <a href="estoque.php" class="bg-white hover:bg-gray-50 border border-gray-100 rounded-[2rem] p-10 shadow-sm transition-all flex flex-col items-center justify-center gap-6 group text-center min-h-[280px]">
            <div class="w-24 h-24 bg-blue-50 text-blue-500 rounded-3xl flex items-center justify-center group-hover:scale-110 group-hover:-translate-y-2 transition-all shadow-sm">
                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
            </div>
            <div>
                <h3 class="font-black text-gray-900 text-2xl mb-2">Estoque</h3>
                <p class="text-sm text-gray-500 font-medium">Visualizar e buscar itens no estoque</p>
            </div>
        </a>

        <a href="movimentacoes.php" class="bg-white hover:bg-gray-50 border border-gray-100 rounded-[2rem] p-10 shadow-sm transition-all flex flex-col items-center justify-center gap-6 group text-center min-h-[280px]">
            <div class="w-24 h-24 bg-emerald-50 text-emerald-500 rounded-3xl flex items-center justify-center group-hover:scale-110 group-hover:-translate-y-2 transition-all shadow-sm">
                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
            </div>
            <div>
                <h3 class="font-black text-gray-900 text-2xl mb-2">Entrada/Saída</h3>
                <p class="text-sm text-gray-500 font-medium">Registrar movimentações de peças</p>
            </div>
        </a>

        <a href="produtos.php" class="bg-white hover:bg-gray-50 border border-gray-100 rounded-[2rem] p-10 shadow-sm transition-all flex flex-col items-center justify-center gap-6 group text-center min-h-[280px]">
            <div class="w-24 h-24 bg-[#2563eb]/10 text-[#2563eb] rounded-3xl flex items-center justify-center group-hover:scale-110 group-hover:-translate-y-2 transition-all shadow-sm">
                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            </div>
            <div>
                <h3 class="font-black text-gray-900 text-2xl mb-2">Cadastrar Peça</h3>
                <p class="text-sm text-gray-500 font-medium">Adicionar um novo produto ao sistema</p>
            </div>
        </a>
    </div>
</div>

<?php include 'layout/footer.php'; ?>
