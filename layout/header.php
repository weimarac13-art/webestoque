<?php
require_once __DIR__ . '/../middleware.php';
?><!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover, minimal-ui">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="WebEstoque">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="theme-color" content="#2563eb">
    <?php $logoV = file_exists('assets/logo.png') ? filemtime('assets/logo.png') : time(); ?>
    <link rel="manifest" href="manifest.json">
    <link rel="icon" href="assets/logo.svg?v=<?= filemtime('assets/logo.svg') ?>" type="image/svg+xml">
    <link rel="apple-touch-icon" href="assets/logo.png?v=<?= $logoV ?>">
    <title>WebEstoque - Controle de Estoque</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Alpine Plugins -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/mask@3.x.x/dist/cdn.min.js"></script>
    <!-- Alpine.js for lightweight interactions -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            darkMode: 'class'
        }
    </script>
    <script>
        if (localStorage.getItem('darkMode') === 'true') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('sw.js');
            });
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        .sidebar-item { display: flex; align-items: center; padding: 0.75rem 1rem; border-radius: 0.75rem; color: #4b5563; font-weight: 600; transition: all 0.3s ease; border: 1px solid transparent; }
        .sidebar-item:not(.active):hover { background-color: #ffffff; color: #2563eb; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); transform: translateY(-2px); border-color: #dbeafe; }
        .sidebar-item.active { background-color: #2563eb; color: white; box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.3); }
        
        /* Dark Mode Overrides */
        .dark body { background-color: #0f172a; color: #f8fafc; }
        .dark .bg-white { background-color: #1e293b !important; border-color: #334155 !important; box-shadow: none !important; }
        .dark .text-gray-900, .dark .text-slate-900 { color: #f1f5f9 !important; }
        .dark .text-gray-800, .dark .text-slate-800 { color: #e2e8f0 !important; }
        .dark .text-gray-600, .dark .text-gray-700 { color: #cbd5e1 !important; }
        .dark .text-gray-500, .dark .text-slate-500 { color: #94a3b8 !important; }
        .dark .border-gray-100, .dark .border-gray-200, .dark .border-slate-200 { border-color: #334155 !important; }
        .dark .bg-gray-50, .dark .bg-slate-50, .dark .bg-gray-100 { background-color: #0f172a !important; }
        .dark .hover\:bg-gray-50:hover, .dark .hover\:bg-gray-100:hover { background-color: #334155 !important; }
        .dark input, .dark select, .dark textarea, .dark [type="text"] { background-color: #0f172a !important; border-color: #334155 !important; color: #f8fafc !important; }
        .dark .sidebar-item:not(.active) { color: #94a3b8; }
        .dark .sidebar-item:not(.active):hover { background-color: #334155 !important; color: #60a5fa !important; border-color: #1e3a8a !important; }
        .dark th, .dark label { color: #f8fafc !important; }
        .dark table td { border-color: #334155 !important; }
        .dark #modal-overlay, .dark .modal-bg { background: rgba(15, 23, 42, 0.8) !important; }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toggleDark = document.getElementById('toggle-dark-mode');
            const darkText   = document.getElementById('dark-mode-text');
            const sunIcon    = document.getElementById('dark-icon-sun');
            const moonIcon   = document.getElementById('dark-icon-moon');

            function updateDarkUI() {
                const isDark = document.documentElement.classList.contains('dark');
                if (darkText && sunIcon && moonIcon) {
                    if (isDark) {
                        darkText.textContent = 'Modo Claro';
                        sunIcon.classList.remove('hidden');
                        moonIcon.classList.add('hidden');
                    } else {
                        darkText.textContent = 'Modo Escuro';
                        sunIcon.classList.add('hidden');
                        moonIcon.classList.remove('hidden');
                    }
                }
                localStorage.setItem('darkMode', isDark);
            }

            if (toggleDark) {
                toggleDark.addEventListener('click', () => {
                    document.documentElement.classList.toggle('dark');
                    updateDarkUI();
                });
                updateDarkUI();
            }
        });
    </script>
</head>
<body class="flex h-[100dvh] overflow-hidden text-gray-900">

    <!-- Mobile Menu Button -->
    <div x-data="{ sidebarOpen: false }" class="lg:hidden fixed top-0 left-0 right-0 h-16 bg-white border-b border-gray-200 z-50 flex items-center px-4 gap-3">
        <button @click="sidebarOpen = !sidebarOpen" class="p-2 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
            <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
        </button>
        <div class="flex items-center gap-2">
            <div class="h-8 w-8 bg-[#2563eb] rounded-lg flex items-center justify-center text-white font-bold shadow-sm">
                <svg class="w-[90%] h-[90%] text-white" viewBox="1 2 22 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="2" y="3" width="20" height="14" rx="2" stroke="currentColor" stroke-width="2"/>
                    <path d="M2 13H22" stroke="currentColor" stroke-width="2"/>
                    <path d="M9 21H15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M12 17V21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <text x="12" y="10.5" text-anchor="middle" font-size="7" font-weight="900" font-family="Inter, sans-serif" fill="currentColor">WE</text>
                </svg>
            </div>
            <div class="flex flex-col justify-center" style="width: max-content;">
                <span class="font-black text-xl tracking-tight leading-none text-[#2563eb] block mb-0.5" style="-webkit-text-stroke: 1.5px white; paint-order: stroke fill; text-shadow: 0 1px 2px rgba(0,0,0,0.1);">WebEstoque</span>
                <div class="w-full flex justify-between text-[8px] text-gray-400 font-bold uppercase leading-none">
                    <?php foreach(str_split("Controle de Estoque") as $l) echo "<span>" . ($l == ' ' ? '&nbsp;' : $l) . "</span>"; ?>
                </div>
            </div>
        </div>
        
        <!-- Mobile Sidebar Overlay -->
        <div x-show="sidebarOpen" class="fixed inset-0 top-16 bg-gray-900/50 z-40" @click="sidebarOpen = false" style="display: none;" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>
        <!-- Mobile Sidebar -->
        <div x-show="sidebarOpen" class="fixed top-16 left-0 max-h-[calc(100dvh-4rem)] w-64 bg-white z-40 border-r border-b border-gray-200 rounded-br-2xl transition-transform transform overflow-y-auto shadow-2xl" x-transition:enter="transition ease-out duration-400" x-transition:enter-start="-translate-y-[150%]" x-transition:enter-end="translate-y-0" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="translate-y-0" x-transition:leave-end="-translate-y-[150%]" style="display: none;">
            <?php include 'sidebar.php'; ?>
        </div>
    </div>

    <!-- Desktop Sidebar -->
    <aside class="hidden lg:flex w-64 bg-white border-r border-gray-100 flex-col h-full z-10 shadow-sm relative pt-4">
        <div class="px-6 pb-6 border-b border-gray-50 flex items-center gap-3">
            <div class="h-10 w-10 bg-gradient-to-br from-[#2563eb] to-[#1e3a8a] rounded-xl flex items-center justify-center text-white shadow-md">
                <svg class="w-[90%] h-[90%] text-white" viewBox="1 2 22 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="2" y="3" width="20" height="14" rx="2" stroke="currentColor" stroke-width="2"/>
                    <path d="M2 13H22" stroke="currentColor" stroke-width="2"/>
                    <path d="M9 21H15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M12 17V21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <text x="12" y="10.5" text-anchor="middle" font-size="7" font-weight="900" font-family="Inter, sans-serif" fill="currentColor">WE</text>
                </svg>
            </div>
            <div class="flex flex-col justify-center" style="width: max-content;">
                <span class="font-black text-[#2563eb] text-[26px] tracking-tight leading-none block mb-0.5" style="-webkit-text-stroke: 1.5px white; paint-order: stroke fill; text-shadow: 0 1px 2px rgba(0,0,0,0.1);">WebEstoque</span>
                <div class="w-full flex justify-between text-[10.5px] text-gray-400 font-bold uppercase leading-none">
                    <?php foreach(str_split("Controle de Estoque") as $l) echo "<span>" . ($l == ' ' ? '&nbsp;' : $l) . "</span>"; ?>
                </div>
            </div>
        </div>
        
        <div class="flex-1 overflow-y-auto px-4 py-6 space-y-1">
            <?php include 'sidebar.php'; ?>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 h-full overflow-y-auto bg-gray-50/50 lg:pt-0 pt-16 pb-24 lg:pb-0 relative">
        <div class="p-6 max-w-7xl mx-auto space-y-6">
        
        <script>
            let deferredPrompt;
            window.addEventListener('beforeinstallprompt', (e) => {
                // Prevent Chrome 67 and earlier from automatically showing the prompt
                e.preventDefault();
                // Stash the event so it can be triggered later.
                deferredPrompt = e;
                // Update UI to notify the user they can add to home screen
                if (!document.getElementById('pwa-install-banner')) {
                    const banner = document.createElement('div');
                    banner.id = 'pwa-install-banner';
                    banner.className = 'fixed bottom-24 left-4 right-4 z-50 bg-[#2563eb] text-white p-4 rounded-2xl shadow-2xl flex items-center justify-between lg:hidden';
                    banner.innerHTML = `
                        <div class="flex flex-col">
                            <span class="font-bold text-sm">App WebEstoque</span>
                            <span class="text-xs opacity-90">Instale no seu celular (Ocupa Tela Cheia)</span>
                        </div>
                        <button id="pwa-install-btn" class="bg-white text-[#2563eb] font-bold px-4 py-2 rounded-xl shadow text-sm active:scale-95 transition-transform">Instalar</button>
                    `;
                    document.body.appendChild(banner);
                    
                    document.getElementById('pwa-install-btn').addEventListener('click', async () => {
                        banner.style.display = 'none';
                        if (deferredPrompt) {
                            deferredPrompt.prompt();
                            const { outcome } = await deferredPrompt.userChoice;
                            deferredPrompt = null;
                        }
                    });
                }
            });
            window.addEventListener('appinstalled', () => {
                const banner = document.getElementById('pwa-install-banner');
                if (banner) banner.style.display = 'none';
                deferredPrompt = null;
            });
        </script>
