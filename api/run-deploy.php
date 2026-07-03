<?php
/**
 * API for Real-time FTP Deploy (SSE)
 */
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo "data: " . json_encode(['type' => 'error', 'message' => 'Não autenticado']) . "\n\n";
    exit;
}
session_write_close(); // Libera o lock da sessão para não travar o sistema


require_once 'db.php';
require_once 'ftp_sync.php';

$db = Database::getConnection();

// Create settings table if not exists (for future use if they want to add settings UI)
try {
    $db->exec("CREATE TABLE IF NOT EXISTS settings (
        `key` VARCHAR(50) PRIMARY KEY,
        `value` VARCHAR(255) NOT NULL
    )");
} catch (PDOException $e) {}

$config = [];
try {
    $stmt = $db->query("SELECT * FROM settings");
    $raw_settings = $stmt->fetchAll();
    foreach ($raw_settings as $s) {
        $config[$s['key']] = $s['value'];
    }
} catch (PDOException $e) {}

if (empty($config['ftp_host'])) {
    echo "data: " . json_encode(['type' => 'error', 'message' => 'Configurações de FTP não encontradas']) . "\n\n";
    exit;
}

$deployer = new FtpDeployer($config);
$include_db = ($_GET['include_db'] ?? '0') === '1';

if (!$include_db) {
    $deployer->addIgnorePattern('database.sql');
}

if (!$deployer->connect()) {
    echo "data: " . json_encode(['type' => 'error', 'message' => $deployer->getErrorMessage() ?: 'Falha na conexão FTP.']) . "\n\n";
    exit;
}

set_time_limit(0);
ignore_user_abort(true);

$local_path = realpath(__DIR__ . '/../');

try {
    $deployer->deploy($local_path, function($file, $status) {
        echo "data: " . json_encode([
            'type' => 'progress',
            'file' => $file,
            'status' => $status
        ]) . "\n\n";
        ob_flush();
        flush();
    });

    echo "data: " . json_encode(['type' => 'finished']) . "\n\n";
} catch (Exception $e) {
    echo "data: " . json_encode(['type' => 'error', 'message' => $e->getMessage()]) . "\n\n";
}

$deployer->disconnect();
