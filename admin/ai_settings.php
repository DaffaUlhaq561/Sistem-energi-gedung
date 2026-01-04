<?php
require_once __DIR__ . '/../config.php';
require_once BASE_PATH . '/auth.php';
require_once BASE_PATH . '/lib/crypto.php';
require_once BASE_PATH . '/lib/EnergyInsights.php';

if (($currentUser['username'] ?? '') !== 'admin') {
    http_response_code(403);
    echo 'Hanya admin yang dapat mengakses halaman ini.';
    exit;
}

function pdo_conn()
{
    global $DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT;
    $dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};port={$DB_PORT};charset=utf8mb4";
    return new PDO($dsn, $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
}

$message = null;
$messageType = 'success';
$current = null;
$hasApiKey = false;

try {
    $pdo = pdo_conn();
    ai_ensure_tables($pdo);
    $stmt = $pdo->query("SELECT enabled, api_key_encrypted, model, temperature, anomaly_threshold_pct, updated_at FROM settings_ai WHERE id = 1 LIMIT 1");
    $current = $stmt->fetch();
    $hasApiKey = !empty($current['api_key_encrypted']);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $enabled = isset($_POST['enabled']) ? 1 : 0;
        $model = trim($_POST['model'] ?? 'gpt-4o-mini');
        $temperature = (float)($_POST['temperature'] ?? 0.2);
        $threshold = (float)($_POST['anomaly_threshold_pct'] ?? 30);
        $newKey = trim($_POST['api_key'] ?? '');

        $pdo->beginTransaction();
        if ($newKey !== '') {
            $encrypted = crypto_encrypt($newKey);
            $sql = "
                INSERT INTO settings_ai (id, enabled, api_key_encrypted, model, temperature, anomaly_threshold_pct, updated_at)
                VALUES (1, :enabled, :api_key_encrypted, :model, :temperature, :threshold, NOW())
                ON DUPLICATE KEY UPDATE
                    enabled = VALUES(enabled),
                    api_key_encrypted = VALUES(api_key_encrypted),
                    model = VALUES(model),
                    temperature = VALUES(temperature),
                    anomaly_threshold_pct = VALUES(anomaly_threshold_pct),
                    updated_at = NOW()
            ";
            $stmtSave = $pdo->prepare($sql);
            $stmtSave->execute([
                ':enabled' => $enabled,
                ':api_key_encrypted' => $encrypted,
                ':model' => $model,
                ':temperature' => $temperature,
                ':threshold' => $threshold,
            ]);
        } else {
            // keep existing key
            $sql = "
                INSERT INTO settings_ai (id, enabled, model, temperature, anomaly_threshold_pct, updated_at)
                VALUES (1, :enabled, :model, :temperature, :threshold, NOW())
                ON DUPLICATE KEY UPDATE
                    enabled = VALUES(enabled),
                    model = VALUES(model),
                    temperature = VALUES(temperature),
                    anomaly_threshold_pct = VALUES(anomaly_threshold_pct),
                    updated_at = NOW()
            ";
            $stmtSave = $pdo->prepare($sql);
            $stmtSave->execute([
                ':enabled' => $enabled,
                ':model' => $model,
                ':temperature' => $temperature,
                ':threshold' => $threshold,
            ]);
        }
        $pdo->commit();
        $message = 'Pengaturan AI berhasil disimpan.';
        
        $stmt = $pdo->query("SELECT enabled, api_key_encrypted, model, temperature, anomaly_threshold_pct, updated_at FROM settings_ai WHERE id = 1 LIMIT 1");
        $current = $stmt->fetch();
        $hasApiKey = !empty($current['api_key_encrypted']);
    }
} catch (Exception $e) {
    try {
        if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
    } catch (Throwable $ignore) {}
    $message = 'Kesalahan: ' . $e->getMessage();
    $messageType = 'danger';
}

include BASE_PATH . '/partials/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="page-title mb-1"><i class="fas fa-robot me-2"></i>AI Settings</h1>
        <p class="text-muted mb-0">Kelola status AI, API key (terenkripsi), model, dan ambang anomali.</p>
    </div>
    <a href="../dashboard.php#ai-chat" class="btn btn-outline-primary">
        <i class="fas fa-arrow-left me-1"></i>Kembali ke Dashboard
    </a>
</div>

<div class="alert alert-info mb-4">
    <h6 class="alert-heading"><i class="fas fa-lightbulb me-2"></i>Tentang AI Chat</h6>
    <p class="mb-0">
        AI Chat adalah asisten monitoring yang dapat membantu Anda memahami kondisi energi gedung secara real-time. 
        AI akan otomatis mengakses data energi terbaru, mendeteksi anomali, dan memberikan saran berdasarkan kondisi aktual aplikasi.
        <br><strong>Untuk menggunakan fitur ini, pastikan Anda sudah memasukkan OpenAI API Key di bawah.</strong>
    </p>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-key me-2"></i>Konfigurasi AI</h5>
        <?php if (!empty($current['updated_at'])): ?>
            <small class="text-muted">Update: <?php echo htmlspecialchars($current['updated_at']); ?></small>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <form method="POST">
            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" id="enabled" name="enabled" <?php echo (!empty($current) && !empty($current['enabled'])) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="enabled">Aktifkan AI Chat</label>
            </div>
            <div class="mb-3">
                <?php
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
                ?>
                <small class="text-muted d-block">
                    Status: <?php echo $sodiumActive ? 'Sodium aktif ✅' : 'Sodium nonaktif ❌'; ?> ·
                    <?php echo $envSet ? 'APP_MASTER_KEY ada ✅' : 'APP_MASTER_KEY tidak ada ❌'; ?>
                </small>
                <?php if (!$sodiumActive): ?>
                <div class="alert alert-warning mt-2 mb-0">
                    <small>
                        <strong>Cara mengaktifkan Sodium extension:</strong><br>
                        1. Buka file <code>php.ini</code> di folder XAMPP (biasanya di <code>C:\xampp\php\php.ini</code>)<br>
                        2. Cari baris <code>;extension=sodium</code> dan hapus tanda <code>;</code> menjadi <code>extension=sodium</code><br>
                        3. Simpan file dan restart Apache di XAMPP Control Panel<br>
                        4. Jika extension tidak ada, download dari <a href="https://pecl.php.net/package/libsodium" target="_blank">PECL</a> atau gunakan OpenSSL sebagai fallback
                    </small>
                </div>
                <?php endif; ?>
                <?php if (!$envSet): ?>
                <div class="alert alert-warning mt-2 mb-0">
                    <small>
                        <strong>Cara set APP_MASTER_KEY:</strong><br>
                        1. <strong>Environment Variable:</strong> Set di sistem operasi atau .htaccess<br>
                        2. <strong>PHP Config:</strong> Tambahkan di config.php: <code>define('APP_MASTER_KEY', 'your-secret-key-here');</code><br>
                        3. <strong>XAMPP Windows:</strong> Edit php.ini atau set di .htaccess dengan <code>SetEnv APP_MASTER_KEY "your-key"</code>
                    </small>
                </div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label class="form-label" for="api_key">
                    OpenAI API Key 
                    <?php if (isset($hasApiKey) && $hasApiKey): ?>
                        <span class="badge bg-success ms-2">✓ Sudah disimpan</span>
                    <?php else: ?>
                        <span class="badge bg-warning ms-2">⚠ Belum disimpan</span>
                    <?php endif; ?>
                </label>
                <input type="password" class="form-control" id="api_key" name="api_key" placeholder="sk-..." autocomplete="off">
                <small class="text-muted d-block mt-1">
                    <i class="fas fa-info-circle me-1"></i>
                    Masukkan API key OpenAI Anda. API key akan dienkripsi dan disimpan dengan aman.
                    <br>
                    <strong>Cara mendapatkan API key:</strong> Kunjungi <a href="https://platform.openai.com/api-keys" target="_blank">https://platform.openai.com/api-keys</a> dan buat API key baru.
                    <br>
                    <em>Kosongkan field ini jika tidak ingin mengganti API key yang sudah ada.</em>
                </small>
            </div>

            <div class="mb-3">
                <label class="form-label" for="model">Model</label>
                <select class="form-select" id="model" name="model">
                    <?php
                    $models = ['gpt-4o-mini', 'gpt-4o', 'gpt-4.1-mini', 'gpt-4.1'];
                    $selected = (!empty($current) && isset($current['model'])) ? $current['model'] : 'gpt-4o-mini';
                    foreach ($models as $m) {
                        $sel = $m === $selected ? 'selected' : '';
                        echo "<option value=\"{$m}\" {$sel}>{$m}</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label" for="temperature">Temperature</label>
                    <input type="number" step="0.1" min="0" max="1" class="form-control" id="temperature" name="temperature" value="<?php echo htmlspecialchars((!empty($current) && isset($current['temperature'])) ? $current['temperature'] : 0.2); ?>">
                    <small class="text-muted">0 = deterministik, 1 = lebih kreatif</small>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="anomaly_threshold_pct">Ambang Anomali (%)</label>
                    <input type="number" step="1" min="1" max="500" class="form-control" id="anomaly_threshold_pct" name="anomaly_threshold_pct" value="<?php echo htmlspecialchars((!empty($current) && isset($current['anomaly_threshold_pct'])) ? $current['anomaly_threshold_pct'] : 30); ?>">
                    <small class="text-muted">Pct_change >= ambang dianggap lonjakan.</small>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i>Simpan Pengaturan
            </button>
        </form>
    </div>
</div>

<?php include BASE_PATH . '/partials/footer.php'; ?>
