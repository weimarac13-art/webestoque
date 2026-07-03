<?php
function logAction($db, $action, $details) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $userName = $_SESSION['nome'] ?? $_SESSION['username'] ?? 'Sistema';
    $userId = $_SESSION['user_id'] ?? '0';
    $perfil = $_SESSION['perfil'] ?? 'Desconhecido';
    
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

    try {
        $stmt = $db->prepare("INSERT INTO system_logs (createdAt, userName, userId, perfil, action, details, ipAddress, userAgent) VALUES (:createdAt, :userName, :userId, :perfil, :action, :details, :ipAddress, :userAgent)");
        $stmt->execute([
            ':createdAt' => date('Y-m-d H:i:s'),
            ':userName' => $userName,
            ':userId' => $userId,
            ':perfil' => $perfil,
            ':action' => $action,
            ':details' => $details,
            ':ipAddress' => $ipAddress,
            ':userAgent' => $userAgent
        ]);
    } catch (PDOException $e) {
        file_put_contents(__DIR__ . '/logger_error.log', date('Y-m-d H:i:s') . " - " . $e->getMessage() . "\n", FILE_APPEND);
    }
}
?>
