<?php
require_once 'middleware.php';
require_once 'api/db.php';
$db = Database::getConnection();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action']) && $_POST['action'] == 'save') {
        $id = $_POST['id'] ?? null;
        $sku = trim($_POST['sku'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $quantity = (int)($_POST['quantity'] ?? 0);
        $minQuantity = (int)($_POST['minQuantity'] ?? 0);
        $costPrice = (float)($_POST['costPrice'] ?? 0);
        $salePrice = (float)($_POST['salePrice'] ?? 0);
        $supplier = trim($_POST['supplier'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $estoque = $_POST['estoque'] ?? 'Caixa Econômica Federal';
        
        if ($id) {
            $stmt = $db->prepare("UPDATE products SET sku=:s, name=:n, category=:c, quantity=:q, minQuantity=:mq, costPrice=:cp, salePrice=:sp, supplier=:sup, location=:l, description=:d, estoque=:est WHERE id=:id");
            $stmt->execute([':id'=>$id, ':s'=>$sku, ':n'=>$name, ':c'=>$category, ':q'=>$quantity, ':mq'=>$minQuantity, ':cp'=>$costPrice, ':sp'=>$salePrice, ':sup'=>$supplier, ':l'=>$location, ':d'=>$description, ':est'=>$estoque]);
        } else {
            $id = 'prod-' . time();
            $stmt = $db->prepare("INSERT INTO products (id, sku, name, category, quantity, minQuantity, costPrice, salePrice, supplier, location, description, estoque, createdAt) VALUES (:id, :s, :n, :c, :q, :mq, :cp, :sp, :sup, :l, :d, :est, :ca)");
            $stmt->execute([':id'=>$id, ':s'=>$sku, ':n'=>$name, ':c'=>$category, ':q'=>$quantity, ':mq'=>$minQuantity, ':cp'=>$costPrice, ':sp'=>$salePrice, ':sup'=>$supplier, ':l'=>$location, ':d'=>$description, ':est'=>$estoque, ':ca'=>date('Y-m-d H:i:s')]);
        }
        header("Location: produtos.php?saved=1");
        exit;
    }
    
    if (isset($_POST['action']) && $_POST['action'] == 'delete') {
        $id = $_POST['id'] ?? null;
        if ($id) {
            $stmt = $db->prepare("DELETE FROM products WHERE id = :id");
            $stmt->execute([':id' => $id]);
        }
        header("Location: produtos.php");
        exit;
    }
}

$stmt = $db->query("
    SELECT MIN(id) as id, name, GROUP_CONCAT(DISTINCT estoque SEPARATOR ', ') as estoques 
    FROM products 
    GROUP BY name 
    ORDER BY name ASC
");
$products = $stmt->fetchAll();

$showPrompt = isset($_GET['saved']) ? 'true' : 'false';

include 'layout/header.php';
?>

<div class="space-y-5" x-data="{ 
    modalOpen: false, 
    showPrompt: <?= $showPrompt ?>,
    formId: '', formSku: '', formName: '', formCategory: '', formQtd: 0, formMinQtd: 0, formCost: 0, formSale: 0, formSupplier: '', formLoc: '', formDesc: '', formEstoque: 'Caixa Econômica Federal'
}">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900">Cadastro de Peças</h1>
            <p class="text-xs text-gray-500">Gerenciamento do catálogo de produtos.</p>
        </div>
        <button 
            @click="modalOpen = true; formId = ''; formSku = ''; formName = ''; formCategory = 'Caixa Terapêutica'; formQtd = 0; formMinQtd = 0; formCost = 0; formSale = 0; formSupplier = ''; formLoc = ''; formDesc = ''; formEstoque = 'Caixa Econômica Federal';"
            class="flex items-center justify-center gap-1.5 px-4 h-11 rounded-xl bg-[#2563eb] text-xs font-extrabold text-white hover:bg-[#1d4ed8] active:scale-95 transition-all shadow-md"
        >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
            Nova Peça
        </button>
    </div>

    <!-- List -->
    <div>
        <div class="mb-4 flex items-center justify-between">
            <h3 class="font-bold text-gray-800 text-sm tracking-wide uppercase">Catálogo</h3>
            <span class="text-xs font-bold text-gray-400 bg-white px-3 py-1.5 border border-gray-100 rounded-xl shadow-sm">
                <?= count($products) ?> cadastrado(s)
            </span>
        </div>

        <?php
require_once 'middleware.php'; if (count($products) == 0): ?>
            <div class="p-12 text-center bg-white rounded-3xl border border-gray-100 shadow-sm">
                <p class="text-gray-400 text-sm font-medium">Nenhuma peça cadastrada.</p>
            </div>
        <?php
require_once 'middleware.php'; else: ?>
            <div class="space-y-3">
                <?php
require_once 'middleware.php'; foreach ($products as $p): ?>
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex items-center justify-between gap-4 hover:shadow-md hover:border-blue-100 hover:-translate-y-0.5 transition-all duration-300 group relative overflow-hidden">
                        
                        <!-- Subtle background gradient on hover -->
                        <div class="absolute inset-0 bg-gradient-to-r from-blue-50/0 via-blue-50/0 to-blue-50/30 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none"></div>

                        <div class="flex items-center gap-4 flex-1 relative z-10">
                            <div class="h-12 w-12 bg-gray-50 rounded-2xl flex items-center justify-center text-gray-400 group-hover:bg-blue-50 group-hover:text-[#2563eb] group-hover:scale-105 transition-all duration-300">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                            </div>
                            <div>
                                <h4 class="text-base font-extrabold text-gray-900 group-hover:text-[#2563eb] transition-colors"><?= htmlspecialchars($p['name']) ?></h4>
                                <p class="text-xs text-gray-500 font-semibold mt-0.5">Cadastrado para: 
                                    <span class="font-bold <?php
require_once 'middleware.php'; 
                                        if(strpos($p['estoques'], 'Wyntech') !== false && strpos($p['estoques'], 'Caixa') !== false) echo 'text-purple-600';
                                        elseif(strpos($p['estoques'], 'Wyntech') !== false) echo 'text-red-700';
                                        else echo 'text-blue-700';
                                    ?> bg-gray-50 px-2 py-0.5 rounded-md inline-block"><?= $p['estoques'] ?></span>
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 relative z-10 opacity-50 group-hover:opacity-100 transition-opacity">
                            <button
                                @click="modalOpen = true; formId = '<?= $p['id'] ?>'; formName = '<?= htmlspecialchars($p['name'], ENT_QUOTES) ?>'; formEstoque = 'Caixa Econômica Federal';"
                                class="p-2.5 rounded-xl text-gray-400 hover:text-[#2563eb] hover:bg-blue-50 transition-colors"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                            </button>
                            <form method="POST" action="produtos.php" onsubmit="return confirm('Excluir esta peça?');" class="inline">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                <button type="submit" class="p-2.5 rounded-xl text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php
require_once 'middleware.php'; endforeach; ?>
            </div>
        <?php
require_once 'middleware.php'; endif; ?>
    </div>

    <!-- Modal Form -->
    <div x-show="modalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
        <div class="absolute inset-0 bg-[#111625]/60 backdrop-blur-sm" @click="modalOpen = false"></div>
        <div class="relative w-full max-w-lg bg-white rounded-3xl shadow-2xl overflow-hidden border border-gray-100 z-10 max-h-[90vh] flex flex-col">
            <div class="flex items-center justify-between px-8 pt-8 pb-2">
                <h3 class="text-2xl font-black text-gray-900 tracking-tight" x-text="formId ? 'Editar Peça' : 'Cadastrar Peça'"></h3>
                <button @click="modalOpen = false" type="button" class="p-1.5 rounded-full hover:bg-gray-200 text-gray-400 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <form method="POST" action="produtos.php" class="px-8 pb-8 pt-2 space-y-5 overflow-y-auto flex-1">
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="id" x-model="formId">
                <input type="hidden" name="sku" x-model="formSku">
                
                <div>
                    <label class="block text-[11px] font-black text-slate-400 uppercase tracking-wider mb-1">Nome da Peça *</label>
                    <input type="text" name="name" required x-model="formName" class="w-full rounded-xl border-0 bg-slate-50 px-4 py-3.5 text-sm ring-1 ring-inset ring-slate-200 focus:ring-2 focus:ring-inset focus:ring-blue-600 transition-all">
                </div>
                
                <div>
                    <label class="block text-[11px] font-black text-slate-400 uppercase tracking-wider mb-1">Estoque Pertencente *</label>
                    <select name="estoque" x-model="formEstoque" class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-semibold outline-none focus:border-[#2563eb] focus:ring-1 focus:ring-[#2563eb]/30 bg-gray-50">
                        <option value="Caixa Econômica Federal">Caixa Econômica Federal</option>
                        <option value="Wyntech">Wyntech</option>
                    </select>
                </div>
                
                <div class="flex gap-3 pt-4">
                    <button type="button" @click="modalOpen = false" class="flex-1 rounded-xl bg-white text-slate-700 ring-1 ring-inset ring-slate-300 py-3 text-sm font-bold hover:bg-slate-50 hover:text-slate-900 transition">Cancelar</button>
                    <button type="submit" class="flex-1 rounded-xl bg-[#2563eb] py-3 text-sm font-semibold text-white shadow-md hover:bg-[#1d4ed8] transition">Salvar Peça</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Success Prompt Modal -->
    <div x-show="showPrompt" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
        <div class="absolute inset-0 bg-[#111625]/60 backdrop-blur-sm" @click="showPrompt = false"></div>
        <div class="relative w-full max-w-sm bg-white rounded-3xl shadow-2xl p-6 text-center z-10"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            <div class="w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4 text-emerald-500">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
            </div>
            <h3 class="text-xl font-black text-gray-900 mb-2">Salvo com Sucesso!</h3>
            <p class="text-sm text-gray-500 mb-6">A peça foi registrada. Deseja cadastrar uma nova peça agora?</p>
            
            <div class="flex gap-3">
                <button @click="showPrompt = false" class="flex-1 rounded-xl bg-white text-slate-700 ring-1 ring-inset ring-slate-300 py-3 text-sm font-bold hover:bg-slate-50 hover:text-slate-900 transition">Não, Voltar</button>
                <button @click="showPrompt = false; modalOpen = true; formId = ''; formName = ''; formEstoque = 'Caixa Econômica Federal';" class="flex-1 rounded-xl bg-[#2563eb] py-3 text-sm font-semibold text-white shadow-md hover:bg-[#1d4ed8] transition">Sim, Cadastrar</button>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'middleware.php'; include 'layout/footer.php'; ?>
