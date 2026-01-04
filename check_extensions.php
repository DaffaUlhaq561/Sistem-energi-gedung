<?php
/**
 * Script untuk mengecek status extension PHP
 * Akses via: http://localhost/itera4_/check_extensions.php
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Extensions Check</title>
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
        .warning {
            background: #fff3cd;
            border-color: #ffc107;
            color: #856404;
        }
        .info {
            background: #d1ecf1;
            border-color: #17a2b8;
            color: #0c5460;
        }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
        pre {
            background: #f4f4f4;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
        }
        .section {
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 PHP Extensions Check</h1>
        
        <?php
        // Cek Sodium
        $sodiumLoaded = extension_loaded('sodium');
        $sodiumAvailable = function_exists('sodium_crypto_secretbox');
        
        // Cek OpenSSL
        $opensslLoaded = extension_loaded('openssl');
        $opensslAvailable = function_exists('openssl_encrypt');
        
        // Cek APP_MASTER_KEY
        $masterKeySet = false;
        $masterKeySource = '';
        if (getenv('APP_MASTER_KEY')) {
            $masterKeySet = true;
            $masterKeySource = 'getenv()';
        } elseif (isset($_ENV['APP_MASTER_KEY'])) {
            $masterKeySet = true;
            $masterKeySource = '$_ENV';
        } elseif (isset($_SERVER['APP_MASTER_KEY'])) {
            $masterKeySet = true;
            $masterKeySource = '$_SERVER';
        } elseif (defined('APP_MASTER_KEY')) {
            $masterKeySet = true;
            $masterKeySource = 'define() in config.php';
        }
        ?>
        
        <div class="section">
            <h2>📦 Sodium Extension</h2>
            <?php if ($sodiumLoaded && $sodiumAvailable): ?>
                <div class="status success">
                    <strong>✅ Sodium Aktif</strong><br>
                    Extension loaded: <?php echo $sodiumLoaded ? 'Yes' : 'No'; ?><br>
                    Functions available: <?php echo $sodiumAvailable ? 'Yes' : 'No'; ?><br>
                    <?php if (defined('SODIUM_CRYPTO_SECRETBOX_NONCEBYTES')): ?>
                        Nonce bytes: <?php echo SODIUM_CRYPTO_SECRETBOX_NONCEBYTES; ?><br>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="status error">
                    <strong>❌ Sodium Tidak Aktif</strong><br>
                    Extension loaded: <?php echo $sodiumLoaded ? 'Yes' : 'No'; ?><br>
                    Functions available: <?php echo $sodiumAvailable ? 'Yes' : 'No'; ?><br>
                    <br>
                    <strong>Solusi:</strong><br>
                    1. Pastikan <code>extension=sodium</code> ada di php.ini (tanpa tanda ;)<br>
                    2. Pastikan file <code>php_sodium.dll</code> ada di folder <code>ext</code><br>
                    3. Restart Apache setelah mengubah php.ini<br>
                    4. Cek error log Apache jika masih tidak aktif
                </div>
            <?php endif; ?>
        </div>
        
        <div class="section">
            <h2>🔐 OpenSSL Extension</h2>
            <?php if ($opensslLoaded && $opensslAvailable): ?>
                <div class="status success">
                    <strong>✅ OpenSSL Aktif</strong><br>
                    Extension loaded: <?php echo $opensslLoaded ? 'Yes' : 'No'; ?><br>
                    Functions available: <?php echo $opensslAvailable ? 'Yes' : 'No'; ?><br>
                    <small>OpenSSL akan digunakan sebagai fallback jika Sodium tidak tersedia.</small>
                </div>
            <?php else: ?>
                <div class="status error">
                    <strong>❌ OpenSSL Tidak Aktif</strong><br>
                    Extension loaded: <?php echo $opensslLoaded ? 'Yes' : 'No'; ?><br>
                    Functions available: <?php echo $opensslAvailable ? 'Yes' : 'No'; ?><br>
                    <br>
                    <strong>PENTING:</strong> OpenSSL diperlukan sebagai fallback jika Sodium tidak aktif!
                </div>
            <?php endif; ?>
        </div>
        
        <div class="section">
            <h2>🔑 APP_MASTER_KEY</h2>
            <?php if ($masterKeySet): ?>
                <div class="status success">
                    <strong>✅ APP_MASTER_KEY Terdeteksi</strong><br>
                    Sumber: <code><?php echo htmlspecialchars($masterKeySource); ?></code><br>
                    Panjang: <?php 
                        $key = getenv('APP_MASTER_KEY') ?: ($_ENV['APP_MASTER_KEY'] ?? ($_SERVER['APP_MASTER_KEY'] ?? (defined('APP_MASTER_KEY') ? APP_MASTER_KEY : '')));
                        echo strlen($key);
                    ?> karakter
                </div>
            <?php else: ?>
                <div class="status error">
                    <strong>❌ APP_MASTER_KEY Tidak Ditemukan</strong><br>
                    <br>
                    <strong>Cara set:</strong><br>
                    1. Via .htaccess: <code>SetEnv APP_MASTER_KEY "your-key"</code><br>
                    2. Via config.php: <code>define('APP_MASTER_KEY', 'your-key');</code><br>
                    3. Via environment variable sistem
                </div>
            <?php endif; ?>
        </div>
        
        <div class="section">
            <h2>📋 Informasi PHP</h2>
            <div class="status info">
                <strong>PHP Version:</strong> <?php echo PHP_VERSION; ?><br>
                <strong>PHP ini file:</strong> <?php echo php_ini_loaded_file(); ?><br>
                <strong>Additional ini files:</strong> <?php echo php_ini_scanned_files() ?: 'None'; ?><br>
                <strong>Server API:</strong> <?php echo php_sapi_name(); ?><br>
            </div>
        </div>
        
        <div class="section">
            <h2>🔧 Test Enkripsi</h2>
            <?php
            require_once 'lib/crypto.php';
            try {
                $testKey = 'test-key-123';
                $encrypted = crypto_encrypt($testKey);
                $decrypted = crypto_decrypt($encrypted);
                
                if ($decrypted === $testKey) {
                    echo '<div class="status success">';
                    echo '<strong>✅ Enkripsi/Dekripsi Berhasil</strong><br>';
                    echo 'Method: ' . (strpos($encrypted, 'SODIUM:') === 0 ? 'Sodium' : 'OpenSSL') . '<br>';
                    echo 'Encrypted: <code>' . htmlspecialchars(substr($encrypted, 0, 50)) . '...</code>';
                    echo '</div>';
                } else {
                    echo '<div class="status error">';
                    echo '<strong>❌ Enkripsi/Dekripsi Gagal</strong><br>';
                    echo 'Decrypted tidak match dengan original';
                    echo '</div>';
                }
            } catch (Exception $e) {
                echo '<div class="status error">';
                echo '<strong>❌ Error: ' . htmlspecialchars($e->getMessage()) . '</strong>';
                echo '</div>';
            }
            ?>
        </div>
        
        <div class="section">
            <h2>📝 Extension yang Ter-load</h2>
            <pre><?php
            $extensions = get_loaded_extensions();
            sort($extensions);
            echo implode("\n", $extensions);
            ?></pre>
        </div>
        
        <div class="section">
            <h2>⚠️ Error Log (jika ada)</h2>
            <?php
            $errorLog = ini_get('error_log');
            if ($errorLog && file_exists($errorLog)) {
                $lines = file($errorLog);
                $recentLines = array_slice($lines, -20);
                echo '<pre>' . htmlspecialchars(implode('', $recentLines)) . '</pre>';
            } else {
                echo '<div class="status info">Error log tidak ditemukan atau tidak dikonfigurasi.</div>';
            }
            ?>
        </div>
        
        <div class="section">
            <h2>💡 Rekomendasi</h2>
            <?php if (!$sodiumLoaded && $opensslLoaded): ?>
                <div class="status warning">
                    <strong>⚠️ Sodium tidak aktif, menggunakan OpenSSL</strong><br>
                    Sistem akan tetap berfungsi dengan OpenSSL sebagai fallback.<br>
                    Untuk mengaktifkan Sodium:<br>
                    1. Pastikan <code>extension=sodium</code> di php.ini (tanpa ;)<br>
                    2. Restart Apache<br>
                    3. Jika masih error, cek apakah <code>php_sodium.dll</code> ada di folder <code>ext</code>
                </div>
            <?php elseif ($sodiumLoaded): ?>
                <div class="status success">
                    <strong>✅ Semua extension siap!</strong><br>
                    Sodium aktif dan siap digunakan untuk enkripsi API key.
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

