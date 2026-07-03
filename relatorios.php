<?php
require_once 'api/db.php';

$db = Database::getConnection();

$tipo = $_GET['tipo'] ?? 'Caixa Econômica Federal';

// Fetch products for the specific stock location grouped by name
$stmt = $db->prepare("
    SELECT name, SUM(quantity) as total_quantity, MIN(minQuantity) as minQuantity
    FROM products 
    WHERE estoque = :tipo 
    GROUP BY name
    ORDER BY name ASC
");
$stmt->execute([':tipo' => $tipo]);
$products = $stmt->fetchAll();

// Get available series for this estoque grouped by productName
$stmtSeries = $db->prepare("
    SELECT productName, serie, 
           SUM(CASE WHEN type='ENTRADA' THEN 1 ELSE 0 END) as enters, 
           SUM(CASE WHEN type='SAÍDA' THEN 1 ELSE 0 END) as exits 
    FROM movements 
    WHERE estoque = :tipo AND serie != '' AND serie IS NOT NULL 
    GROUP BY productName, serie 
    HAVING enters > exits
");
$stmtSeries->execute([':tipo' => $tipo]);
$seriesRaw = $stmtSeries->fetchAll();

$seriesByName = [];
foreach ($seriesRaw as $s) {
    $seriesByName[$s['productName']][] = $s['serie'];
}

$currentPage = 'relatorios.php';
include 'layout/header.php';
?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-gray-900 tracking-tight">Relatório: <?= htmlspecialchars($tipo) ?></h1>
            <p class="text-sm text-gray-500 font-medium">Relação agrupada de peças e séries disponíveis no estoque <?= htmlspecialchars($tipo) ?>.</p>
        </div>
        <button onclick="window.print()" class="flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-bold text-sm transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            Imprimir
        </button>
    </div>

    <div>
        <div class="mb-4 flex items-center justify-between">
            <h3 class="font-bold text-gray-800 text-sm tracking-wide uppercase">Tabela de Relatório</h3>
            <span class="text-xs font-bold text-gray-400 bg-white px-3 py-1.5 border border-gray-100 rounded-xl shadow-sm">
                <?= count($products) ?> ite<?= count($products) == 1 ? 'm' : 'ns' ?>
            </span>
        </div>

        <?php if (count($products) == 0): ?>
            <div class="p-12 text-center bg-white rounded-3xl border border-gray-100 shadow-sm">
                <p class="text-gray-400 text-sm font-medium">Nenhuma peça encontrada no estoque <?= htmlspecialchars($tipo) ?>.</p>
            </div>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($products as $p): ?>
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:shadow-md hover:border-blue-100 hover:-translate-y-0.5 transition-all duration-300 group relative overflow-hidden">
                        
                        <!-- Subtle background gradient on hover -->
                        <div class="absolute inset-0 bg-gradient-to-r from-blue-50/0 via-blue-50/0 to-blue-50/30 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none"></div>

                        <div class="flex items-start gap-4 flex-1 relative z-10">
                            <div class="h-12 w-12 mt-1 bg-gray-50 rounded-2xl flex items-center justify-center text-gray-400 group-hover:bg-blue-50 group-hover:text-[#2563eb] group-hover:scale-105 transition-all duration-300 shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </div>
                            <div>
                                <h4 class="text-base font-extrabold text-gray-900 group-hover:text-[#2563eb] transition-colors mb-1"><?= htmlspecialchars($p['name']) ?></h4>
                                <div class="text-xs font-semibold text-gray-500">
                                    <?php 
                                        $series = $seriesByName[$p['name']] ?? [];
                                        if (count($series) > 0) {
                                            echo 'Séries: <span class="text-gray-700 font-bold bg-gray-50 px-2 py-0.5 rounded-md inline-block">' . implode(', ', array_map('htmlspecialchars', $series)) . '</span>';
                                        } else {
                                            echo '<span class="text-gray-400 font-medium italic bg-gray-50 px-2 py-0.5 rounded-md inline-block">Nenhuma série atrelada.</span>';
                                        }
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center sm:justify-end gap-5 relative z-10 mt-4 sm:mt-0">
                            <div class="text-right">
                                <div class="text-2xl font-black <?= $p['total_quantity'] <= $p['minQuantity'] ? 'text-orange-600' : 'text-gray-900' ?> tracking-tight">
                                    <?= (int)$p['total_quantity'] ?>
                                </div>
                                <div class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Unidades</div>
                            </div>
                            <?php if ($p['total_quantity'] <= $p['minQuantity']): ?>
                                <div class="h-10 px-3 bg-orange-50 rounded-xl flex items-center justify-center text-orange-600 text-xs font-bold border border-orange-100">
                                    Estoque Baixo
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    @media print {
        /* Hide UI elements */
        .sidebar-item, aside, button, header, .lg\:hidden { display: none !important; }
        main { padding: 0 !important; margin: 0 !important; }
        
        /* Clean white background */
        body { background-color: white !important; }
        
        /* Professional report font sizes */
        * { 
            font-size: 14px !important; 
            line-height: 1.5 !important;
            color: #000 !important;
            box-shadow: none !important;
        }
        
        /* Titles */
        h1, .text-2xl { font-size: 18px !important; font-weight: bold !important; margin-bottom: 15px !important; border-bottom: 1px solid #ccc !important; padding-bottom: 5px !important; }
        h3, h4, .text-base { font-size: 14px !important; font-weight: bold !important; }
        
        /* Flatten cards into list rows and FORCE horizontal layout even on mobile */
        .bg-white { border: none !important; box-shadow: none !important; background: transparent !important; }
        .p-5 { 
            padding: 8px 0 !important; 
            border-bottom: 1px solid #eee !important; 
            flex-direction: row !important; 
            align-items: center !important; 
            justify-content: space-between !important; 
        }
        .rounded-2xl { border-radius: 0 !important; }
        .gap-4 { gap: 10px !important; }
        
        /* Hide icons for a cleaner look */
        .h-12.w-12 { display: none !important; }
        
        /* Adjust alignment of quantities */
        .text-2xl.font-black { font-size: 14px !important; font-weight: bold !important; display: inline !important; }
        .text-\[10px\] { display: inline !important; font-size: 14px !important; margin-left: 4px !important; text-transform: lowercase !important; }
        
        /* Remove low stock badges */
        .bg-orange-50 { display: none !important; }
        
        /* Remove flex spacing to compact the list */
        .mt-4, .mb-4 { margin-top: 0 !important; margin-bottom: 5px !important; }
        .space-y-6 > :not([hidden]) ~ :not([hidden]) { margin-top: 10px !important; }
        .space-y-3 > :not([hidden]) ~ :not([hidden]) { margin-top: 0 !important; }
    }
</style>

<?php include 'layout/footer.php'; ?>
