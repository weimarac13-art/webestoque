<?php
require_once 'api/db.php';
$db = Database::getConnection();

// Update Database
$db->exec("UPDATE products SET estoque = 'Caixa Econômica Federal' WHERE estoque = 'CAIXA'");
$db->exec("UPDATE movements SET estoque = 'Caixa Econômica Federal' WHERE estoque = 'CAIXA'");

echo "DB Updated.\n";

$files = [
    'produtos.php',
    'estoque.php',
    'movimentacoes.php',
    'relatorios.php',
    'layout/sidebar.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $newContent = str_replace("'CAIXA'", "'Caixa Econômica Federal'", $content);
        $newContent = str_replace('"CAIXA"', '"Caixa Econômica Federal"', $newContent);
        $newContent = str_replace('>CAIXA<', '>Caixa Econômica Federal<', $newContent);
        if ($content !== $newContent) {
            file_put_contents($file, $newContent);
            echo "Updated $file\n";
        }
    }
}
echo "Done.";
