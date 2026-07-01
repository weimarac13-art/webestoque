<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once 'db.php';

$method = $_SERVER['REQUEST_METHOD'];
$db = Database::getConnection();

if ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    if (!$data || !isset($data['username']) || !isset($data['password'])) {
        http_response_code(400);
        echo json_encode(["error" => "Usuário e senha são obrigatórios"]);
        exit;
    }

    try {
        $stmt = $db->prepare("SELECT id, username, password FROM users WHERE username = :username");
        $stmt->execute([':username' => $data['username']]);
        $user = $stmt->fetch();

        // In a real app, use password_verify(), but we use plain '123' as requested.
        if ($user && ($user['password'] === $data['password'] || password_verify($data['password'], $user['password']))) {
            echo json_encode([
                "success" => true,
                "user" => [
                    "id" => $user['id'],
                    "username" => $user['username']
                ]
            ]);
        } else {
            http_response_code(401);
            echo json_encode(["error" => "Usuário ou senha incorretos"]);
        }
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
}
