<?php
require_once __DIR__ . '/config.php';

class Database {
    private static $conn;

    public static function getConnection() {
        if (self::$conn === null) {
            try {
                self::$conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
                self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch(PDOException $exception) {
                echo json_encode(["error" => "Connection failed: " . $exception->getMessage()]);
                exit;
            }
        }
        return self::$conn;
    }
}
?>