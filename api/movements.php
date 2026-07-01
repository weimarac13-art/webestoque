<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
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
            $stmt = $db->query("SELECT * FROM movements ORDER BY date DESC");
            $movements = $stmt->fetchAll();
            foreach($movements as &$m) {
                $m['quantity'] = (int)$m['quantity'];
            }
            echo json_encode($movements);
        } catch(PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => $e->getMessage()]);
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        if (!$data || !isset($data['id'])) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid data"]);
            exit;
        }

        try {
            $db->beginTransaction();

            // Insert movement
            $sql = "INSERT INTO movements (id, productId, productName, type, quantity, date, reason, responsible, estoque, serie, tecnico, chamado, unidadeDestino, usuario, matricula) 
                    VALUES (:id, :productId, :productName, :type, :quantity, :date, :reason, :responsible, :estoque, :serie, :tecnico, :chamado, :unidadeDestino, :usuario, :matricula)";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':id' => $data['id'],
                ':productId' => $data['productId'],
                ':productName' => $data['productName'],
                ':type' => $data['type'],
                ':quantity' => $data['quantity'],
                ':date' => $data['date'],
                ':reason' => $data['reason'],
                ':responsible' => $data['responsible'],
                ':estoque' => $data['estoque'] ?? null,
                ':serie' => $data['serie'] ?? null,
                ':tecnico' => $data['tecnico'] ?? null,
                ':chamado' => $data['chamado'] ?? null,
                ':unidadeDestino' => $data['unidadeDestino'] ?? null,
                ':usuario' => $data['usuario'] ?? null,
                ':matricula' => $data['matricula'] ?? null
            ]);

            // Update product quantity
            $qtyDiff = $data['type'] === 'ENTRADA' ? (int)$data['quantity'] : -(int)$data['quantity'];
            
            $updateSql = "UPDATE products SET quantity = quantity + :qtyDiff WHERE id = :productId";
            $updateStmt = $db->prepare($updateSql);
            $updateStmt->execute([
                ':qtyDiff' => $qtyDiff,
                ':productId' => $data['productId']
            ]);

            $db->commit();
            echo json_encode(["success" => true, "id" => $data['id']]);

        } catch(PDOException $e) {
            $db->rollBack();
            http_response_code(500);
            echo json_encode(["error" => $e->getMessage()]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Method not allowed"]);
        break;
}
