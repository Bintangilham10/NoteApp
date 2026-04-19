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
    <div class="auth-wrapper">
        <div class="landing-hero text-center" style="max-width: 800px; margin: 0 auto;">
            <div class="landing-icon" style="color: var(--primary-color);">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 80px; height: 80px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z" />
                </svg>
            </div>
            <h1 class="page-title" style="font-size: 3.5rem; margin-bottom: 1rem; background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Kamsis</h1>
            <p class="page-subtitle" style="font-size: 1.25rem;">
                Platform resmi reservasi ruang perkuliahan, ruang rapat, dan laboratorium kampus. <br>Pesan ruangan dengan cepat, aman, dan tanpa bentrok jadwal.
            </p>
            
            <div style="display: flex; gap: 1rem; justify-content: center; margin-top: 2rem;">
                <a href="auth/login.php" class="btn btn-primary" style="padding: 1rem 2rem; font-size: 1.125rem;">Masuk ke Akun</a>
                <a href="auth/register.php" class="btn" style="padding: 1rem 2rem; font-size: 1.125rem; border: 1px solid var(--border-color);">Daftar Akun Baru</a>
            </div>
        </div>
    </div>
</body>
</html>
