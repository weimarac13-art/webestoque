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
    <div class="flex flex-col md:grid md:grid-cols-3 gap-5 md:gap-8 h-[calc(100vh-280px)] md:h-auto min-h-[350px]">
        <a href="estoque.php" class="bg-white hover:bg-gray-50 border border-gray-100 rounded-3xl md:rounded-[2rem] p-6 md:p-10 shadow-sm transition-all flex flex-1 flex-row md:flex-col items-center justify-start md:justify-center gap-5 md:gap-6 group text-left md:text-center min-h-[110px] md:min-h-[280px]">
            <div class="w-14 h-14 md:w-24 md:h-24 shrink-0 bg-blue-50 text-blue-500 rounded-2xl md:rounded-3xl flex items-center justify-center group-hover:scale-110 group-hover:-translate-y-2 transition-all shadow-sm">
                <svg class="w-7 h-7 md:w-12 md:h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
            </div>
            <div>
                <h3 class="font-black text-gray-900 text-xl md:text-3xl mb-0.5 md:mb-2 uppercase tracking-wide">Estoque</h3>
                <p class="text-xs md:text-base text-gray-500 font-bold leading-tight">Visualizar e buscar itens no estoque</p>
            </div>
        </a>

        <a href="movimentacoes.php" class="bg-white hover:bg-gray-50 border border-gray-100 rounded-3xl md:rounded-[2rem] p-6 md:p-10 shadow-sm transition-all flex flex-1 flex-row md:flex-col items-center justify-start md:justify-center gap-5 md:gap-6 group text-left md:text-center min-h-[110px] md:min-h-[280px]">
            <div class="w-14 h-14 md:w-24 md:h-24 shrink-0 bg-emerald-50 text-emerald-500 rounded-2xl md:rounded-3xl flex items-center justify-center group-hover:scale-110 group-hover:-translate-y-2 transition-all shadow-sm">
                <svg class="w-7 h-7 md:w-12 md:h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
            </div>
            <div>
                <h3 class="font-black text-gray-900 text-xl md:text-3xl mb-0.5 md:mb-2 uppercase tracking-wide">Entrada/Saída</h3>
                <p class="text-xs md:text-base text-gray-500 font-bold leading-tight">Registrar movimentações de peças</p>
            </div>
        </a>

        <a href="produtos.php" class="bg-white hover:bg-gray-50 border border-gray-100 rounded-3xl md:rounded-[2rem] p-6 md:p-10 shadow-sm transition-all flex flex-1 flex-row md:flex-col items-center justify-start md:justify-center gap-5 md:gap-6 group text-left md:text-center min-h-[110px] md:min-h-[280px]">
            <div class="w-14 h-14 md:w-24 md:h-24 shrink-0 bg-orange-50 text-orange-500 rounded-2xl md:rounded-3xl flex items-center justify-center group-hover:scale-110 group-hover:-translate-y-2 transition-all shadow-sm">
                <svg class="w-7 h-7 md:w-12 md:h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            </div>
            <div>
                <h3 class="font-black text-gray-900 text-xl md:text-3xl mb-0.5 md:mb-2 uppercase tracking-wide">Cadastrar Peça</h3>
                <p class="text-xs md:text-base text-gray-500 font-bold leading-tight">Adicionar um novo produto ao sistema</p>
            </div>
        </a>
    </div>
</div>

<?php include 'layout/footer.php'; ?>
