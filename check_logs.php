<?php
require 'api/db.php';
$db = Database::getConnection();
$stmt = $db->query("SELECT * FROM system_logs");
$logs = $stmt->fetchAll();
print_r($logs);
?>
