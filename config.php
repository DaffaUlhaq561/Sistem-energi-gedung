<?php
if (!defined('BASE_PATH')) {
    define('BASE_PATH', realpath(__DIR__));
}

function load_env_file($path)
{
    if (!is_string($path) || !is_file($path) || !is_readable($path)) return;
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if ($line === '' || substr($line, 0, 1) === '#') continue;
        $pos = strpos($line, '=');
        if ($pos === false) continue;
        $key = trim(substr($line, 0, $pos));
        $val = trim(substr($line, $pos + 1));
        if ($key === '') continue;
        if ((substr($val, 0, 1) === '"' && substr($val, -1) === '"') || (substr($val, 0, 1) === "'" && substr($val, -1) === "'")) {
            $val = substr($val, 1, -1);
        }
        putenv($key . '=' . $val);
        $_ENV[$key] = $val;
        $_SERVER[$key] = $val;
    }
}

load_env_file(BASE_PATH . '/.env');
load_env_file(BASE_PATH . '/php-docker/.env');

// Pastikan session path aman di Linux container
if (function_exists('session_save_path')) {
    @session_save_path('/tmp');
}

// Konfigurasi koneksi database MySQL (portable: env > default)
$DB_HOST = getenv('DB_HOST') ?: (getenv('MYSQL_HOST') ?: 'localhost');
$DB_USER = getenv('DB_USER') ?: (getenv('MYSQL_USER') ?: 'root');
$DB_PASS = getenv('DB_PASS') ?: (getenv('MYSQL_PASSWORD') ?: '');
$DB_NAME = getenv('DB_NAME') ?: (getenv('MYSQL_DATABASE') ?: 'energy_monitoring');
$DB_PORT = (int)(getenv('DB_PORT') ?: (getenv('MYSQL_PORT') ?: 3306));

// APP_MASTER_KEY untuk enkripsi API key OpenAI
// PENTING: Lebih aman set via environment variable atau .htaccess
// Jika tidak bisa, uncomment baris di bawah dan ganti dengan key yang kuat (minimal 32 karakter)
// define('APP_MASTER_KEY', 'ganti-dengan-key-rahasia-yang-sangat-panjang-dan-aman-minimal-32-karakter');

function db()
{
    global $DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT;
    $mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, (int)$DB_PORT);
    if ($mysqli->connect_errno) {
        throw new Exception('Gagal konek database: ' . $mysqli->connect_error);
    }
    $mysqli->set_charset('utf8mb4');
    ensure_core_tables_mysqli($mysqli);
    ensure_seed_energy_readings_mysqli($mysqli);
    ensure_seed_maintenance_notes_mysqli($mysqli);
    ensure_default_admin_mysqli($mysqli);
    return $mysqli;
}

function ensure_core_tables_mysqli(mysqli $conn)
{
    try { $conn->query("SELECT 1 FROM users LIMIT 1"); }
    catch (Throwable $e) {
        $conn->query("CREATE TABLE IF NOT EXISTS users (id INT AUTO_INCREMENT PRIMARY KEY, username VARCHAR(50) NOT NULL UNIQUE, password_hash VARCHAR(255) NOT NULL, name VARCHAR(100) NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }
    try { $conn->query("SELECT 1 FROM energy_readings LIMIT 1"); }
    catch (Throwable $e) {
        $conn->query("CREATE TABLE IF NOT EXISTS energy_readings (id BIGINT AUTO_INCREMENT PRIMARY KEY, reading_time DATETIME NOT NULL, energy_kwh DECIMAL(10,2) NOT NULL, INDEX idx_reading_time (reading_time)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }
    try { $conn->query("SELECT 1 FROM maintenance_notes LIMIT 1"); }
    catch (Throwable $e) {
        $conn->query("CREATE TABLE IF NOT EXISTS maintenance_notes (id INT AUTO_INCREMENT PRIMARY KEY, note TEXT NOT NULL, status ENUM('not_started','in_progress','completed') NOT NULL DEFAULT 'not_started', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }
}

function ensure_default_admin_mysqli(mysqli $conn)
{
    $res = $conn->query("SELECT COUNT(*) AS cnt FROM users WHERE username='admin'");
    $row = $res ? $res->fetch_assoc() : null;
    if (!$row || (int)$row['cnt'] === 0) {
        $hash = password_hash('admin', PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, password_hash, name) VALUES ('admin', ?, 'Admin Operasional')");
        if ($stmt) { $stmt->bind_param('s', $hash); $stmt->execute(); $stmt->close(); }
    }
}

function ensure_seed_energy_readings_mysqli(mysqli $conn)
{
    $res = $conn->query("SELECT COUNT(*) AS cnt FROM energy_readings");
    $row = $res ? $res->fetch_assoc() : null;
    if ($row && (int)$row['cnt'] === 0) {
        $stmt = $conn->prepare("INSERT INTO energy_readings (reading_time, energy_kwh) VALUES (?, ?)");
        if ($stmt) {
            for ($i = 29; $i >= 0; $i--) {
                $ts = time() - ($i * 60);
                $reading_time = date('Y-m-d H:i:s', $ts);
                $base = 45 + 35 * sin(($i) / 3);
                $noise = ((crc32((string)$i) % 1000) / 1000) * 10 - 5;
                $energy = max(5, round($base + $noise, 2));
                $stmt->bind_param('sd', $reading_time, $energy);
                $stmt->execute();
            }
            $stmt->close();
        }
    }
}

function ensure_seed_maintenance_notes_mysqli(mysqli $conn)
{
    $res = $conn->query("SELECT COUNT(*) AS cnt FROM maintenance_notes");
    $row = $res ? $res->fetch_assoc() : null;
    if ($row && (int)$row['cnt'] === 0) {
        $notes = [
            ['Periksa panel listrik lantai 2', 'in_progress'],
            ['Kalibrasi meter utama gedung', 'not_started'],
            ['Ganti MCB ruang server', 'completed'],
            ['Audit beban puncak minggu kemarin', 'in_progress'],
            ['Bersihkan ruang genset dan ventilasi', 'not_started'],
        ];
        $stmt = $conn->prepare("INSERT INTO maintenance_notes (note, status) VALUES (?, ?)");
        if ($stmt) {
            foreach ($notes as $n) {
                $stmt->bind_param('ss', $n[0], $n[1]);
                $stmt->execute();
            }
            $stmt->close();
        }
    }
}
