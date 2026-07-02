<?php
require_once __DIR__ . '/../middleware.php';
?><!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WebEstoque - Controle de Estoque</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Alpine Plugins -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/mask@3.x.x/dist/cdn.min.js"></script>
    <!-- Alpine.js for lightweight interactions -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        .sidebar-item { display: flex; align-items: center; padding: 0.75rem 1rem; border-radius: 0.75rem; color: #4b5563; font-weight: 600; transition: all 0.3s ease; border: 1px solid transparent; }
        .sidebar-item:not(.active):hover { background-color: #ffffff; color: #2563eb; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); transform: translateY(-2px); border-color: #dbeafe; }
        .sidebar-item.active { background-color: #2563eb; color: white; box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.3); }
    </style>
</head>
<body class="flex h-screen overflow-hidden text-gray-900">

    <!-- Mobile Menu Button -->
    <div x-data="{ sidebarOpen: false }" class="lg:hidden fixed top-0 left-0 right-0 h-16 bg-white border-b border-gray-200 z-50 flex items-center justify-between px-4">
        <div class="flex items-center gap-2">
            <div class="h-8 w-8 bg-[#2563eb] rounded-lg flex items-center justify-center text-white font-bold">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
            </div>
            <span class="font-black text-gray-900">WebEstoque</span>
        </div>
        <button @click="sidebarOpen = !sidebarOpen" class="p-2 bg-gray-100 rounded-lg">
            <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
        </button>
        
        <!-- Mobile Sidebar -->
        <div x-show="sidebarOpen" class="fixed inset-0 bg-gray-900/50 z-40" @click="sidebarOpen = false"></div>
        <div x-show="sidebarOpen" class="fixed top-0 left-0 bottom-0 w-64 bg-white z-50 border-r border-gray-200 transition-transform transform" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full">
            <?php include 'sidebar.php'; ?>
        </div>
    </div>

    <!-- Desktop Sidebar -->
    <aside class="hidden lg:flex w-64 bg-white border-r border-gray-100 flex-col h-full z-10 shadow-sm relative pt-4">
        <div class="px-6 pb-6 border-b border-gray-50 flex items-center gap-3">
            <div class="h-10 w-10 bg-gradient-to-br from-[#2563eb] to-[#1e3a8a] rounded-xl flex items-center justify-center text-white shadow-md">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
            </div>
            <div>
                <span class="font-black text-gray-900 text-[22px] leading-none block mb-0.5">WebEstoque</span>
                <span class="text-[10px] text-gray-400 font-bold uppercase tracking-widest block">Controle de Estoque</span>
            </div>
        </div>
        
        <div class="flex-1 overflow-y-auto px-4 py-6 space-y-1">
            <?php include 'sidebar.php'; ?>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 h-full overflow-y-auto bg-gray-50/50 lg:pt-0 pt-16 relative">
        <div class="p-6 max-w-7xl mx-auto space-y-6">
