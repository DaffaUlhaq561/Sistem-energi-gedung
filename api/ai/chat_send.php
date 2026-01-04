<?php
require_once __DIR__ . '/../../config.php';
require_once BASE_PATH . '/auth.php';
require_once BASE_PATH . '/lib/crypto.php';
require_once BASE_PATH . '/lib/OpenAIClient.php';
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

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

$sessionId = (int)($input['session_id'] ?? 0);
$message = trim($input['message'] ?? '');

if ($sessionId <= 0 || $message === '') {
    http_response_code(400);
    echo json_encode(['message' => 'session_id atau pesan tidak valid']);
    exit;
}

try {
    $pdo = pdo_conn();
    
    // Pastikan tabel ada
    ai_ensure_tables($pdo);
    
    $settings = ai_load_settings($pdo);
    if (!$settings || (int)$settings['enabled'] !== 1) {
        http_response_code(400);
        echo json_encode(['message' => 'AI tidak aktif atau belum dikonfigurasi.']);
        exit;
    }
    if (empty($settings['api_key_encrypted'])) {
        http_response_code(400);
        echo json_encode(['message' => 'API key belum disimpan.']);
        exit;
    }

    $apiKey = crypto_decrypt($settings['api_key_encrypted']);
    $model = $settings['model'] ?? 'gpt-4o-mini';
    $temperature = isset($settings['temperature']) ? (float)$settings['temperature'] : 0.2;
    $thresholdPct = isset($settings['anomaly_threshold_pct']) ? (float)$settings['anomaly_threshold_pct'] : 30.0;

    // Verifikasi kepemilikan sesi
    $check = $pdo->prepare("SELECT id FROM ai_chat_sessions WHERE id = :id AND user_id = :user_id LIMIT 1");
    $check->execute([':id' => $sessionId, ':user_id' => $currentUser['id']]);
    if (!$check->fetch()) {
        http_response_code(403);
        echo json_encode(['message' => 'Sesi tidak ditemukan atau tidak milik Anda']);
        exit;
    }

    // Simpan pesan user
    $insertUser = $pdo->prepare("INSERT INTO ai_chat_messages (session_id, role, content) VALUES (:session_id, 'user', :content)");
    $insertUser->execute([':session_id' => $sessionId, ':content' => $message]);

    // Ambil 15 pesan terakhir
    $histStmt = $pdo->prepare("SELECT role, content FROM ai_chat_messages WHERE session_id = :session_id ORDER BY created_at DESC LIMIT 15");
    $histStmt->execute([':session_id' => $sessionId]);
    $history = array_reverse($histStmt->fetchAll());

    // Ambil konteks energi
    $insights = ai_energy_snapshot($pdo, $thresholdPct);
    $contextStr = "Data energi terbaru:\n";
    if (!$insights['has_data']) {
        $contextStr .= "Belum ada data energi.\n";
    } else {
        $contextStr .= sprintf(
            "- Terbaru: %s = %.2f kWh\n- Baseline 30 menit: %.2f kWh\n- Pct change: %s%%\n- Ambang anomali: %.2f%%\n",
            $insights['latest']['time'],
            $insights['latest']['value'],
            $insights['baseline']['value'],
            $insights['pct_change'] !== null ? $insights['pct_change'] : 'N/A',
            $thresholdPct
        );
        if (!empty($insights['stats_24h']['avg'])) {
            $contextStr .= sprintf(
                "- Rata-rata 24 jam: %.2f kWh | Maks: %.2f kWh | Min: %.2f kWh\n",
                $insights['stats_24h']['avg'],
                $insights['stats_24h']['max'],
                $insights['stats_24h']['min']
            );
        }
        if (!empty($insights['anomalies'])) {
            foreach ($insights['anomalies'] as $an) {
                $contextStr .= sprintf(
                    "- Anomali: %s = %.2f kWh (baseline %.2f, +%.2f%%)\n",
                    $an['timestamp'],
                    $an['value'],
                    $an['baseline'],
                    $an['pct_change']
                );
            }
        }
    }

    $systemPrompt = "Asisten monitoring energi gedung. Gunakan data yang diberikan, jangan mengarang angka. Jika ada anomali, beri kemungkinan sebab & langkah cek. Jawab singkat dan actionable.";
    $messagesForAI = array_merge(
        [['role' => 'user', 'content' => $contextStr]],
        $history
    );

    $assistantReply = ai_openai_request($apiKey, $model, $temperature, $systemPrompt, $messagesForAI);

    // Simpan balasan
    $insertBot = $pdo->prepare("INSERT INTO ai_chat_messages (session_id, role, content) VALUES (:session_id, 'assistant', :content)");
    $insertBot->execute([':session_id' => $sessionId, ':content' => $assistantReply]);

    // Update updated_at sesi
    $upd = $pdo->prepare("UPDATE ai_chat_sessions SET updated_at = NOW() WHERE id = :id");
    $upd->execute([':id' => $sessionId]);

    echo json_encode([
        'assistant' => $assistantReply,
        'insights' => $insights,
        'anomaly' => $insights['anomaly'] ?? false,
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['message' => 'Gagal memproses chat', 'error' => $e->getMessage()]);
}
