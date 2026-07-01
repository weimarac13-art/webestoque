<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<a href="index.php" class="sidebar-item <?= $currentPage == 'index.php' ? 'active' : '' ?>">
    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
    Início
</a>

<a href="estoque.php" class="sidebar-item <?= $currentPage == 'estoque.php' ? 'active' : '' ?>">
    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
    Estoque
</a>

<a href="movimentacoes.php" class="sidebar-item <?= $currentPage == 'movimentacoes.php' ? 'active' : '' ?>">
    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path></svg>
    Movimentações
</a>

<div x-data="{ relatoriosOpen: false }" class="mt-2">
    <button @click="relatoriosOpen = !relatoriosOpen" class="w-full sidebar-item flex items-center justify-between <?= $currentPage == 'relatorios.php' ? 'active' : '' ?>">
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            Relatórios
        </div>
        <svg class="w-4 h-4 transition-transform duration-200" :class="{'rotate-180': relatoriosOpen}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
    </button>
    <div x-show="relatoriosOpen || <?= $currentPage == 'relatorios.php' ? 'true' : 'false' ?>" class="pl-8 pr-2 py-2 space-y-1">
        <a href="relatorios.php?tipo=Caixa Econômica Federal" class="sidebar-item !py-2 text-sm <?= ($currentPage == 'relatorios.php' && ($_GET['tipo'] ?? '') == 'Caixa Econômica Federal') ? 'font-bold bg-blue-50' : '' ?> text-blue-600 hover:text-blue-700">Caixa Econômica Federal</a>
        <a href="relatorios.php?tipo=Wyntech" class="sidebar-item !py-2 text-sm <?= ($currentPage == 'relatorios.php' && ($_GET['tipo'] ?? '') == 'Wyntech') ? 'font-bold bg-red-100' : '' ?> text-red-800 hover:text-red-900">Wyntech</a>
    </div>
</div>

<?php if (isset($_SESSION['perfil']) && $_SESSION['perfil'] === 'Administrador'): ?>
<div x-data="{ cadastrarOpen: false }" class="mt-2">
    <button @click="cadastrarOpen = !cadastrarOpen" class="w-full sidebar-item flex items-center justify-between <?= in_array($currentPage, ['agencias.php', 'tecnicos.php', 'produtos.php']) ? 'active' : '' ?>">
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Cadastrar
        </div>
        <svg class="w-4 h-4 transition-transform duration-200" :class="{'rotate-180': cadastrarOpen}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
    </button>
    <div x-show="cadastrarOpen || <?= in_array($currentPage, ['agencias.php', 'tecnicos.php', 'produtos.php']) ? 'true' : 'false' ?>" class="pl-8 pr-2 py-2 space-y-1">
        <a href="agencias.php" class="sidebar-item !py-2 text-sm <?= $currentPage == 'agencias.php' ? 'text-[#2563eb] font-bold' : '' ?>">Agência</a>
        <a href="tecnicos.php" class="sidebar-item !py-2 text-sm <?= $currentPage == 'tecnicos.php' ? 'text-[#2563eb] font-bold' : '' ?>">Técnicos</a>
        <a href="produtos.php" class="sidebar-item !py-2 text-sm <?= $currentPage == 'produtos.php' ? 'text-[#2563eb] font-bold' : '' ?>">Peças</a>
    </div>
</div>
<?php endif; ?>

<?php if (isset($_SESSION['perfil']) && $_SESSION['perfil'] === 'Administrador'): ?>
<div x-data="{ ferramentasOpen: false }" class="mt-2">
    <button @click="ferramentasOpen = !ferramentasOpen" class="w-full sidebar-item flex items-center justify-between <?= in_array($currentPage, ['logs.php', 'backup.php', 'deploy.php', 'usuarios.php']) ? 'active' : '' ?>">
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
            Ferramentas
        </div>
        <svg class="w-4 h-4 transition-transform duration-200" :class="{'rotate-180': ferramentasOpen}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
    </button>
    <div x-show="ferramentasOpen || <?= in_array($currentPage, ['logs.php', 'backup.php', 'deploy.php', 'usuarios.php']) ? 'true' : 'false' ?>" class="pl-8 pr-2 py-2 space-y-1">
        <a href="logs.php" class="sidebar-item !py-2 text-sm <?= $currentPage == 'logs.php' ? 'text-[#2563eb] font-bold' : '' ?>">Logs</a>
        <a href="backup.php" class="sidebar-item !py-2 text-sm <?= $currentPage == 'backup.php' ? 'text-[#2563eb] font-bold' : '' ?>">Backup</a>
        <a href="deploy.php" class="sidebar-item !py-2 text-sm <?= $currentPage == 'deploy.php' ? 'text-[#2563eb] font-bold' : '' ?>">Deploy</a>
        <a href="usuarios.php" class="sidebar-item !py-2 text-sm <?= $currentPage == 'usuarios.php' ? 'text-[#2563eb] font-bold' : '' ?>">Cadastrar Usuário</a>
    </div>
</div>
<?php else: ?>
<div class="mt-2">
    <a href="alterar_senha.php" class="sidebar-item <?= $currentPage == 'alterar_senha.php' ? 'active' : '' ?>">
        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4v-3.286l7.472-7.472A6 6 0 1115 7z"></path></svg>
        Alterar Senha
    </a>
</div>
<?php endif; ?>

<div class="mt-8 pt-6 border-t border-gray-100">
    <a href="logout.php" class="sidebar-item text-red-500 hover:bg-red-50 hover:text-red-600">
        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
        Sair
    </a>
</div>
