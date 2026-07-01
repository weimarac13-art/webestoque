<?php
require_once 'api/db.php';
$db = Database::getConnection();

// Assegurar que a tabela system_logs existe
try {
    $db->exec("CREATE TABLE IF NOT EXISTS system_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        createdAt DATETIME NOT NULL,
        userName VARCHAR(255) NOT NULL,
        userId VARCHAR(50),
        action VARCHAR(100) NOT NULL,
        details TEXT,
        ipAddress VARCHAR(45),
        userAgent TEXT
    )");
} catch (PDOException $e) {
    // Ignora se der erro ou já existir
}

// Paginação
$limit = 50;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Contagem total
$total_logs = $db->query("SELECT COUNT(*) FROM system_logs")->fetchColumn();
$total_pages = ceil($total_logs / $limit);

// Buscar logs
$stmt = $db->prepare("SELECT * FROM system_logs ORDER BY createdAt DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$logs = $stmt->fetchAll();

$page_title = 'Logs do Sistema';
require_once 'layout/header.php';
?>

<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
    <div>
        <h2 class="text-3xl font-black text-gray-900 tracking-tight">Logs do Sistema</h2>
        <p class="text-sm text-gray-500 font-medium">Histórico completo de ações realizadas no sistema.</p>
    </div>
</div>

<div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50/50 border-b border-gray-100">
                    <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-widest whitespace-nowrap">Data / Hora</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-widest whitespace-nowrap">Usuário</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-widest whitespace-nowrap">Ação</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-widest whitespace-nowrap">Detalhes</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-widest whitespace-nowrap hidden lg:table-cell">IP / Origem</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if ($logs): foreach ($logs as $l): 
                    $logDate = strtotime($l['createdAt']);
                    $dateStr = date('d/m/Y H:i:s', $logDate);
                    
                    $action_class = 'bg-gray-100 text-gray-600';
                    if (strpos($l['action'], 'Novo') !== false) $action_class = 'bg-emerald-100 text-emerald-700';
                    if (strpos($l['action'], 'Excluir') !== false) $action_class = 'bg-red-100 text-red-700';
                    if (strpos($l['action'], 'Editar') !== false) $action_class = 'bg-indigo-100 text-indigo-700';
                    if ($l['action'] === 'Login') $action_class = 'bg-green-100 text-green-700';
                    if ($l['action'] === 'Logout') $action_class = 'bg-amber-100 text-amber-700';
                ?>
                    <tr class="hover:bg-gray-50 transition-all group">
                        <td class="px-6 py-4 text-sm font-medium text-gray-500 whitespace-nowrap">
                            <?php echo $dateStr; ?>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-bold text-gray-700"><?php echo htmlspecialchars($l['userName']); ?></div>
                            <div class="text-[10px] text-gray-400 font-mono"><?php echo htmlspecialchars($l['userId']) ?: 'Sistema'; ?></div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase <?php echo $action_class; ?>">
                                <?php echo htmlspecialchars($l['action']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-600 max-w-xs truncate lg:max-w-md" title="<?php echo htmlspecialchars($l['details']); ?>">
                                <?php echo htmlspecialchars($l['details']); ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 hidden lg:table-cell">
                            <div class="text-[10px] font-mono text-gray-400"><?php echo htmlspecialchars($l['ipAddress']); ?></div>
                            <div class="text-[9px] text-gray-300 truncate max-w-[150px]" title="<?php echo htmlspecialchars($l['userAgent']); ?>">
                                <?php echo htmlspecialchars($l['userAgent']); ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="5" class="px-6 py-12 text-center text-gray-400 italic">Nenhum registro de log encontrado.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Paginação -->
    <?php if ($total_pages > 1): ?>
    <div class="px-6 py-4 bg-gray-50/50 border-t border-gray-100 flex items-center justify-between">
        <div class="text-xs font-bold text-gray-400 uppercase tracking-widest">
            Página <?php echo $page; ?> de <?php echo $total_pages; ?>
        </div>
        <div class="flex gap-2">
            <?php if ($page > 1): ?>
            <a href="logs.php?page=<?php echo $page - 1; ?>" class="p-2 bg-white border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50 transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="m15 18-6-6 6-6"/></svg>
            </a>
            <?php endif; ?>
            
            <?php if ($page < $total_pages): ?>
            <a href="logs.php?page=<?php echo $page + 1; ?>" class="p-2 bg-white border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50 transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="m9 18 6-6-6-6"/></svg>
            </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once 'layout/footer.php'; ?>
