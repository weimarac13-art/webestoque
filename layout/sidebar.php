<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$activeGroup = '';
if (in_array($currentPage, ['index.php'])) $activeGroup = 'inicio';
elseif (in_array($currentPage, ['estoque.php'])) $activeGroup = 'estoque';
elseif (in_array($currentPage, ['movimentacoes.php'])) $activeGroup = 'movimentacoes';
elseif (in_array($currentPage, ['relatorios.php'])) $activeGroup = 'relatorios';
elseif (in_array($currentPage, ['agencias.php', 'tecnicos.php', 'produtos.php'])) $activeGroup = 'cadastrar';
elseif (in_array($currentPage, ['logs.php', 'backup.php', 'deploy.php', 'usuarios.php'])) $activeGroup = 'ferramentas';
elseif (in_array($currentPage, ['alterar_senha.php'])) $activeGroup = 'alterar_senha';
?>

<div x-data="{ 
    activeItem: '<?= $activeGroup ?>',
    relatoriosOpen: <?= $currentPage == 'relatorios.php' ? 'true' : 'false' ?>,
    cadastrarOpen: <?= in_array($currentPage, ['agencias.php', 'tecnicos.php', 'produtos.php']) ? 'true' : 'false' ?>,
    ferramentasOpen: <?= in_array($currentPage, ['logs.php', 'backup.php', 'deploy.php', 'usuarios.php']) ? 'true' : 'false' ?>
}" class="flex flex-col h-full w-full">

    <a href="index.php" @click="activeItem = 'inicio'" class="sidebar-item" :class="activeItem === 'inicio' ? 'active' : ''">
        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
        Início
    </a>

    <a href="estoque.php" @click="activeItem = 'estoque'" class="sidebar-item" :class="activeItem === 'estoque' ? 'active' : ''">
        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
        Estoque
    </a>

    <a href="movimentacoes.php" @click="activeItem = 'movimentacoes'" class="sidebar-item" :class="activeItem === 'movimentacoes' ? 'active' : ''">
        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path></svg>
        Movimentações
    </a>

    <div class="mt-2">
        <button @click="relatoriosOpen = !relatoriosOpen; activeItem = 'relatorios'" class="w-full sidebar-item flex items-center justify-between" :class="activeItem === 'relatorios' ? 'active' : ''">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Relatórios
            </div>
            <svg class="w-4 h-4 transition-transform duration-200" :class="{'rotate-180': relatoriosOpen}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
        </button>
        <div x-show="relatoriosOpen" class="pl-8 pr-2 py-2 space-y-1">
            <a href="relatorios.php?tipo=Caixa Econômica Federal" class="sidebar-item !py-2 text-sm <?= ($currentPage == 'relatorios.php' && ($_GET['tipo'] ?? '') == 'Caixa Econômica Federal') ? 'font-bold bg-blue-50' : '' ?> text-blue-600 hover:text-blue-700">Caixa Econômica Federal</a>
            <a href="relatorios.php?tipo=Wyntech" class="sidebar-item !py-2 text-sm <?= ($currentPage == 'relatorios.php' && ($_GET['tipo'] ?? '') == 'Wyntech') ? 'font-bold bg-red-100' : '' ?> text-red-800 hover:text-red-900">Wyntech</a>
        </div>
    </div>

    <?php if (isset($_SESSION['perfil']) && $_SESSION['perfil'] === 'Administrador'): ?>
    <div class="mt-2">
        <button @click="cadastrarOpen = !cadastrarOpen; activeItem = 'cadastrar'" class="w-full sidebar-item flex items-center justify-between" :class="activeItem === 'cadastrar' ? 'active' : ''">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Cadastrar
            </div>
            <svg class="w-4 h-4 transition-transform duration-200" :class="{'rotate-180': cadastrarOpen}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
        </button>
        <div x-show="cadastrarOpen" class="pl-8 pr-2 py-2 space-y-1">
            <a href="agencias.php" class="sidebar-item !py-2 text-sm <?= $currentPage == 'agencias.php' ? 'text-[#2563eb] font-bold' : '' ?>">Agência</a>
            <a href="tecnicos.php" class="sidebar-item !py-2 text-sm <?= $currentPage == 'tecnicos.php' ? 'text-[#2563eb] font-bold' : '' ?>">Técnicos</a>
            <a href="produtos.php" class="sidebar-item !py-2 text-sm <?= $currentPage == 'produtos.php' ? 'text-[#2563eb] font-bold' : '' ?>">Peças</a>
        </div>
    </div>
    <?php endif; ?>

    <!-- Modo Escuro Toggle -->
    <div class="mt-2">
        <button id="toggle-dark-mode" class="w-full sidebar-item flex items-center justify-between cursor-pointer">
            <div class="flex items-center">
                <svg id="dark-icon-moon" class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"></path></svg>
                <svg id="dark-icon-sun" class="w-5 h-5 mr-3 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="4"></circle><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2v2M12 20v2m-7.07-15.07l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2m-11.34 5.66l-1.41 1.41M19.07 4.93l-1.41 1.41"></path></svg>
                <span id="dark-mode-text">Modo Escuro</span>
            </div>
        </button>
    </div>

    <?php if (isset($_SESSION['perfil']) && $_SESSION['perfil'] === 'Administrador'): ?>
    <div class="mt-2">
        <button @click="ferramentasOpen = !ferramentasOpen; activeItem = 'ferramentas'" class="w-full sidebar-item flex items-center justify-between" :class="activeItem === 'ferramentas' ? 'active' : ''">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                Ferramentas
            </div>
            <svg class="w-4 h-4 transition-transform duration-200" :class="{'rotate-180': ferramentasOpen}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
        </button>
        <div x-show="ferramentasOpen" class="pl-8 pr-2 py-2 space-y-1">
            <a href="logs.php" class="sidebar-item !py-2 text-sm <?= $currentPage == 'logs.php' ? 'text-[#2563eb] font-bold' : '' ?>">Logs</a>
            <a href="backup.php" class="sidebar-item !py-2 text-sm <?= $currentPage == 'backup.php' ? 'text-[#2563eb] font-bold' : '' ?>">Backup</a>
            <a href="deploy.php" class="sidebar-item !py-2 text-sm <?= $currentPage == 'deploy.php' ? 'text-[#2563eb] font-bold' : '' ?>">Deploy FTP</a>
            <a href="usuarios.php" class="sidebar-item !py-2 text-sm <?= $currentPage == 'usuarios.php' ? 'text-[#2563eb] font-bold' : '' ?>">Cadastrar Usuário</a>
        </div>
    </div>
    <?php else: ?>
    <div class="mt-2">
        <a href="alterar_senha.php" @click="activeItem = 'alterar_senha'" class="sidebar-item" :class="activeItem === 'alterar_senha' ? 'active' : ''">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4v-3.286l7.472-7.472A6 6 0 1115 7z"></path></svg>
            Alterar Senha
        </a>
    </div>
    <?php endif; ?>

    <div class="mt-4 pt-6 border-t border-gray-100 mb-4">
        <a href="logout.php" class="sidebar-item text-red-500 hover:!bg-red-50 hover:!text-red-600 hover:!border-red-200">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
            Sair
        </a>
    </div>

</div>
