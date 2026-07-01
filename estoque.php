<?php
require_once 'middleware.php';
require_once 'api/db.php';

$db = Database::getConnection();

// Calculate stock levels by grouping same named products in the same estoque
$stmt = $db->query("
    SELECT name, estoque, SUM(quantity) as qty 
    FROM products 
    GROUP BY name, estoque 
    ORDER BY name ASC
");
$inventory = $stmt->fetchAll();

// Get available series grouped by name and estoque
$stmtSeries = $db->query("
    SELECT productName, estoque, serie, 
           SUM(CASE WHEN type='ENTRADA' THEN 1 ELSE 0 END) as enters, 
           SUM(CASE WHEN type='SAÍDA' THEN 1 ELSE 0 END) as exits 
    FROM movements 
    WHERE serie != '' AND serie IS NOT NULL 
    GROUP BY productName, estoque, serie 
    HAVING enters > exits
");
$availableSeriesRaw = $stmtSeries->fetchAll();
$seriesMap = [];
foreach ($availableSeriesRaw as $row) {
    $key = $row['productName'] . '_' . $row['estoque'];
    $seriesMap[$key][] = $row['serie'];
}
$jsonSeries = json_encode($seriesMap);

$currentPage = 'estoque.php';
include 'layout/header.php';
?>

<div class="space-y-5" x-data="{ searchQuery: '', filterEstoque: 'Todos', expanded: null, seriesMap: <?= htmlspecialchars($jsonSeries, ENT_QUOTES) ?> }">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900">Estoque Atual</h1>
            <p class="text-xs text-gray-500">Acompanhe a disponibilidade das peças.</p>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="flex flex-col sm:flex-row gap-3">
        <div class="relative flex-1">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <input type="text" x-model="searchQuery" placeholder="Pesquisar peça..." class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:border-[#2563eb] focus:ring-1 focus:ring-[#2563eb]/30 outline-none">
        </div>
        <select x-model="filterEstoque" class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-semibold focus:border-[#2563eb] focus:ring-1 outline-none bg-white">
            <option value="Todos">Todos os Estoques</option>
            <option value="Caixa Econômica Federal">Apenas CAIXA</option>
            <option value="Wyntech">Apenas Wyntech</option>
        </select>
    </div>

    <!-- Inventory List -->
    <div>
        <div class="mb-4 flex items-center justify-between">
            <h3 class="font-bold text-gray-800 text-sm tracking-wide uppercase">Peças em Estoque</h3>
        </div>

        <?php
require_once 'middleware.php'; if (count($inventory) == 0): ?>
            <div class="p-12 text-center bg-white rounded-3xl border border-gray-100 shadow-sm">
                <p class="text-gray-400 text-sm font-medium">Nenhuma peça cadastrada ou em estoque.</p>
            </div>
        <?php
require_once 'middleware.php'; else: ?>
            <div class="space-y-3">
                <?php
require_once 'middleware.php'; foreach ($inventory as $item): ?>
                    <?php
require_once 'middleware.php'; $key = htmlspecialchars($item['name'] . '_' . $item['estoque'], ENT_QUOTES); ?>
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex flex-col hover:shadow-md hover:border-blue-100 hover:-translate-y-0.5 transition-all duration-300 cursor-pointer group relative overflow-hidden"
                         @click="expanded = expanded === '<?= $key ?>' ? null : '<?= $key ?>'"
                         x-show="(() => {
                            if (filterEstoque !== 'Todos' && filterEstoque !== '<?= htmlspecialchars($item['estoque'], ENT_QUOTES) ?>') return false;
                            if (searchQuery === '') return true;
                            const q = searchQuery.toLowerCase();
                            if ('<?= strtolower(htmlspecialchars($item['name'], ENT_QUOTES)) ?>'.includes(q)) return true;
                            const series = seriesMap['<?= $key ?>'] || [];
                            return series.some(s => s.toLowerCase().includes(q));
                         })()">
                         
                        <!-- Subtle background gradient on hover -->
                        <div class="absolute inset-0 bg-gradient-to-r from-blue-50/0 via-blue-50/0 to-blue-50/30 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none"></div>
                        
                        <div class="flex items-center justify-between relative z-10">
                            <div class="flex items-center gap-4">
                                <div class="h-12 w-12 bg-emerald-50 rounded-2xl flex items-center justify-center text-emerald-600 group-hover:bg-emerald-100 group-hover:scale-105 transition-all duration-300">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                                </div>
                                <div>
                                    <h4 class="text-base font-extrabold text-gray-900 group-hover:text-[#2563eb] transition-colors"><?= htmlspecialchars($item['name']) ?></h4>
                                    <p class="text-xs text-gray-500 font-semibold mt-0.5">
                                        <span class="inline-flex items-center gap-1 <?= strpos($item['estoque'], 'Wyntech') !== false ? 'bg-red-50 text-red-700' : 'bg-blue-50 text-blue-700' ?> px-2 py-0.5 rounded-md font-bold">
                                            <?= htmlspecialchars($item['estoque']) ?>
                                        </span>
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-5">
                                <div class="text-right">
                                    <div class="text-2xl font-black text-gray-900 tracking-tight"><?= (int)$item['qty'] ?></div>
                                    <div class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Unidades</div>
                                </div>
                                <div class="w-8 h-8 rounded-full bg-gray-50 flex items-center justify-center group-hover:bg-white group-hover:shadow-sm border border-transparent group-hover:border-gray-100 transition-all">
                                    <svg class="w-4 h-4 text-gray-400 group-hover:text-[#2563eb] transition-all duration-300" :class="expanded === '<?= $key ?>' ? 'rotate-180 text-[#2563eb]' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>
                        </div>

                        <!-- Expanded Series List -->
                        <div x-show="expanded === '<?= $key ?>'" 
                             x-collapse
                             class="relative z-10"
                             style="display: none;">
                            <div class="mt-4 pt-4 border-t border-gray-100/60">
                                <p class="text-[11px] font-black text-gray-400 uppercase tracking-wider mb-3">Séries Disponíveis neste Estoque</p>
                                <div class="flex flex-wrap gap-2">
                                    <template x-for="serie in (seriesMap['<?= $key ?>'] || [])">
                                        <span class="px-3 py-1.5 bg-gray-50 border border-gray-100 rounded-xl text-xs font-bold text-gray-600 hover:bg-white hover:shadow-sm hover:border-gray-200 transition-all cursor-default" x-text="serie"></span>
                                    </template>
                                    <template x-if="!(seriesMap['<?= $key ?>'] || []).length">
                                        <span class="text-xs text-gray-400 font-medium italic bg-gray-50 px-3 py-1.5 rounded-lg border border-transparent">Nenhuma série registrada.</span>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php
require_once 'middleware.php'; endforeach; ?>
            </div>
            
            <!-- Empty state when search yields no results -->
            <div class="p-12 text-center bg-white rounded-3xl border border-gray-100 shadow-sm mt-4" style="display: none;" 
                 x-show="!document.querySelectorAll('.space-y-3 > div[style=\'display: flex;\'], .space-y-3 > div:not([style*=\'none\'])').length">
                <p class="text-gray-400 text-sm font-medium">Nenhuma peça encontrada para sua pesquisa.</p>
            </div>
        <?php
require_once 'middleware.php'; endif; ?>
    </div>
</div>

<?php
require_once 'middleware.php'; include 'layout/footer.php'; ?>
