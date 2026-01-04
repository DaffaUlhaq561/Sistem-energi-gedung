<?php
if (function_exists('session_save_path')) {
    @session_save_path('/tmp');
}
session_start();
require_once __DIR__ . '/config.php';

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    try {
        if ($name === '' || $username === '' || $password === '') {
            throw new Exception('Semua field wajib diisi');
        }
        $conn = db();
        $stmt = $conn->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->fetch_assoc()) {
            throw new Exception('Username sudah dipakai');
        }
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $conn->prepare('INSERT INTO users (username, password_hash, name) VALUES (?, ?, ?)');
        $stmt->bind_param('sss', $username, $hash, $name);
        $stmt->execute();
        $_SESSION['flash_success'] = 'Registrasi berhasil, silakan login.';
        header('Location: login.php');
        exit;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Daftar akun baru di sistem monitoring energi gedung">
    <title>Registrasi - EnergiGedung</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/style.css">
    <style>
        body{
            background-image: url('assets/gedung2.jpg');
            background-size: cover;
            background-position: center;
        }

        .register-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .register-container::before {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 50%;
            top: -100px;
            left: -100px;
            animation: float 6s ease-in-out infinite;
        }

        .register-container::after {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.06);
            border-radius: 50%;
            bottom: -50px;
            right: -50px;
            animation: float 8s ease-in-out infinite reverse;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(20px); }
        }

        .register-card {
            position: relative;
            z-index: 10;
            max-width: 450px;
            width: 100%;
            border: none;
            border-radius: 12px;
            box-shadow: 0 12px 40px rgba(0,0,0,0.35);
            overflow: hidden;
            background: #ffffff;
        }

        .register-header {
            background: transparent;
            color: #1f2937;
            padding: 2.25rem 1.25rem 1.25rem;
            text-align: center;
        }

        .register-header i {
            font-size: 2.25rem;
            margin-bottom: 0.6rem;
            display: block;
            color: #6c757d;
        }

        .register-header h2 {
            font-size: 1.6rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
            color: #111827;
        }

        .register-header p {
            font-size: 0.9rem;
            opacity: 0.9;
            margin: 0;
        }

        .register-body {
            padding: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-group label {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
        }

        .form-group label i {
            margin-right: 0.5rem;
            color: #6c757d;
        }

        .form-group input {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 0.65rem 0.9rem;
            font-size: 0.95rem;
            transition: all 0.18s ease;
        }

        .form-group input:focus {
            border-color: rgba(13,110,253,0.9);
            box-shadow: 0 0 0 4px rgba(13,110,253,0.06);
            outline: none;
        }

        .btn-register {
            background: linear-gradient(90deg, #0d6efd 0%, #3d8bfd 100%);
            border: none;
            color: #fff;
            font-weight: 600;
            padding: 0.875rem;
            border-radius: 8px;
            width: 100%;
            transition: all 0.18s ease;
            font-size: 1rem;
        }

        .btn-register:hover {
            box-shadow: 0 8px 20px rgba(13,110,253,0.18);
            transform: translateY(-2px);
            color: #fff;
        }

        .register-footer {
            padding: 1.5rem;
            text-align: center;
            border-top: 1px solid #f0f0f0;
            background: #fafafa;
        }

        .register-footer p {
            margin: 0;
            font-size: 0.9rem;
            color: #666;
        }

        .register-footer a {
            color: #f5576c;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .register-footer a:hover {
            color: #f093fb;
            text-decoration: underline;
        }

        .alert {
            border: none;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
<div class="register-container">
    <div class="register-card">
        <div class="register-header">
            <i class="fas fa-user-plus"></i>
            <h2>Energi Gedung</h2>
            <p>Buat Akun Baru</p>
        </div>
        
        <div class="register-body">
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="register.php">
                <div class="form-group">
                    <label for="name">
                        <i class="fas fa-id-card"></i>Nama Lengkap
                    </label>
                    <input 
                        type="text" 
                        id="name"
                        name="name" 
                        class="form-control" 
                        placeholder="Masukkan nama lengkap Anda"
                        required
                        autofocus
                    >
                </div>

                <div class="form-group">
                    <label for="username">
                        <i class="fas fa-user"></i>Username
                    </label>
                    <input 
                        type="text" 
                        id="username"
                        name="username" 
                        class="form-control" 
                        placeholder="Pilih username yang mudah diingat"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i>Password
                    </label>
                    <input 
                        type="password" 
                        id="password"
                        name="password" 
                        class="form-control" 
                        placeholder="Buat password yang kuat"
                        required
                    >
                </div>

                <button type="submit" class="btn-register">
                    <i class="fas fa-user-check me-2"></i>Daftar
                </button>
            </form>
        </div>

        <div class="register-footer">
            <p>Sudah punya akun? <a href="login.php">Login di sini</a></p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
