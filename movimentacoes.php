<?php
require_once 'middleware.php';
require_once 'api/db.php';
$db = Database::getConnection();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    if ($_POST['action'] == 'delete') {
        try {
            $db->beginTransaction();
            $id = $_POST['id'] ?? null;
            if ($id) {
                $stmt = $db->prepare("SELECT * FROM movements WHERE id = :id");
                $stmt->execute([':id' => $id]);
                $mov = $stmt->fetch();
                if ($mov) {
                    $stmt = $db->prepare("SELECT quantity FROM products WHERE id = :pid");
                    $stmt->execute([':pid' => $mov['productId']]);
                    $product = $stmt->fetch();
                    if ($product) {
                        $newQuantity = $product['quantity'];
                        if ($mov['type'] == 'ENTRADA') $newQuantity -= $mov['quantity'];
                        else $newQuantity += $mov['quantity'];
                        
                        $stmt = $db->prepare("UPDATE products SET quantity = :q WHERE id = :id");
                        $stmt->execute([':q' => $newQuantity, ':id' => $mov['productId']]);
                    }
                    $stmt = $db->prepare("DELETE FROM movements WHERE id = :id");
                    $stmt->execute([':id' => $id]);
                }
            }
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
        }
        header("Location: movimentacoes.php");
        exit;
    }

    if ($_POST['action'] == 'save') {
        try {
            $db->beginTransaction();
            
            $movId = $_POST['movId'] ?? '';
            $productId = $_POST['productId'] ?? '';
            $type = $_POST['type'] ?? 'ENTRADA';
            $quantity = (int)($_POST['quantity'] ?? 1);
            $reason = $_POST['reason'] ?? '';
            $responsible = $_POST['responsible'] ?? 'Administrador';
            $estoque = $_POST['estoque'] ?? 'Caixa Econômica Federal';
            
            // Fetch product
            $stmt = $db->prepare("SELECT name, quantity FROM products WHERE id = :id");
            $stmt->execute([':id' => $productId]);
            $product = $stmt->fetch();
            
            if (!$product) {
                throw new Exception("Produto não encontrado.");
            }
            
            if ($movId) {
                // EDIT EXISTING MOVEMENT
                $stmt = $db->prepare("SELECT * FROM movements WHERE id = :id");
                $stmt->execute([':id' => $movId]);
                $oldMov = $stmt->fetch();
                
                if ($oldMov) {
                    // Reverse old quantity
                    $oldQ_diff = ($oldMov['type'] == 'ENTRADA') ? -$oldMov['quantity'] : $oldMov['quantity'];
                    $db->prepare("UPDATE products SET quantity = quantity + (:q) WHERE id = :id")->execute([':q' => $oldQ_diff, ':id' => $oldMov['productId']]);
                    
                    // Apply new quantity
                    $newQ_diff = ($type == 'ENTRADA') ? $quantity : -$quantity;
                    $db->prepare("UPDATE products SET quantity = quantity + (:q) WHERE id = :id")->execute([':q' => $newQ_diff, ':id' => $productId]);
                    
                    // Update movement
                    $sql = "UPDATE movements SET type=:t, estoque=:e, productId=:pid, productName=:pname, quantity=:q, reason=:r, serie=:serie, tecnico=:tec, chamado=:cham, unidadeDestino=:ud, usuario=:usu, matricula=:mat WHERE id=:id";
                    $stmt = $db->prepare($sql);
                    $stmt->execute([
                        ':t' => $type, ':e' => $estoque, ':pid' => $productId, ':pname' => $product['name'], ':q' => $quantity,
                        ':r' => $reason, ':serie' => $_POST['serie'] ?? '', ':tec' => $_POST['tecnico'] ?? '', ':cham' => $_POST['chamado'] ?? '',
                        ':ud' => $_POST['unidadeDestino'] ?? '', ':usu' => $_POST['usuario'] ?? '', ':mat' => $_POST['matricula'] ?? '', ':id' => $movId
                    ]);
                }
            } else {
                // INSERT NEW MOVEMENT
                // Calculate new quantity
                $newQuantity = $product['quantity'];
                if ($type === 'ENTRADA') {
                    $newQuantity += $quantity;
                } else {
                    if ($product['quantity'] < $quantity) {
                        throw new Exception("Estoque insuficiente.");
                    }
                    $newQuantity -= $quantity;
                }
                
                // Update product
                $stmt = $db->prepare("UPDATE products SET quantity = :q WHERE id = :id");
                $stmt->execute([':q' => $newQuantity, ':id' => $productId]);
                
                // Insert movement
                $id = 'mov-' . time();
                $sql = "INSERT INTO movements (id, productId, productName, type, quantity, date, reason, responsible, estoque, serie, tecnico, chamado, unidadeDestino, usuario, matricula) 
                        VALUES (:id, :pid, :pname, :type, :q, :d, :r, :resp, :est, :serie, :tec, :cham, :ud, :usu, :mat)";
                        
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    ':id' => $id,
                    ':pid' => $productId,
                    ':pname' => $product['name'],
                    ':type' => $type,
                    ':q' => $quantity,
                    ':d' => date('Y-m-d H:i:s'),
                    ':r' => $reason,
                    ':resp' => $responsible,
                    ':est' => $estoque,
                    ':serie' => $_POST['serie'] ?? '',
                    ':tec' => $_POST['tecnico'] ?? '',
                    ':cham' => $_POST['chamado'] ?? '',
                    ':ud' => $_POST['unidadeDestino'] ?? '',
            ':usu' => $_POST['usuario'] ?? '',
            ':mat' => $_POST['matricula'] ?? ''
        ]);
            }
        
        $db->commit();
        if (!$movId) {
            header("Location: movimentacoes.php?saved=" . urlencode($id));
        } else {
            header("Location: movimentacoes.php");
        }
        exit;
    } catch(Exception $e) {
        $db->rollBack();
        die("Erro: " . $e->getMessage());
    }
}
}

// Data fetching
$movements = $db->query("SELECT * FROM movements ORDER BY date DESC")->fetchAll();
$products = $db->query("SELECT id, name, quantity, estoque FROM products ORDER BY name ASC")->fetchAll();
$technicians = $db->query("SELECT id, nome, matricula, celCorporativo, celPessoal FROM technicians ORDER BY nome ASC")->fetchAll();
$agencies = $db->query("SELECT id, codigo, nome FROM agencies ORDER BY nome ASC")->fetchAll();

$showPrompt = isset($_GET['saved']) ? $_GET['saved'] : false;
$whatsappUrl = '';
$whatsappTech = '';
$teamsUrl = '';

if ($showPrompt) {
    $stmt = $db->prepare("SELECT * FROM movements WHERE id = :id");
    $stmt->execute([':id' => $showPrompt]);
    $savedMov = $stmt->fetch();
    
    if ($savedMov) {
        if ($savedMov['type'] == 'SAÍDA' && $savedMov['estoque'] == 'Wyntech' && !empty($savedMov['tecnico'])) {
            $stmt = $db->prepare("SELECT celCorporativo, celPessoal FROM technicians WHERE nome = :nome");
            $stmt->execute([':nome' => $savedMov['tecnico']]);
            $tech = $stmt->fetch();
            if ($tech) {
                $phone = !empty($tech['celCorporativo']) ? $tech['celCorporativo'] : $tech['celPessoal'];
                if ($phone) {
                    $phoneClean = preg_replace('/[^0-9]/', '', $phone);
                    // Assume default Brazil country code if not present (usually cel starts with 11-99, so 11 digits)
                    if (strlen($phoneClean) <= 11) $phoneClean = '55' . $phoneClean;
                    $msg = "Olá " . $savedMov['tecnico'] . ", o equipamento *" . $savedMov['productName'] . "* foi liberado no estoque para você.";
                    if (!empty($savedMov['chamado'])) $msg .= "\nChamado: " . $savedMov['chamado'];
                    if (!empty($savedMov['serie'])) $msg .= "\nSérie: " . $savedMov['serie'];
                    $whatsappUrl = "https://wa.me/" . $phoneClean . "?text=" . urlencode($msg);
                    $whatsappTech = $savedMov['tecnico'];
                }
            }
        } elseif ($savedMov['estoque'] == 'Caixa Econômica Federal') {
            $msg = "Olá! Foi registrada uma nova movimentação no estoque CAIXA:\n\n";
            $msg .= "• Peça: " . $savedMov['productName'] . "\n";
            $msg .= "• Tipo: " . $savedMov['type'] . "\n";
            $msg .= "• Quantidade: " . $savedMov['quantity'] . " un\n";
            if (!empty($savedMov['chamado'])) $msg .= "• Chamado: " . $savedMov['chamado'] . "\n";
            if (!empty($savedMov['serie'])) $msg .= "• Série: " . $savedMov['serie'] . "\n";
            if (!empty($savedMov['unidadeDestino'])) $msg .= "• Destino: " . $savedMov['unidadeDestino'] . "\n";
            
            $teamsUser = "c138776@caixa.gov.br"; 
            $teamsUrl = "https://teams.microsoft.com/l/chat/0/0?users=" . urlencode($teamsUser) . "&message=" . urlencode($msg);
        }
    }
}

// Get available series per product
$stmt = $db->query("SELECT productId, serie, SUM(CASE WHEN type='ENTRADA' THEN 1 ELSE 0 END) as enters, SUM(CASE WHEN type='SAÍDA' THEN 1 ELSE 0 END) as exits FROM movements WHERE serie != '' AND serie IS NOT NULL GROUP BY productId, serie HAVING enters > exits");
$availableSeriesRaw = $stmt->fetchAll();
$productSeries = [];
foreach ($availableSeriesRaw as $row) {
    $productSeries[$row['productId']][] = $row['serie'];
}
$jsonSeries = json_encode($productSeries);

include 'layout/header.php';
?>

<div class="space-y-5" 
     x-data="{ modalOpen: false, type: 'ENTRADA', estoque: 'Caixa Econômica Federal', productId: '', chamado: 'REQ00000', formSerie: '', availableSeries: <?= htmlspecialchars($jsonSeries, ENT_QUOTES) ?>, formMovId: '', showPrompt: <?= ($whatsappUrl || $teamsUrl) ? 'true' : 'false' ?> }"
     x-effect="if (!formMovId) { if (type === 'SAÍDA' && estoque === 'Wyntech') { chamado = 'WO00000'; } else if (type === 'SAÍDA' && estoque === 'Caixa Econômica Federal') { chamado = 'REQ00000'; } else { chamado = ''; } }">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900">Movimentações</h1>
            <p class="text-xs text-gray-500">Histórico de Entradas e Saídas do estoque.</p>
        </div>
        <button 
            @click="modalOpen = true; formMovId = '';"
            class="flex items-center justify-center gap-1.5 px-4 h-11 rounded-xl bg-[#2563eb] text-xs font-extrabold text-white hover:bg-[#1d4ed8] active:scale-95 transition-all shadow-md shadow-[#2563eb]/30"
        >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path></svg>
            Lançar Movimento
        </button>
    </div>

    <!-- List -->
    <div>
        <div class="mb-4 flex items-center justify-between">
            <h3 class="font-bold text-gray-800 text-sm tracking-wide uppercase">Histórico de Movimentações</h3>
            <span class="text-xs font-bold text-gray-400 bg-white px-3 py-1.5 border border-gray-100 rounded-xl shadow-sm">
                <?= count($movements) ?> registro(s)
            </span>
        </div>

        <?php if (count($movements) == 0): ?>
            <div class="p-12 text-center bg-white rounded-3xl border border-gray-100 shadow-sm">
                <p class="text-gray-400 text-sm font-medium">Nenhuma movimentação registrada.</p>
            </div>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($movements as $m): ?>
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex items-center justify-between gap-4 hover:shadow-md hover:border-blue-100 hover:-translate-y-0.5 transition-all duration-300 group relative overflow-hidden">
                        
                        <!-- Subtle background gradient on hover -->
                        <div class="absolute inset-0 bg-gradient-to-r from-blue-50/0 via-blue-50/0 to-blue-50/30 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none"></div>

                        <div class="flex items-center gap-4 flex-1 relative z-10">
                            <div class="h-12 w-12 rounded-2xl flex items-center justify-center font-black text-xl group-hover:scale-105 transition-transform duration-300 <?= $m['type'] == 'ENTRADA' ? 'bg-emerald-50 text-emerald-600 group-hover:bg-emerald-100' : 'bg-rose-50 text-rose-600 group-hover:bg-rose-100' ?>">
                                <?= $m['type'] == 'ENTRADA' ? '+' : '-' ?>
                            </div>
                            <div>
                                <h4 class="text-base font-extrabold text-gray-900 group-hover:text-[#2563eb] transition-colors"><?= htmlspecialchars($m['productName']) ?></h4>
                                <p class="text-xs text-gray-500 font-medium mt-0.5">
                                    <?php
                                        $details = [];
                                        if (!empty($m['chamado'])) $details[] = "Chamado: " . $m['chamado'];
                                        if (!empty($m['serie'])) $details[] = "Série: " . $m['serie'];
                                        if (!empty($m['unidadeDestino'])) $details[] = "Destino: " . $m['unidadeDestino'];
                                        if (!empty($m['tecnico'])) $details[] = "Téc: " . $m['tecnico'];
                                        echo htmlspecialchars(implode(' • ', $details));
                                    ?>
                                </p>
                                <div class="mt-2 flex items-center gap-2 text-[11px] text-gray-400 font-bold uppercase tracking-wider">
                                    <span class="inline-flex items-center gap-1 <?= strpos($m['estoque'], 'Wyntech') !== false ? 'bg-red-50 text-red-700' : 'bg-blue-50 text-blue-700' ?> px-2 py-0.5 rounded-md">
                                        <?= $m['estoque'] ?>
                                    </span>
                                    <span><?= date('d/m/Y H:i', strtotime($m['date'])) ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-6 relative z-10">
                            <div class="text-right">
                                <div class="text-2xl font-black text-gray-900 tracking-tight">
                                    <?= $m['quantity'] ?>
                                </div>
                                <div class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Un</div>
                            </div>
                            <div class="flex items-center gap-2 opacity-50 group-hover:opacity-100 transition-opacity">
                                <button
                                    @click="modalOpen = true; formMovId = '<?= $m['id'] ?>'; type = '<?= $m['type'] ?>'; estoque = '<?= $m['estoque'] ?>'; productId = '<?= $m['productId'] ?>'; chamado = '<?= htmlspecialchars($m['chamado'], ENT_QUOTES) ?>'; formSerie = '<?= htmlspecialchars($m['serie'], ENT_QUOTES) ?>'; quantity = <?= $m['quantity'] ?>; document.getElementById('formTecnico').value = '<?= htmlspecialchars($m['tecnico'], ENT_QUOTES) ?>';"
                                    class="p-2.5 rounded-xl text-gray-400 hover:text-[#2563eb] hover:bg-blue-50 transition-colors"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </button>
                                <form method="POST" action="movimentacoes.php" onsubmit="return confirm('Tem certeza que deseja excluir esta movimentação? A quantidade correspondente retornará ao estoque de origem.');" class="inline">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $m['id'] ?>">
                                    <button type="submit" class="p-2.5 rounded-xl text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal Form -->
    <div x-show="modalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
        <div class="absolute inset-0 bg-[#111625]/60 backdrop-blur-sm" @click="modalOpen = false"></div>
        <div class="relative w-full max-w-xl bg-white rounded-3xl shadow-2xl overflow-hidden border border-gray-100 z-10 max-h-[90vh] flex flex-col">
            <div class="flex items-center justify-between px-8 pt-8 pb-2">
                <h3 class="text-2xl font-black text-gray-900 tracking-tight" x-text="formMovId ? 'Editar Movimentação' : 'Registrar Movimentação'"></h3>
                <button @click="modalOpen = false" type="button" class="p-1.5 rounded-full hover:bg-gray-200 text-gray-400 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <form method="POST" action="movimentacoes.php" class="px-8 pb-8 pt-2 space-y-6 overflow-y-auto flex-1">
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="movId" x-model="formMovId">
                
                <!-- Type Selection -->
                <div>
                    <label class="block text-[11px] font-black text-slate-400 uppercase tracking-wider mb-2">Tipo de Movimento</label>
                    <div class="flex gap-3">
                        <button type="button" @click="type = 'ENTRADA'" :class="type === 'ENTRADA' ? 'border-emerald-500 bg-emerald-50 text-emerald-700 ring-2 ring-emerald-500 shadow-md scale-105' : 'border-gray-200 text-gray-500 hover:bg-gray-50'" class="flex-1 rounded-2xl border py-4 flex flex-col items-center justify-center gap-2 transition-all duration-300">
                            <div class="h-10 w-10 rounded-full flex items-center justify-center transition-colors" :class="type === 'ENTRADA' ? 'bg-emerald-500 text-white' : 'bg-gray-100 text-gray-400'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
                            </div>
                            <span class="font-bold text-sm">Entrada</span>
                        </button>
                        <button type="button" @click="type = 'SAÍDA'" :class="type === 'SAÍDA' ? 'border-rose-500 bg-rose-50 text-rose-700 ring-2 ring-rose-500 shadow-md scale-105' : 'border-gray-200 text-gray-500 hover:bg-gray-50'" class="flex-1 rounded-2xl border py-4 flex flex-col items-center justify-center gap-2 transition-all duration-300">
                            <div class="h-10 w-10 rounded-full flex items-center justify-center transition-colors" :class="type === 'SAÍDA' ? 'bg-rose-500 text-white' : 'bg-gray-100 text-gray-400'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>
                            </div>
                            <span class="font-bold text-sm">Saída</span>
                        </button>
                    </div>
                    <input type="hidden" name="type" x-model="type">
                </div>

                <!-- Estoque Selection -->
                <div>
                    <label class="block text-[11px] font-black text-slate-400 uppercase tracking-wider mb-1">Estoque Origem/Destino *</label>
                    <select name="estoque" required x-model="estoque" class="w-full rounded-xl border-0 bg-slate-50 px-4 py-3.5 text-sm ring-1 ring-inset ring-slate-200 focus:ring-2 focus:ring-inset focus:ring-blue-600 transition-all font-semibold">
                        <option value="Caixa Econômica Federal">Caixa Econômica Federal</option>
                        <option value="Wyntech">Wyntech</option>
                    </select>
                </div>

                <!-- Product Selection -->
                <div>
                    <label class="block text-[11px] font-black text-slate-400 uppercase tracking-wider mb-1">Peça *</label>
                    <select name="productId" required x-model="productId" class="w-full rounded-xl border-0 bg-slate-50 px-4 py-3.5 text-sm ring-1 ring-inset ring-slate-200 focus:ring-2 focus:ring-inset focus:ring-blue-600 transition-all bg-white">
                        <option value="">Selecione uma peça...</option>
                        <?php foreach ($products as $p): ?>
                            <template x-if="estoque === '<?= htmlspecialchars($p['estoque']) ?>' || !'<?= htmlspecialchars($p['estoque']) ?>'">
                                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?> (Estoque: <?= $p['quantity'] ?>)</option>
                            </template>
                        <?php endforeach; ?>
                    </select>
                    
                    <template x-if="type === 'SAÍDA' && productId">
                        <div class="mt-4">
                            <label class="block text-[11px] font-black text-slate-400 uppercase tracking-wider mb-1">Série da Peça</label>
                            <select name="serie" x-model="formSerie" class="w-full rounded-xl border-0 bg-slate-50 px-4 py-3.5 text-sm ring-1 ring-inset ring-slate-200 focus:ring-2 focus:ring-inset focus:ring-blue-600 transition-all bg-white">
                                <option value="">Nenhuma / Não se aplica</option>
                                <template x-for="serie in (availableSeries[productId] || [])" :key="serie">
                                    <option :value="serie" x-text="serie"></option>
                                </template>
                            </select>
                            <template x-if="!(availableSeries[productId] || []).length">
                                <p class="text-xs text-gray-400 mt-1 italic">Nenhuma série registrada neste estoque.</p>
                            </template>
                        </div>
                    </template>
                    <p class="text-xs text-gray-400 mt-1" x-show="!productId">Apenas peças do estoque <span x-text="estoque" class="font-bold"></span> são exibidas.</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Quantidade *</label>
                        <input type="number" name="quantity" x-model="quantity" required min="1" class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm outline-none focus:border-gray-500 focus:ring-1">
                    </div>
                    <template x-if="type === 'SAÍDA'">
                        <div>
                            <label class="block text-[11px] font-black text-slate-400 uppercase tracking-wider mb-1">Chamado *</label>
                            <input type="text" name="reason" required x-model="chamado" class="w-full rounded-xl border-0 bg-slate-50 px-4 py-3.5 text-sm ring-1 ring-inset ring-slate-200 focus:ring-2 focus:ring-inset focus:ring-blue-600 transition-all">
                        </div>
                    </template>
                    <template x-if="type === 'ENTRADA'">
                        <div>
                            <label class="block text-[11px] font-black text-slate-400 uppercase tracking-wider mb-1">Série da Peça</label>
                            <input type="text" name="serie" x-model="formSerie" placeholder="Opcional" class="w-full rounded-xl border-0 bg-slate-50 px-4 py-3.5 text-sm ring-1 ring-inset ring-slate-200 focus:ring-2 focus:ring-inset focus:ring-blue-600 transition-all">
                        </div>
                    </template>
                </div>

                <!-- Conditional Fields -->
                <template x-if="type === 'SAÍDA'">
                    <div class="space-y-4 p-4 bg-gray-50 rounded-2xl border border-gray-100">
                        <template x-if="estoque === 'Caixa Econômica Federal'">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-[11px] font-black text-slate-400 uppercase tracking-wider mb-1">Unidade Destino *</label>
                                    <input type="text" name="unidadeDestino" list="agencies-list" placeholder="Pesquise a agência" class="w-full rounded-xl border-0 bg-slate-50 px-4 py-3.5 text-sm ring-1 ring-inset ring-slate-200 focus:ring-2 focus:ring-inset focus:ring-blue-600 transition-all">
                                    <datalist id="agencies-list">
                                        <?php foreach ($agencies as $ag): ?>
                                            <option value="<?= htmlspecialchars($ag['codigo'] . ' - ' . $ag['nome']) ?>"></option>
                                        <?php endforeach; ?>
                                    </datalist>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-[11px] font-black text-slate-400 uppercase tracking-wider mb-1">Usuário Recebedor</label>
                                        <input type="text" name="usuario" class="w-full rounded-xl border-0 bg-slate-50 px-4 py-3.5 text-sm ring-1 ring-inset ring-slate-200 focus:ring-2 focus:ring-inset focus:ring-blue-600 transition-all">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-black text-slate-400 uppercase tracking-wider mb-1">Matrícula</label>
                                        <input type="text" name="matricula" class="w-full rounded-xl border-0 bg-slate-50 px-4 py-3.5 text-sm ring-1 ring-inset ring-slate-200 focus:ring-2 focus:ring-inset focus:ring-blue-600 transition-all">
                                    </div>
                                </div>
                            </div>
                        </template>

                        <template x-if="estoque === 'Wyntech'">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Técnico *</label>
                                    <select name="tecnico" id="formTecnico" class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm outline-none focus:border-gray-500 bg-white">
                                        <option value="">Selecione um técnico...</option>
                                        <?php foreach ($technicians as $t): ?>
                                            <option value="<?= htmlspecialchars($t['nome']) ?>"><?= htmlspecialchars($t['nome']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
                
                <div class="flex gap-3 pt-2">
                    <button type="button" @click="modalOpen = false" class="flex-1 rounded-xl border border-gray-200 py-3 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition">Cancelar</button>
                    <button type="submit" class="flex-1 rounded-xl bg-gray-900 py-3 text-sm font-semibold text-white shadow-md hover:bg-gray-800 transition">Salvar Movimento</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- WhatsApp Success Prompt -->
    <?php if ($whatsappUrl): ?>
    <div x-show="showPrompt" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
        <div class="absolute inset-0 bg-[#111625]/60 backdrop-blur-sm" @click="showPrompt = false"></div>
        <div class="relative w-full max-w-sm bg-white rounded-3xl shadow-2xl p-6 z-10"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
             
            <div class="w-14 h-14 bg-[#eefcf3] rounded-2xl flex items-center justify-center text-[#25D366] mb-5">
                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/>
                </svg>
            </div>
            
            <h3 class="text-xl font-extrabold text-[#1f2937] mb-2 text-left">Notificar Técnico</h3>
            <p class="text-sm text-[#6b7280] mb-8 text-left leading-relaxed font-medium">Processo salvo! Deseja enviar os detalhes para o WhatsApp de <?= htmlspecialchars($whatsappTech) ?>?</p>
            
            <div class="flex justify-end items-center gap-2">
                <button @click="showPrompt = false" class="px-5 py-2.5 text-sm font-bold text-[#4b5563] hover:text-[#111827] transition">
                    Agora não
                </button>
                <a href="<?= htmlspecialchars($whatsappUrl) ?>" target="_blank" @click="showPrompt = false" class="px-5 py-2.5 rounded-xl bg-[#22c55e] hover:bg-[#16a34a] text-sm font-bold text-white shadow-sm transition">
                    Enviar WhatsApp
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Teams Success Prompt -->
    <?php if ($teamsUrl): ?>
    <div x-show="showPrompt" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
        <div class="absolute inset-0 bg-[#111625]/60 backdrop-blur-sm" @click="showPrompt = false"></div>
        <div class="relative w-full max-w-sm bg-white rounded-3xl shadow-2xl p-6 z-10"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
             
            <div class="w-16 h-16 bg-transparent flex items-center justify-center mb-5">
                <svg class="w-14 h-14 drop-shadow-md" viewBox="0 0 256 256" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M165.333 133.333H224C235.782 133.333 245.333 142.884 245.333 154.667V245.333C245.333 251.224 240.557 256 234.667 256H165.333C118.239 256 80 217.761 80 170.667C80 150.048 80 133.333 80 133.333Z" fill="#5059C9"/>
                    <path d="M197.333 122.667C217.953 122.667 234.667 105.953 234.667 85.3333C234.667 64.7147 217.953 48 197.333 48C176.715 48 160 64.7147 160 85.3333C160 105.953 176.715 122.667 197.333 122.667Z" fill="#5059C9"/>
                    <path d="M154.667 106.667H64C46.3269 106.667 32 120.994 32 138.667V245.333C32 251.224 36.7761 256 42.6667 256H154.667C195.905 256 229.333 222.571 229.333 181.333C229.333 140.095 195.905 106.667 154.667 106.667Z" fill="#7B83EB"/>
                    <circle cx="122.667" cy="74.6667" r="42.6667" fill="#7B83EB"/>
                    <path d="M117.333 128H21.3333C9.55059 128 0 137.551 0 149.333V224C0 235.782 9.55059 245.333 21.3333 245.333H117.333C129.116 245.333 138.667 235.782 138.667 224V149.333C138.667 137.551 129.116 128 117.333 128Z" fill="#464EB8"/>
                    <path d="M85.3333 154.667H53.3333V218.667H37.3333V154.667H5.33333V144H85.3333V154.667Z" fill="#FFFFFF"/>
                </svg>
            </div>
            
            <h3 class="text-xl font-extrabold text-[#1f2937] mb-2 text-left">Notificar via Teams</h3>
            <p class="text-sm text-[#6b7280] mb-8 text-left leading-relaxed font-medium">Registro salvo! Deseja enviar os detalhes desta movimentação para C138776 via Teams?</p>
            
            <div class="flex justify-end items-center gap-2">
                <button @click="showPrompt = false" class="px-5 py-2.5 text-sm font-bold text-[#4b5563] hover:text-[#111827] transition">
                    Agora não
                </button>
                <a href="<?= htmlspecialchars($teamsUrl) ?>" target="_blank" @click="showPrompt = false" class="px-5 py-2.5 rounded-xl bg-[#5B5FC7] hover:bg-[#464aa6] text-sm font-bold text-white shadow-sm shadow-[#5B5FC7]/30 transition">
                    Enviar Teams
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php
require_once 'middleware.php'; include 'layout/footer.php'; ?>
