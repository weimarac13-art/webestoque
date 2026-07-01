<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once 'db.php';

$method = $_SERVER['REQUEST_METHOD'];
$db = Database::getConnection();

switch ($method) {
    case 'GET':
        try {
            $stmt = $db->query("SELECT * FROM agencies ORDER BY nome ASC");
            $agencies = $stmt->fetchAll();
            echo json_encode($agencies);
        } catch(PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => $e->getMessage()]);
        }
        break;

    case 'POST':
    case 'PUT':
        $data = json_decode(file_get_contents("php://input"), true);
        if (!$data || !isset($data['id'])) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid data"]);
            exit;
        }

        try {
            $stmt = $db->prepare("SELECT id FROM agencies WHERE id = :id");
            $stmt->execute([':id' => $data['id']]);
            $exists = $stmt->fetch();

            if ($exists) {
                $sql = "UPDATE agencies SET 
                        codigo = :codigo, nome = :nome
                        WHERE id = :id";
                
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    ':id' => $data['id'],
                    ':codigo' => $data['codigo'],
                    ':nome' => $data['nome']
                ]);
            } else {
                $sql = "INSERT INTO agencies (id, codigo, nome, createdAt) 
                        VALUES (:id, :codigo, :nome, :createdAt)";
                
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    ':id' => $data['id'],
                    ':codigo' => $data['codigo'],
                    ':nome' => $data['nome'],
                    ':createdAt' => $data['createdAt'] ?? date('Y-m-d H:i:s')
                ]);
            }
            echo json_encode(["success" => true, "id" => $data['id']]);
        } catch(PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => $e->getMessage()]);
        }
        break;

    case 'DELETE':
        $id = $_GET['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(["error" => "ID is required"]);
            exit;
        }

        try {
            $stmt = $db->prepare("DELETE FROM agencies WHERE id = :id");
            $stmt->execute([':id' => $id]);
            echo json_encode(["success" => true]);
        } catch(PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => $e->getMessage()]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Method not allowed"]);
        break;
}
