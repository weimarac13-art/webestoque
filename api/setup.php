<?php
require_once 'config.php';

try {
    // Connect without database first
    $dsn = "mysql:host=" . DB_HOST . ";charset=utf8mb4";
    $conn = new PDO($dsn, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create DB if not exists
    $conn->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
    $conn->exec("USE " . DB_NAME);

    // Read and execute database.sql
    $sql = file_get_contents('../database.sql');
    if ($sql === false) {
        throw new Exception("Cannot read database.sql");
    }
    
    // Some PDO drivers don't support multi-query execution well, but for simple schema it might work.
    $conn->exec($sql);
    
    echo "<h1>Database initialized successfully!</h1>";
    echo "<p>You can now go back to <a href='/webestoque'>WebEstoque</a></p>";

} catch (Exception $e) {
    echo "<h1>Error Initializing Database</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
}
