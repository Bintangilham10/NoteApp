<?php
// index.php
require_once 'config/security.php';
start_secure_session();

// Redirect otomatis sesuai role jika sudah login
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin/dashboard_admin.php");
    } else {
        header("Location: user/dashboard_user.php");
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selamat Datang - Kamsis</title>
    <!-- Karena di root, tidak perlu prefix base_url yang rumit -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="auth-wrapper" style="background-color: var(--card-bg);">
        <div class="container text-center" style="max-width: 800px; text-align: center;">
            <div style="font-size: 4rem; margin-bottom: 1rem;">🏢</div>
            <h1 class="page-title" style="font-size: 3rem; margin-bottom: 1.5rem; color: var(--primary-color);">Kamsis</h1>
            <p class="page-subtitle" style="font-size: 1.25rem;">
                Platform resmi reservasi ruang perkuliahan, ruang rapat, dan laboratorium kampus. 
                Pesan ruangan dengan cepat, aman, dan tanpa bentrok jadwal.
            </p>
            
            <div style="display: flex; gap: 1rem; justify-content: center; margin-top: 2rem;">
                <a href="auth/login.php" class="btn btn-primary" style="padding: 1rem 2rem; font-size: 1.125rem;">Masuk ke Akun</a>
                <a href="auth/register.php" class="btn" style="padding: 1rem 2rem; font-size: 1.125rem; border: 1px solid var(--border-color);">Daftar Akun Baru</a>
            </div>
        </div>
    </div>
</body>
</html>
