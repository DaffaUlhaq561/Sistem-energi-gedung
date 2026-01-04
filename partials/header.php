<?php
if (function_exists('session_save_path')) {
    @session_save_path('/tmp');
}
if (!isset($currentUser)) { session_start(); $currentUser = $_SESSION['user'] ?? null; }
$apiBase = 'backend/api.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Sistem monitoring energi gedung modern dan intuitif">
    <title>Dashboard Energi Gedung</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body data-api-base="<?php echo htmlspecialchars($apiBase); ?>">
<nav class="navbar navbar-expand-lg navbar-dark mb-4">
  <div class="container-fluid px-4">
    <a class="navbar-brand fw-bold d-flex align-items-center" href="dashboard.php">
      <i class="fas fa-bolt me-2" style="font-size: 1.8rem;"></i>
      <span>Energy Gedung</span>
    </a>
    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link" href="dashboard.php">
            <i class="fas fa-chart-line me-2"></i>Dashboard
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="monitoring.php">
            <i class="fas fa-eye me-2"></i>Monitoring
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="laporan.php">
            <i class="fas fa-file-alt me-2"></i>Laporan
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="catatan.php">
            <i class="fas fa-wrench me-2"></i>Catatan
          </a>
        </li>
      </ul>
      <div class="d-flex align-items-center gap-3 ms-lg-3">
        <div class="navbar-text">
          <i class="fas fa-user-circle me-2"></i>
          <span><?php echo htmlspecialchars($currentUser['name'] ?? 'User'); ?></span>
        </div>
        <a href="logout.php" class="btn btn-sm btn-outline-light">
          <i class="fas fa-sign-out-alt me-1"></i>Logout
        </a>
      </div>
    </div>
  </div>
</nav>
<div class="container px-4 pb-5">
