<?php
// auth/login.php
require_once '../config/database.php';
require_once '../config/security.php';
start_secure_session();

// Redirect jika sudah login
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: ../admin/dashboard_admin.php");
    } else {
        header("Location: ../user/dashboard_user.php");
    }
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nim_nip = trim($_POST['nim_nip'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validasi input untuk mencegah eksekusi berlebih
    // Menolak input jika lebih dari panjang kolom max db
    if (strlen($nim_nip) > 50 || strlen($password) > 255) {
        $error = "Panjang karakter tidak valid.";
    } elseif (empty($nim_nip) || empty($password)) {
        $error = "NIM/NIP dan Password harus diisi.";
    } else {
        // Cek ke DB
        try {
            $stmt = $pdo->prepare("SELECT id, nama_lengkap, password, role FROM users WHERE nim_nip = :nim_nip");
            $stmt->execute(['nim_nip' => $nim_nip]);
            $user = $stmt->fetch();

            // Verifikasi Pwd Hash BCRYPT
            if ($user && password_verify($password, $user['password'])) {
                // Regenerasi session id agar aman (Session Fixation protection)
                session_regenerate_id(true);

                // Set data ke session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
                $_SESSION['role'] = $user['role'];

                // Redirect sesuai role
                if ($user['role'] === 'admin') {
                    header("Location: ../admin/dashboard_admin.php");
                } else {
                    header("Location: ../user/dashboard_user.php");
                }
                exit;
            } else {
                $error = "NIM/NIP atau Password salah.";
            }
        } catch (PDOException $e) {
            $error = "Terjadi kesalahan pada server.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - Sistem Booking Ruangan Kampus</title>
    <!-- Base URL fallback jika tanpa header global -->
    <?php 
        $dir_name = basename(dirname(__DIR__));
        $base_url = "/" . $dir_name . "/"; 
    ?>
    <link rel="stylesheet" href="<?= $base_url ?>assets/css/style.css">
</head>
<body>
    <div class="auth-wrapper">
        <div class="floating-blob-1"></div>
        <div class="floating-blob-2"></div>
        <div class="auth-card">
            <div class="auth-header">
                <h1 class="gradient-text" style="font-family: 'Outfit', sans-serif;">Kamsis</h1>
                <p class="text-muted">Sistem Booking Ruangan Kampus</p>
            </div>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= escape($error) ?></div>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?= escape($_SESSION['success']) ?></div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label">NIM / NIP</label>
                    <input type="text" name="nim_nip" class="form-control" required placeholder="Masukkan NIM atau NIP Anda" autocomplete="off">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required placeholder="••••••••">
                </div>
                
                <button type="submit" class="btn btn-primary btn-block" style="margin-top: 1rem; padding: 0.75rem;">Masuk</button>
            </form>
            
            <p style="text-align: center; margin-top: 1.5rem; font-size: 0.875rem;">
                Belum punya akun? <a href="register.php">Daftar sekarang</a>
            </p>
            <p style="text-align: center; margin-top: 0.5rem; font-size: 0.75rem;">
                <a href="../index.php">Kembali ke Beranda</a>
            </p>
        </div>
    </div>
</body>
</html>
