<?php
// auth/register.php
require_once '../config/database.php';
require_once '../config/security.php';
start_secure_session();

// Redirect jika sudah login
if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
    $nim_nip = trim($_POST['nim_nip'] ?? '');
    $password = $_POST['password'] ?? '';
    $konfirmasi_password = $_POST['konfirmasi_password'] ?? '';

    // Validasi panjang input mencegah serangan buffer overflow basic/kerusakan DB
    if (strlen($nama_lengkap) > 100 || strlen($nim_nip) > 50 || strlen($password) > 255) {
        $error = "Gagal. Panjang input melebihi batas maksimal.";
    } elseif (empty($nama_lengkap) || empty($nim_nip) || empty($password)) {
        $error = "Semua kolom wajib diisi.";
    } elseif ($password !== $konfirmasi_password) {
        $error = "Konfirmasi password tidak cocok.";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter.";
    } else {
        // Cek jika NIM/NIP sudah terdaftar
        $stmt_check = $pdo->prepare("SELECT id FROM users WHERE nim_nip = :nim_nip");
        $stmt_check->execute(['nim_nip' => $nim_nip]);
        
        if ($stmt_check->fetch()) {
            $error = "NIM/NIP '$nim_nip' sudah terdaftar dalam sistem.";
        } else {
            // Hashing password dengan BCRYPT yang sudah termasuk salting otomatis dari PHP
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $role = 'user'; // Secara default semua register akan masuk ke role 'user'

            try {
                $stmt = $pdo->prepare("INSERT INTO users (nama_lengkap, nim_nip, password, role) VALUES (:nama_lengkap, :nim_nip, :password, :role)");
                $berhasil = $stmt->execute([
                    'nama_lengkap' => $nama_lengkap,
                    'nim_nip' => $nim_nip,
                    'password' => $hashed_password,
                    'role' => $role
                ]);

                if ($berhasil) {
                    $_SESSION['success'] = "Pendaftaran berhasil, silakan Masuk dengan akun baru Anda.";
                    header("Location: login.php");
                    exit;
                } else {
                    $error = "Pendaftaran gagal akibat kesalahan teknis.";
                }
            } catch (PDOException $e) {
                // PDO Exception bisa ter-trigger kalau misal concurrent request pada UNIQUE index
                $error = "Terjadi kesalahan pada query atau database.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - Sistem Booking Ruangan Kampus</title>
    <?php 
        $dir_name = basename(dirname(__DIR__));
        $base_url = "/" . $dir_name . "/"; 
    ?>
    <link rel="stylesheet" href="<?= $base_url ?>assets/css/style.css">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-header">
                <h1>Daftar Akun</h1>
                <p class="text-muted">Lengkapi data Anda di bawah ini</p>
            </div>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= escape($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label">Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" class="form-control" required placeholder="Contoh: Budi Santoso" value="<?= escape($_POST['nama_lengkap'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">NIM / NIP</label>
                    <input type="text" name="nim_nip" class="form-control" required placeholder="Masukkan nomor identitas" value="<?= escape($_POST['nim_nip'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required placeholder="Minimal 6 karakter">
                </div>

                <div class="form-group">
                    <label class="form-label">Konfirmasi Password</label>
                    <input type="password" name="konfirmasi_password" class="form-control" required placeholder="Ulangi password">
                </div>
                
                <button type="submit" class="btn btn-success btn-block" style="margin-top: 1rem; padding: 0.75rem;">Mendaftar</button>
            </form>
            
            <p style="text-align: center; margin-top: 1.5rem; font-size: 0.875rem;">
                Sudah punya akun? <a href="login.php">Masuk di sini</a>
            </p>
        </div>
    </div>
</body>
</html>
