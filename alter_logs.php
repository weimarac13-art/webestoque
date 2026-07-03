<?php
require 'api/db.php';
$db = Database::getConnection();
try {
    $db->exec("ALTER TABLE system_logs ADD COLUMN perfil VARCHAR(100) AFTER userId");
    echo "Success";
} catch (Exception $e) {
    echo $e->getMessage();
}
?>
