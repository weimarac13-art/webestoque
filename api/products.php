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
require_once 'logger.php';

$method = $_SERVER['REQUEST_METHOD'];
$db = Database::getConnection();

switch ($method) {
    case 'GET':
        // Obter produtos
        try {
            $stmt = $db->query("SELECT * FROM products ORDER BY createdAt DESC");
            $products = $stmt->fetchAll();
            // Converter quantity e minQuantity para int, prices para float
            foreach($products as &$p) {
                $p['quantity'] = (int)$p['quantity'];
                $p['minQuantity'] = (int)$p['minQuantity'];
                $p['costPrice'] = (float)$p['costPrice'];
                $p['salePrice'] = (float)$p['salePrice'];
            }
            echo json_encode($products);
        } catch(PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => $e->getMessage()]);
        }
        break;

    case 'POST':
    case 'PUT':
        // Criar ou Editar produto
        $data = json_decode(file_get_contents("php://input"), true);
        if (!$data || !isset($data['id'])) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid data"]);
            exit;
        }

        try {
            // Check if exists
            $stmt = $db->prepare("SELECT id FROM products WHERE id = :id");
            $stmt->execute([':id' => $data['id']]);
            $exists = $stmt->fetch();

            if ($exists) {
                // Update
                $sql = "UPDATE products SET 
                        name = :name, sku = :sku, category = :category, 
                        minQuantity = :minQuantity, costPrice = :costPrice, salePrice = :salePrice, 
                        supplier = :supplier, location = :location, description = :description, 
                        estoque = :estoque 
                        WHERE id = :id";
                
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    ':id' => $data['id'],
                    ':name' => $data['name'],
                    ':sku' => $data['sku'],
                    ':category' => $data['category'],
                    ':minQuantity' => $data['minQuantity'],
                    ':costPrice' => $data['costPrice'],
                    ':salePrice' => $data['salePrice'],
                    ':supplier' => $data['supplier'],
                    ':location' => $data['location'],
                    ':description' => $data['description'],
                    ':estoque' => isset($data['estoque']) ? $data['estoque'] : null
                ]);
                logAction($db, 'Editar', "Produto " . $data['name'] . " editado.");
            } else {
                // Insert
                $sql = "INSERT INTO products (id, name, sku, category, quantity, minQuantity, costPrice, salePrice, supplier, location, description, createdAt, estoque) 
                        VALUES (:id, :name, :sku, :category, :quantity, :minQuantity, :costPrice, :salePrice, :supplier, :location, :description, :createdAt, :estoque)";
                
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    ':id' => $data['id'],
                    ':name' => $data['name'],
                    ':sku' => $data['sku'],
                    ':category' => $data['category'],
                    ':quantity' => $data['quantity'] ?? 0,
                    ':minQuantity' => $data['minQuantity'],
                    ':costPrice' => $data['costPrice'],
                    ':salePrice' => $data['salePrice'],
                    ':supplier' => $data['supplier'],
                    ':location' => $data['location'],
                    ':description' => $data['description'],
                    ':createdAt' => $data['createdAt'] ?? date('Y-m-d H:i:s'),
                    ':estoque' => isset($data['estoque']) ? $data['estoque'] : null
                ]);
                logAction($db, 'Novo', "Produto " . $data['name'] . " cadastrado.");
            }
            echo json_encode(["success" => true, "id" => $data['id']]);
        } catch(PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => $e->getMessage()]);
        }
        break;

    case 'DELETE':
        // Deletar produto
        $id = $_GET['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(["error" => "ID is required"]);
            exit;
        }

        try {
            $stmt = $db->prepare("DELETE FROM products WHERE id = :id");
            $stmt->execute([':id' => $id]);
            logAction($db, 'Excluir', "Produto ID " . $id . " excluído.");
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
