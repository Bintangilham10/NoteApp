<?php
// includes/header.php
require_once __DIR__ . '/../config/security.php';
start_secure_session();

// Menentukan base url secara dinamis (sederhana)
$dir_name = basename(dirname(__DIR__));
$base_url = "/" . $dir_name . "/";

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kamsis - Booking Ruangan Kampus</title>
    <!-- Menghubungkan CSS -->
    <link rel="stylesheet" href="<?= $base_url ?>assets/css/style.css">
</head>
<body>
    <!-- Ambient Background Globs -->
    <div class="floating-blob-1" style="position: fixed; z-index: -1;"></div>
    <div class="floating-blob-2" style="position: fixed; z-index: -1;"></div>

<?php
// Tampilkan navigasi hanya jika user sudah login
if (isset($_SESSION['user_id'])):
?>
<nav class="navbar">
    <div class="navbar-brand">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 28px; height: 28px; color: var(--primary-color);">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z" />
        </svg>
        <span class="gradient-text">Kamsis</span>
    </div>
    <div class="navbar-menu">
        <?php if ($_SESSION['role'] === 'admin'): ?>
            <a href="<?= $base_url ?>admin/dashboard_admin.php">Dashboard</a>
            <a href="<?= $base_url ?>admin/manage_rooms.php">Kelola Ruangan</a>
            <a href="<?= $base_url ?>admin/manage_bookings.php">Approval Booking</a>
        <?php else: ?>
            <a href="<?= $base_url ?>user/dashboard_user.php">Dashboard</a>
            <a href="<?= $base_url ?>user/rooms.php">Daftar Ruangan</a>
            <a href="<?= $base_url ?>user/my_bookings.php">Riwayat Booking</a>
        <?php endif; ?>
        
        <span style="color: var(--text-muted); margin-left: 1rem; border-left: 1px solid var(--border-color); padding-left: 1rem;">
            Halo, <?= htmlspecialchars($_SESSION['nama_lengkap']) ?>
        </span>
        <a href="<?= $base_url ?>auth/logout.php" class="btn-logout" onclick="return confirm('Yakin ingin keluar?');">Keluar</a>
    </div>
</nav>
<?php endif; ?>

<main class="container">
