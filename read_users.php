<?php
$db = new PDO('sqlite:C:/xampp/htdocs/sisjur/database.sqlite');
$stmt = $db->query("SELECT * FROM users");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
