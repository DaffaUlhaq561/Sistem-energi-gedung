<?php
/**
 * Script untuk membuat tabel AI secara manual
 * Akses via: http://localhost/itera4_/create_ai_tables.php
 */

require_once 'config.php';
require_once 'lib/EnergyInsights.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create AI Tables</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
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
        pre {
            background: #f4f4f4;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Create AI Tables</h1>
        
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
            
            $pdo = pdo_conn();
            
            echo '<div class="status info">';
            echo '<strong>Mencoba membuat tabel AI...</strong><br>';
            echo '</div>';
            
            // Panggil fungsi ensure tables
            ai_ensure_tables($pdo);
            
            echo '<div class="status success">';
            echo '<strong>✅ Tabel AI berhasil dibuat!</strong><br><br>';
            echo 'Tabel yang dibuat:<br>';
            echo '1. settings_ai<br>';
            echo '2. ai_chat_sessions<br>';
            echo '3. ai_chat_messages<br>';
            echo '</div>';
            
            // Verifikasi tabel
            echo '<div class="status info">';
            echo '<strong>Verifikasi tabel:</strong><br>';
            
            try {
                $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM settings_ai");
                $row = $stmt->fetch();
                echo '✓ settings_ai: OK (rows: ' . $row['cnt'] . ')<br>';
            } catch (Exception $e) {
                echo '✗ settings_ai: ERROR - ' . $e->getMessage() . '<br>';
            }
            
            try {
                $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM ai_chat_sessions");
                $row = $stmt->fetch();
                echo '✓ ai_chat_sessions: OK (rows: ' . $row['cnt'] . ')<br>';
            } catch (Exception $e) {
                echo '✗ ai_chat_sessions: ERROR - ' . $e->getMessage() . '<br>';
            }
            
            try {
                $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM ai_chat_messages");
                $row = $stmt->fetch();
                echo '✓ ai_chat_messages: OK (rows: ' . $row['cnt'] . ')<br>';
            } catch (Exception $e) {
                echo '✗ ai_chat_messages: ERROR - ' . $e->getMessage() . '<br>';
            }
            
            echo '</div>';
            
            echo '<div class="status success">';
            echo '<strong>✅ Selesai!</strong><br>';
            echo 'Sekarang Anda bisa mengakses halaman AI Settings tanpa error.<br>';
            echo '<a href="admin/ai_settings.php">Buka AI Settings</a>';
            echo '</div>';
            
        } catch (Exception $e) {
            echo '<div class="status error">';
            echo '<strong>❌ Error:</strong><br>';
            echo htmlspecialchars($e->getMessage());
            echo '</div>';
            
            echo '<div class="status info">';
            echo '<strong>Detail Error:</strong><br>';
            echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
            echo '</div>';
        }
        ?>
    </div>
</body>
</html>

