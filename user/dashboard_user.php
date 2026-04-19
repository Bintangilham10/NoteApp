<?php
// user/dashboard_user.php
require_once '../config/database.php';
require_once '../config/security.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Statistik user sendiri
$total_booking = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE user_id = ?");
$total_booking->execute([$user_id]);
$total_booking = $total_booking->fetchColumn();

$approved_booking = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE user_id = ? AND status = 'approved'");
$approved_booking->execute([$user_id]);
$approved_booking = $approved_booking->fetchColumn();

// Booking terdekat (Upcoming)
$upcoming = $pdo->prepare("
    SELECT b.*, r.nama_ruangan 
    FROM bookings b 
    JOIN rooms r ON b.room_id = r.id 
    WHERE b.user_id = ? AND b.status = 'approved' AND b.tanggal_booking >= CURDATE()
    ORDER BY b.tanggal_booking ASC, b.jam_mulai ASC
    LIMIT 1
");
$upcoming->execute([$user_id]);
$upcoming_booking = $upcoming->fetch();

require_once '../includes/header.php';
?>

<h1 class="page-title">Selamat datang, <?= escape($_SESSION['nama_lengkap']) ?>!</h1>
<p class="page-subtitle">Pesan ruangan kampus untuk kegiatan akademik dan organisasi dengan mudah.</p>

<div class="grid grid-cols-3">
    <div class="stat-card">
        <div class="stat-icon">📅</div>
        <div class="stat-details">
            <h3>Total Pengajuan Anda</h3>
            <p><?= $total_booking ?></p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon" style="background-color: #d1fae5; color: #059669;">✅</div>
        <div class="stat-details">
            <h3>Disetujui</h3>
            <p><?= $approved_booking ?></p>
        </div>
    </div>

    <!-- Pintasan -->
    <div class="stat-card" style="background-color: var(--primary-color); color: white; border: none; align-items: start; flex-direction: column; justify-content: center; position: relative; overflow: hidden;">
        <h3 style="color: white; margin-bottom: 0.5rem; font-size: 1rem;">Butuh Ruangan Sekarang?</h3>
        <a href="rooms.php" class="btn btn-sm" style="background-color: white; color: var(--primary-color);">Lihat Ketersediaan</a>
    </div>
</div>

<div class="card" style="margin-top: 2rem;">
    <h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1rem;">Jadwal Terdekat Anda</h2>
    
    <?php if ($upcoming_booking): ?>
        <div style="padding: 1rem; border: 1px solid #bfdbfe; background-color: #eff6ff; border-radius: var(--border-radius);">
            <h3 style="color: var(--primary-color); margin-bottom: 0.5rem;">
                🏢 <?= escape($upcoming_booking['nama_ruangan']) ?>
            </h3>
            <p style="margin-bottom: 0.25rem;"><b>Tanggal:</b> <?= date('d M Y', strtotime($upcoming_booking['tanggal_booking'])) ?></p>
            <p style="margin-bottom: 0.25rem;"><b>Waktu:</b> <?= date('H:i', strtotime($upcoming_booking['jam_mulai'])) ?> - <?= date('H:i', strtotime($upcoming_booking['jam_selesai'])) ?> WIB</p>
            <p style="margin-bottom: 0.25rem;"><b>Keperluan:</b> <?= escape($upcoming_booking['keperluan']) ?></p>
        </div>
    <?php else: ?>
        <p class="text-muted text-center">Belum ada jadwal yang akan datang.</p>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
