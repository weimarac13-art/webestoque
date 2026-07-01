<?php
require_once 'config.php';

class Database {
    private static $conn = null;

    public static function getConnection() {
        if (self::$conn === null) {
            try {
                $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
                self::$conn = new PDO($dsn, DB_USER, DB_PASS);
                self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                http_response_code(500);
                die(json_encode(["error" => "Connection failed: " . $e->getMessage()]));
            }
        }
        return self::$conn;
    }
}
