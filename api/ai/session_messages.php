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

$sessionId = (int)($_GET['session_id'] ?? 0);

if ($sessionId <= 0) {
    http_response_code(400);
    echo json_encode(['message' => 'session_id tidak valid']);
    exit;
}

try {
    $pdo = pdo_conn();
    require_once BASE_PATH . '/lib/EnergyInsights.php';
    
    // Pastikan tabel ada
    ai_ensure_tables($pdo);
    
    $check = $pdo->prepare("SELECT id FROM ai_chat_sessions WHERE id = :id AND user_id = :user_id LIMIT 1");
    $check->execute([':id' => $sessionId, ':user_id' => $currentUser['id']]);
    if (!$check->fetch()) {
        http_response_code(403);
        echo json_encode(['message' => 'Sesi tidak ditemukan atau tidak milik Anda']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT role, content, created_at FROM ai_chat_messages WHERE session_id = :session_id ORDER BY created_at ASC");
    $stmt->execute([':session_id' => $sessionId]);
    $messages = $stmt->fetchAll();
    echo json_encode(['messages' => $messages]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Gagal memuat pesan', 'error' => $e->getMessage()]);
}
