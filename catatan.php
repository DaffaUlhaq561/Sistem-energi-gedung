<?php
require_once __DIR__ . '/config.php';
require_once BASE_PATH . '/auth.php';

$message = null;
$messageType = 'success';
$statusOptions = [
    'not_started' => [
        'label' => 'Not started',
        'badge' => 'bg-danger',
        'icon' => 'times-circle'
    ],
    'in_progress' => [
        'label' => 'In Progress',
        'badge' => 'bg-primary',
        'icon' => 'spinner'
    ],
    'completed' => [
        'label' => 'Completed',
        'badge' => 'bg-success',
        'icon' => 'check'
    ],
];
$legacyStatusMap = [
    'belum' => 'not_started',
    'sudah' => 'completed'
];

function normalizeStatus($status, $options, $legacyMap)
{
    if (isset($options[$status])) {
        return $status;
    }
    if (isset($legacyMap[$status])) {
        return $legacyMap[$status];
    }
    return 'not_started';
}
try {
    $conn = db();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['action']) && $_POST['action'] === 'add') {
            $note = trim($_POST['note'] ?? '');
            if ($note !== '') {
                $stmt = $conn->prepare('INSERT INTO maintenance_notes (note, status) VALUES (?, ?)');
                $status = 'not_started';
                $stmt->bind_param('ss', $note, $status);
                $stmt->execute();
                $message = 'Catatan berhasil ditambahkan';
            } else {
                $message = 'Catatan tidak boleh kosong';
                $messageType = 'danger';
            }
        } elseif (isset($_POST['action']) && $_POST['action'] === 'edit') {
            $id = (int)($_POST['id'] ?? 0);
            $newNote = trim($_POST['note'] ?? '');
            if ($id > 0 && $newNote !== '') {
                $stmt = $conn->prepare('UPDATE maintenance_notes SET note = ? WHERE id = ?');
                $stmt->bind_param('si', $newNote, $id);
                $stmt->execute();
                $message = 'Catatan berhasil diubah';
            } else {
                $message = 'Catatan tidak boleh kosong';
                $messageType = 'danger';
            }
        } elseif (isset($_POST['action']) && $_POST['action'] === 'update') {
            $id = (int)$_POST['id'];
            $requestedStatus = $_POST['status'] ?? 'not_started';
            $newStatus = normalizeStatus($requestedStatus, $statusOptions, $legacyStatusMap);
            $stmt = $conn->prepare('UPDATE maintenance_notes SET status = ? WHERE id = ?');
            $stmt->bind_param('si', $newStatus, $id);
            $stmt->execute();
            $message = 'Status catatan berhasil diperbarui';
        } elseif (isset($_POST['action']) && $_POST['action'] === 'delete') {
            $id = (int)$_POST['id'];
            $stmt = $conn->prepare('DELETE FROM maintenance_notes WHERE id = ?');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $message = 'Catatan berhasil dihapus';
        }
    }
    $notes = [];
    $result = $conn->query('SELECT id, note, status, created_at FROM maintenance_notes ORDER BY created_at DESC');
    while ($row = $result->fetch_assoc()) {
        $notes[] = $row;
    }
} catch (Exception $e) {
    $message = 'Error: ' . $e->getMessage();
    $messageType = 'danger';
}

include 'partials/header.php';
?>

<div class="page-header mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="page-title mb-0">
                <i class="fas fa-wrench me-2"></i>Catatan Teknis & Perbaikan
            </h1>
            <p class="page-subtitle mb-0">Kelola catatan pemeliharaan gedung</p>
        </div>
    </div>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
        <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?> me-2"></i>
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- Form Tambah Catatan -->
<div class="card mb-4">
    <div class="card-header">
        <h6 class="mb-0">
            <i class="fas fa-plus-circle me-2"></i>Tambah Catatan Baru
        </h6>
    </div>
    <div class="card-body">
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="mb-3">
                <label class="form-label" for="noteInput">
                    <i class="fas fa-pen me-1"></i>Deskripsi Catatan
                </label>
                <textarea 
                    id="noteInput"
                    name="note" 
                    class="form-control" 
                    rows="4" 
                    placeholder="Contoh: Perbaikan kabel listrik di lantai 3, ganti lampu LED di ruang meeting, inspeksi generator..."
                    required
                ></textarea>
                <small class="text-muted d-block mt-2">
                    <i class="fas fa-info-circle me-1"></i>Berikan deskripsi detail tentang pekerjaan perbaikan atau pemeliharaan
                </small>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-2"></i>Simpan Catatan
            </button>
        </form>
    </div>
</div>

<!-- Daftar Catatan -->
<div class="card">
    <div class="card-header">
        <h6 class="mb-0">
            <i class="fas fa-list me-2"></i>Daftar Catatan Perbaikan
        </h6>
        <small class="text-muted">Total: <strong><?php echo count($notes); ?></strong> catatan</small>
    </div>
    <div class="card-body">
        <?php if (count($notes) === 0): ?>
            <div class="text-center py-5">
                <i class="fas fa-clipboard fa-3x text-muted mb-3" style="opacity: 0.5;"></i>
                <p class="text-muted mb-0">Belum ada catatan. Mulai dengan menambahkan catatan baru di atas.</p>
            </div>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($notes as $note): ?>
                    <div class="col-md-6">
                        <div class="card border-0 bg-light">
                            <div class="card-body">
                                <?php
                                $statusKey = normalizeStatus($note['status'], $statusOptions, $legacyStatusMap);
                                $statusMeta = $statusOptions[$statusKey];
                                ?>
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="card-title mb-0">
                                        <i class="fas fa-sticky-note me-2"></i>Catatan
                                    </h6>
                                    <span class="badge <?php echo $statusMeta['badge']; ?>">
                                        <i class="fas fa-<?php echo $statusMeta['icon']; ?> me-1"></i>
                                        <?php echo htmlspecialchars($statusMeta['label']); ?>
                                    </span>
                                </div>
                                <p class="card-text mb-3"><?php echo htmlspecialchars($note['note']); ?></p>
                                <small class="text-muted d-block mb-3">
                                    <i class="fas fa-calendar-check me-1"></i>
                                    <?php echo date('d M Y H:i', strtotime($note['created_at'])); ?>
                                </small>
                                <div class="d-flex gap-2">
                                    <form method="POST" class="flex-grow-1">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="id" value="<?php echo $note['id']; ?>">
                                        <div class="input-group input-group-sm">
                                            <select name="status" class="form-select">
                                                <?php foreach ($statusOptions as $value => $option): ?>
                                                    <option value="<?php echo $value; ?>" <?php if ($statusKey === $value) echo 'selected'; ?>>
                                                        <?php echo htmlspecialchars($option['label']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="submit" class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-save me-1"></i>Update
                                            </button>
                                        </div>
                                    </form>
                                    <a class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" href="#editForm-<?php echo $note['id']; ?>" role="button">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" onsubmit="return confirm('Yakin hapus catatan ini?');" class="d-inline">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $note['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                                <div class="collapse mt-3" id="editForm-<?php echo $note['id']; ?>">
                                    <form method="POST">
                                        <input type="hidden" name="action" value="edit">
                                        <input type="hidden" name="id" value="<?php echo $note['id']; ?>">
                                        <div class="mb-2">
                                            <textarea name="note" class="form-control" rows="3" required><?php echo htmlspecialchars($note['note']); ?></textarea>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <button type="submit" class="btn btn-primary btn-sm">
                                                <i class="fas fa-save me-1"></i>Simpan
                                            </button>
                                            <button type="button" class="btn btn-light btn-sm" data-bs-toggle="collapse" data-bs-target="#editForm-<?php echo $note['id']; ?>">
                                                Batal
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'partials/footer.php'; ?>
