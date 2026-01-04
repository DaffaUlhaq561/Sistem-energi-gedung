<?php
if (function_exists('session_save_path')) {
    @session_save_path('/tmp');
}
session_start();
require_once __DIR__ . '/config.php';

$error = null;
$success = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_success']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    try {
        $conn = db();
        $stmt = $conn->prepare('SELECT id, username, password_hash, name FROM users WHERE username = ? LIMIT 1');
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            if (password_verify($password, $row['password_hash'])) {
                $_SESSION['user'] = [
                    'id' => $row['id'],
                    'username' => $row['username'],
                    'name' => $row['name'],
                ];
                header('Location: dashboard.php');
                exit;
            }
        }
        $error = 'Username atau password salah';
    } catch (Exception $e) {
        $error = 'Gagal login: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Login ke sistem monitoring energi gedung">
    <title>Login - EnergyGedung</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/style.css">
    <style>
        body{
            background-image: url('assets/gedung2.jpg');
            background-size: cover;
            background-position: center;
        }

        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            /* background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); */
            position: relative;
            overflow: hidden;
        }

        .login-container::before {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            top: -100px;
            left: -100px;
            animation: float 6s ease-in-out infinite;
        }

        .login-container::after {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            bottom: -50px;
            right: -50px;
            animation: float 8s ease-in-out infinite reverse;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(20px); }
        }

        .login-card {
            position: relative;
            z-index: 10;
            max-width: 420px;
            width: 100%;
            border: none;
            border-radius: 12px;
            box-shadow: 0 12px 40px rgba(0,0,0,0.35);
            overflow: hidden;
            background: #ffffff;
        }

        .login-header {
            background: transparent;
            color: #1f2937;
            padding: 2.25rem 1.25rem 1.25rem;
            text-align: center;
        }

        .login-header i {
            font-size: 2.25rem;
            margin-bottom: 0.6rem;
            display: block;
            color: #6c757d;
        }

        .login-header h2 {
            font-size: 1.6rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
            color: #111827;
        }

        .login-header p {
            font-size: 0.9rem;
            opacity: 0.9;
            margin: 0;
        }

        .login-body {
            padding: 2rem 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
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
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            border-color: rgba(13,110,253,0.9);
            box-shadow: 0 0 0 4px rgba(13,110,253,0.06);
            outline: none;
        }

        .btn-login {
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

        .btn-login:hover {
            box-shadow: 0 8px 20px rgba(13,110,253,0.18);
            transform: translateY(-2px);
            color: #fff;
        }

        .login-footer {
            padding: 1.5rem;
            text-align: center;
            border-top: 1px solid #f0f0f0;
            background: #fafafa;
        }

        .login-footer p {
            margin: 0;
            font-size: 0.9rem;
            color: #666;
        }

        .login-footer a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .login-footer a:hover {
            color: #764ba2;
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
<div class="login-container">
    <div class="login-card">
        <div class="login-header">
            <i class="fas fa-bolt"></i>
            <h2>Energi Gedung</h2>
            <p>Monitoring Energi Gedung</p>
        </div>
        
        <div class="login-body">
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php">
                <div class="form-group">
                    <label for="username">
                        <i class="fas fa-user"></i>Username
                    </label>
                    <input 
                        type="text" 
                        id="username"
                        name="username" 
                        class="form-control" 
                        placeholder="Masukkan username Anda"
                        required
                        autofocus
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
                        placeholder="Masukkan password Anda"
                        required
                    >
                </div>

                <button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt me-2"></i>Masuk
                </button>
            </form>
        </div>

        <div class="login-footer">
            <p>Belum punya akun? <a href="register.php">Daftar di sini</a></p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
