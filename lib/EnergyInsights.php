<?php

require_once __DIR__ . '/../config.php';

function ensure_core_tables(PDO $pdo)
{
    // Buat tabel users jika belum ada
    try {
        $pdo->query("SELECT 1 FROM users LIMIT 1");
    } catch (Exception $e) {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) NOT NULL UNIQUE,
                password_hash VARCHAR(255) NOT NULL,
                name VARCHAR(100) NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }
    
    // Buat tabel energy_readings jika belum ada
    try {
        $pdo->query("SELECT 1 FROM energy_readings LIMIT 1");
    } catch (Exception $e) {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS energy_readings (
                id BIGINT AUTO_INCREMENT PRIMARY KEY,
                reading_time DATETIME NOT NULL,
                energy_kwh DECIMAL(10,2) NOT NULL,
                INDEX idx_reading_time (reading_time)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }
    
    // Buat tabel maintenance_notes jika belum ada
    try {
        $pdo->query("SELECT 1 FROM maintenance_notes LIMIT 1");
    } catch (Exception $e) {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS maintenance_notes (
                id INT AUTO_INCREMENT PRIMARY KEY,
                note TEXT NOT NULL,
                status ENUM('not_started','in_progress','completed') NOT NULL DEFAULT 'not_started',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }
}

function ai_pdo()
{
    global $DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT;
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};port={$DB_PORT};charset=utf8mb4";
        $pdo = new PDO($dsn, $DB_USER, $DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
    return $pdo;
}

function ai_ensure_tables(PDO $pdo)
{
    // Pastikan tabel dasar ada terlebih dahulu
    ensure_core_tables($pdo);
    
    // Cek dan buat tabel settings_ai jika belum ada
    try {
        $pdo->query("SELECT 1 FROM settings_ai LIMIT 1");
    } catch (Exception $e) {
        try {
            $pdo->exec("DROP TABLE IF EXISTS settings_ai");
        } catch (Exception $ignore) {}
        $createSettingsTable = "
            CREATE TABLE IF NOT EXISTS settings_ai (
                id INT PRIMARY KEY DEFAULT 1,
                enabled TINYINT(1) NOT NULL DEFAULT 0,
                api_key_encrypted TEXT,
                model VARCHAR(100) NOT NULL DEFAULT 'gpt-4o-mini',
                temperature DECIMAL(3,2) NOT NULL DEFAULT 0.20,
                anomaly_threshold_pct DECIMAL(5,2) NOT NULL DEFAULT 30.00,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        $pdo->exec($createSettingsTable);
        try {
            $pdo->query("SELECT 1 FROM settings_ai LIMIT 1");
        } catch (Exception $ignore) {}
    }
    
    // Cek dan buat tabel ai_chat_sessions jika belum ada
    try {
        $pdo->query("SELECT 1 FROM ai_chat_sessions LIMIT 1");
    } catch (Exception $e) {
        $createSessionsTable = "
            CREATE TABLE IF NOT EXISTS ai_chat_sessions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                title VARCHAR(255) NOT NULL DEFAULT 'Chat',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_user (user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        $pdo->exec($createSessionsTable);
    }
    
    // Cek dan buat tabel ai_chat_messages jika belum ada
    try {
        $pdo->query("SELECT 1 FROM ai_chat_messages LIMIT 1");
    } catch (Exception $e) {
        // Cek dulu apakah tabel sessions sudah ada (untuk foreign key)
        try {
            $pdo->query("SELECT 1 FROM ai_chat_sessions LIMIT 1");
            $createMessagesTable = "
                CREATE TABLE IF NOT EXISTS ai_chat_messages (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    session_id INT NOT NULL,
                    role ENUM('user','assistant') NOT NULL,
                    content TEXT NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_session (session_id),
                    CONSTRAINT fk_ai_chat_session FOREIGN KEY (session_id) REFERENCES ai_chat_sessions(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ";
            $pdo->exec($createMessagesTable);
        } catch (Exception $e2) {
            // Jika sessions belum ada, buat tanpa foreign key dulu
            $createMessagesTable = "
                CREATE TABLE IF NOT EXISTS ai_chat_messages (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    session_id INT NOT NULL,
                    role ENUM('user','assistant') NOT NULL,
                    content TEXT NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_session (session_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ";
            $pdo->exec($createMessagesTable);
        }
    }
}

function ai_load_settings(PDO $pdo)
{
    // Pastikan tabel ada sebelum query
    ai_ensure_tables($pdo);
    
    $stmt = $pdo->query("SELECT enabled, api_key_encrypted, model, temperature, anomaly_threshold_pct FROM settings_ai WHERE id = 1 LIMIT 1");
    $row = $stmt->fetch();
    return $row ?: null;
}

function ai_energy_snapshot(PDO $pdo, $thresholdPct = 30.0)
{
    $latestStmt = $pdo->query("SELECT reading_time, energy_kwh FROM energy_readings ORDER BY reading_time DESC LIMIT 1");
    $latest = $latestStmt->fetch();
    if (!$latest) {
        return [
            'has_data' => false,
            'anomaly' => false,
            'message' => 'Belum ada data energi',
        ];
    }

    $latestTime = $latest['reading_time'];
    $currentVal = (float)$latest['energy_kwh'];

    $baselineStmt = $pdo->prepare("
        SELECT AVG(energy_kwh) AS avg_kw
        FROM energy_readings
        WHERE reading_time >= DATE_SUB(?, INTERVAL 30 MINUTE)
          AND reading_time < ?
    ");
    $baselineStmt->execute([$latestTime, $latestTime]);
    $baselineRow = $baselineStmt->fetch();
    $baseline = (float)($baselineRow['avg_kw'] ?? 0);

    $pctChange = $baseline > 0 ? (($currentVal - $baseline) / $baseline) * 100 : null;
    $isAnomaly = $pctChange !== null && $pctChange >= $thresholdPct;

    $statsStmt = $pdo->prepare("
        SELECT AVG(energy_kwh) AS avg_24h, MAX(energy_kwh) AS max_24h, MIN(energy_kwh) AS min_24h
        FROM energy_readings
        WHERE reading_time >= DATE_SUB(?, INTERVAL 24 HOUR)
    ");
    $statsStmt->execute([$latestTime]);
    $stats = $statsStmt->fetch();

    $anomalies = [];
    if ($isAnomaly) {
        $anomalies[] = [
            'timestamp' => $latestTime,
            'value' => $currentVal,
            'baseline' => round($baseline, 2),
            'pct_change' => round($pctChange, 2),
            'threshold_pct' => (float)$thresholdPct,
            'reason' => 'Lonjakan di atas ambang persen'
        ];
    }

    return [
        'has_data' => true,
        'latest' => [
            'time' => $latestTime,
            'value' => $currentVal,
        ],
        'baseline' => [
            'value' => round($baseline, 2),
            'window_minutes' => 30
        ],
        'pct_change' => $pctChange !== null ? round($pctChange, 2) : null,
        'anomaly' => $isAnomaly,
        'anomalies' => $anomalies,
        'stats_24h' => [
            'avg' => isset($stats['avg_24h']) ? round((float)$stats['avg_24h'], 2) : null,
            'max' => isset($stats['max_24h']) ? round((float)$stats['max_24h'], 2) : null,
            'min' => isset($stats['min_24h']) ? round((float)$stats['min_24h'], 2) : null,
        ],
    ];
}
