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
    
    $stmt = $pdo->prepare("SELECT id, title, updated_at FROM ai_chat_sessions WHERE user_id = :user_id ORDER BY updated_at DESC");
    $stmt->execute([':user_id' => $currentUser['id']]);
    $rows = $stmt->fetchAll();
    $rows = array_map(function ($r) {
        return [
            'id' => (int)$r['id'],
            'title' => $r['title'],
            'updated_at' => $r['updated_at'],
        ];
    }, $rows);
    echo json_encode(['sessions' => $rows]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Gagal memuat sesi', 'error' => $e->getMessage()]);
}
