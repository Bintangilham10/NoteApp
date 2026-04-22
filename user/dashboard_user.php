<?php
// user/dashboard_user.php
require_once '../config/database.php';
require_once '../config/security.php';

start_secure_session();
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
        <div class="stat-icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 28px; height: 28px;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5M9.75 15h.008v.008H9.75V15zm0-2.25h.008v.008H9.75v-.008zm-2.25 2.25h.008v.008H7.5V15zm0-2.25h.008v.008H7.5v-.008zm4.5 2.25h.008v.008h-.008V15zm0-2.25h.008v.008h-.008v-.008zm2.25 2.25h.008v.008h-.008V15zm0-2.25h.008v.008h-.008v-.008z" />
            </svg>
        </div>
        <div class="stat-details">
            <h3>Total Pengajuan Anda</h3>
            <p><?= $total_booking ?></p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon" style="background-color: #d1fae5; color: #059669;">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 28px; height: 28px;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
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
            <h3 style="color: var(--primary-color); margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 24px; height: 24px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z" />
                </svg>
                <?= escape($upcoming_booking['nama_ruangan']) ?>
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
