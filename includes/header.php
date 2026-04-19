<?php
// includes/header.php
// Pastikan session sudah menyala (bisa diakses via auth atau tiap halaman)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

<?php
// Tampilkan navigasi hanya jika user sudah login
if (isset($_SESSION['user_id'])):
?>
<nav class="navbar">
    <div class="navbar-brand">
        <!-- Ikon sederhana menggunakan karakter Unicode atau teks biasa -->
        🏢 Kamsis
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
