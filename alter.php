<?php
require 'api/db.php';
$db = Database::getConnection();
try {
    $db->exec("ALTER TABLE products ADD COLUMN estoque VARCHAR(50) DEFAULT 'CAIXA'");
    echo "Success!";
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'Duplicate column') !== false) {
        echo "Already exists.";
    } else {
        echo $e->getMessage();
    }
}
