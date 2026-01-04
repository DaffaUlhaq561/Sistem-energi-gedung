<?php
require_once __DIR__ . '/../../config.php';
require_once BASE_PATH . '/auth.php';
require_once BASE_PATH . '/lib/EnergyInsights.php';

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
    
    // Pastikan tabel ada
    ai_ensure_tables($pdo);
    
    $settings = ai_load_settings($pdo);
    if (!$settings || (int)$settings['enabled'] !== 1) {
        http_response_code(400);
        echo json_encode(['message' => 'AI belum diaktifkan atau belum dikonfigurasi.']);
        exit;
    }

    $title = 'Chat ' . date('d M H:i');
    $stmt = $pdo->prepare("INSERT INTO ai_chat_sessions (user_id, title) VALUES (:user_id, :title)");
    $stmt->execute([
        ':user_id' => $currentUser['id'],
        ':title' => $title,
    ]);
    $sessionId = (int)$pdo->lastInsertId();

    echo json_encode(['session_id' => $sessionId, 'title' => $title]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Gagal membuat sesi', 'error' => $e->getMessage()]);
}
