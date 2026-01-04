<?php
/**
 * Script untuk setup database lengkap
 * Membuat semua tabel yang diperlukan untuk aplikasi
 * Akses via: http://localhost/itera4_/setup_database.php
 */

require_once 'config.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Database</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 900px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #0d6efd;
            padding-bottom: 10px;
        }
        .status {
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            border-left: 4px solid;
        }
        .success {
            background: #d4edda;
            border-color: #28a745;
            color: #155724;
        }
        .error {
            background: #f8d7da;
            border-color: #dc3545;
            color: #721c24;
        }
        .info {
            background: #d1ecf1;
            border-color: #17a2b8;
            color: #0c5460;
        }
        .warning {
            background: #fff3cd;
            border-color: #ffc107;
            color: #856404;
        }
        pre {
            background: #f4f4f4;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            font-size: 12px;
        }
        .table-list {
            list-style: none;
            padding: 0;
        }
        .table-list li {
            padding: 8px;
            margin: 5px 0;
            background: #f8f9fa;
            border-radius: 4px;
        }
        .table-list li.success {
            background: #d4edda;
        }
        .table-list li.error {
            background: #f8d7da;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Setup Database - Energy Monitoring</h1>
        
        <?php
        try {
            function pdo_conn()
            {
                global $DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT;
                $dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};port={$DB_PORT};charset=utf8mb4";
                return new PDO($dsn, $DB_USER, $DB_PASS, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
            }
            
            // Cek koneksi database
            echo '<div class="status info">';
            echo '<strong>Mencoba koneksi ke database...</strong><br>';
            echo "Host: {$DB_HOST}<br>";
            echo "Database: {$DB_NAME}<br>";
            echo '</div>';
            
            $pdo = pdo_conn();
            
            echo '<div class="status success">';
            echo '<strong>✅ Koneksi database berhasil!</strong><br>';
            echo '</div>';
            
            // Daftar semua tabel yang perlu dibuat
            $tables = [];
            
            // 1. Tabel users
            $tables['users'] = "
                CREATE TABLE IF NOT EXISTS users (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    username VARCHAR(50) NOT NULL UNIQUE,
                    password_hash VARCHAR(255) NOT NULL,
                    name VARCHAR(100) NOT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ";
            
            // 2. Tabel energy_readings
            $tables['energy_readings'] = "
                CREATE TABLE IF NOT EXISTS energy_readings (
                    id BIGINT AUTO_INCREMENT PRIMARY KEY,
                    reading_time DATETIME NOT NULL,
                    energy_kwh DECIMAL(10,2) NOT NULL,
                    INDEX idx_reading_time (reading_time)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ";
            
            // 3. Tabel maintenance_notes
            $tables['maintenance_notes'] = "
                CREATE TABLE IF NOT EXISTS maintenance_notes (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    note TEXT NOT NULL,
                    status ENUM('not_started','in_progress','completed') NOT NULL DEFAULT 'not_started',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ";
            
            // 4. Tabel settings_ai
            $tables['settings_ai'] = "
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
            
            // 5. Tabel ai_chat_sessions
            $tables['ai_chat_sessions'] = "
                CREATE TABLE IF NOT EXISTS ai_chat_sessions (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    title VARCHAR(255) NOT NULL DEFAULT 'Chat',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_user (user_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ";
            
            // 6. Tabel ai_chat_messages
            $tables['ai_chat_messages'] = "
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
            
            echo '<div class="status info">';
            echo '<strong>Membuat tabel...</strong><br>';
            echo '</div>';
            
            $results = [];
            $pdo->beginTransaction();
            
            foreach ($tables as $tableName => $sql) {
                try {
                    // Cek apakah tabel sudah ada
                    $check = $pdo->query("SHOW TABLES LIKE '{$tableName}'");
                    if ($check->rowCount() > 0) {
                        $results[$tableName] = ['status' => 'exists', 'message' => 'Tabel sudah ada'];
                    } else {
                        // Buat tabel
                        $pdo->exec($sql);
                        $results[$tableName] = ['status' => 'created', 'message' => 'Tabel berhasil dibuat'];
                    }
                } catch (Exception $e) {
                    $results[$tableName] = ['status' => 'error', 'message' => $e->getMessage()];
                }
            }
            
            $pdo->commit();
            
            // Tampilkan hasil
            echo '<div class="status info">';
            echo '<strong>Hasil pembuatan tabel:</strong><br>';
            echo '<ul class="table-list">';
            foreach ($results as $tableName => $result) {
                $class = $result['status'] === 'error' ? 'error' : 'success';
                $icon = $result['status'] === 'error' ? '❌' : ($result['status'] === 'exists' ? 'ℹ️' : '✅');
                echo "<li class=\"{$class}\">{$icon} <strong>{$tableName}</strong>: {$result['message']}</li>";
            }
            echo '</ul>';
            echo '</div>';
            
            // Verifikasi semua tabel
            echo '<div class="status info">';
            echo '<strong>Verifikasi tabel:</strong><br>';
            echo '<ul class="table-list">';
            
            $allTables = ['users', 'energy_readings', 'maintenance_notes', 'settings_ai', 'ai_chat_sessions', 'ai_chat_messages'];
            $allExists = true;
            
            foreach ($allTables as $tableName) {
                try {
                    $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM {$tableName}");
                    $row = $stmt->fetch();
                    echo "<li class=\"success\">✅ {$tableName}: OK (rows: {$row['cnt']})</li>";
                } catch (Exception $e) {
                    echo "<li class=\"error\">❌ {$tableName}: ERROR - " . htmlspecialchars($e->getMessage()) . "</li>";
                    $allExists = false;
                }
            }
            
            echo '</ul>';
            echo '</div>';
            
            // Setup data awal jika diperlukan
            echo '<div class="status info">';
            echo '<strong>Setup data awal...</strong><br>';
            
            try {
                // Cek apakah user admin sudah ada
                $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM users WHERE username = 'admin'");
                $row = $stmt->fetch();
                
                if ($row['cnt'] == 0) {
                    // Insert user admin default
                    $adminPassword = password_hash('admin', PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, name) VALUES ('admin', ?, 'Admin Operasional')");
                    $stmt->execute([$adminPassword]);
                    echo '✅ User admin berhasil dibuat (username: admin, password: admin)<br>';
                } else {
                    echo 'ℹ️ User admin sudah ada<br>';
                }
            } catch (Exception $e) {
                echo '⚠️ Gagal membuat user admin: ' . htmlspecialchars($e->getMessage()) . '<br>';
            }
            
            echo '</div>';
            
            if ($allExists) {
                echo '<div class="status success">';
                echo '<strong>✅ Setup Database Selesai!</strong><br><br>';
                echo 'Semua tabel berhasil dibuat dan diverifikasi.<br>';
                echo 'Anda sekarang bisa:<br>';
                echo '<ul>';
                echo '<li><a href="admin/ai_settings.php">Buka AI Settings</a></li>';
                echo '<li><a href="dashboard.php">Buka Dashboard</a></li>';
                echo '<li><a href="login.php">Login</a> (username: admin, password: admin)</li>';
                echo '</ul>';
                echo '</div>';
            } else {
                echo '<div class="status warning">';
                echo '<strong>⚠️ Beberapa tabel masih bermasalah</strong><br>';
                echo 'Silakan cek error di atas dan coba lagi.';
                echo '</div>';
            }
            
        } catch (PDOException $e) {
            echo '<div class="status error">';
            echo '<strong>❌ Database Error:</strong><br>';
            echo htmlspecialchars($e->getMessage());
            echo '</div>';
            
            echo '<div class="status info">';
            echo '<strong>Kemungkinan penyebab:</strong><br>';
            echo '1. Database belum dibuat - buat database "energy_monitoring" terlebih dahulu<br>';
            echo '2. Kredensial database salah - cek file config.php<br>';
            echo '3. MySQL/MariaDB tidak berjalan - pastikan XAMPP MySQL aktif<br>';
            echo '</div>';
        } catch (Exception $e) {
            echo '<div class="status error">';
            echo '<strong>❌ Error:</strong><br>';
            echo htmlspecialchars($e->getMessage());
            echo '</div>';
        }
        ?>
    </div>
</body>
</html>

