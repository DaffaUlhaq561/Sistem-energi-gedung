<?php
require_once __DIR__ . '/../../config.php';
require_once BASE_PATH . '/auth.php';

header('Content-Type: application/json; charset=utf-8');

function pdo_conn()
{
    global $DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT;
    $dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};port={$DB_PORT};charset=utf8mb4";
    return new PDO($dsn, $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
}

try {
    $pdo = pdo_conn();
    require_once BASE_PATH . '/lib/EnergyInsights.php';
    
    // Pastikan tabel ada
    ai_ensure_tables($pdo);
    
    $sodiumActive = extension_loaded('sodium');
    // Cek berbagai cara untuk mendapatkan APP_MASTER_KEY
    $envSet = false;
    if (getenv('APP_MASTER_KEY')) {
        $envSet = true;
    } elseif (isset($_ENV['APP_MASTER_KEY'])) {
        $envSet = true;
    } elseif (isset($_SERVER['APP_MASTER_KEY'])) {
        $envSet = true;
    } elseif (defined('APP_MASTER_KEY')) {
        $envSet = true;
    }
    $hasSettings = false;
    $enabled = false;
    $hasKey = false;
    try {
        $row = $pdo->query("SELECT enabled, api_key_encrypted FROM settings_ai WHERE id = 1 LIMIT 1")->fetch();
        if ($row) {
            $hasSettings = true;
            $enabled = ((int)$row['enabled'] === 1);
            $hasKey = !empty($row['api_key_encrypted']);
        }
    } catch (Exception $e) {
        $hasSettings = false;
    }
    echo json_encode([
        'sodium_active' => $sodiumActive,
        'app_master_key_set' => $envSet,
        'has_settings_row' => $hasSettings,
        'enabled' => $enabled,
        'has_key' => $hasKey,
        'configured' => ($sodiumActive && $envSet && $hasSettings && $enabled && $hasKey),
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Gagal memeriksa status AI', 'error' => $e->getMessage()]);
}
