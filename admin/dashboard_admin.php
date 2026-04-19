<?php
// admin/dashboard_admin.php
require_once '../config/database.php';
require_once '../config/security.php';

// Pastikan user adalah admin
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Analitik sederhana
$total_rooms = $pdo->query("SELECT COUNT(*) FROM rooms")->fetchColumn();
$total_users = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();
$total_pending = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'pending'")->fetchColumn();
$total_approved = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'approved'")->fetchColumn();

// Ambil 5 booking terbaru yang perlu approval
$recent_bookings = $pdo->query("
    SELECT b.*, u.nama_lengkap, r.nama_ruangan 
    FROM bookings b 
    JOIN users u ON b.user_id = u.id 
    JOIN rooms r ON b.room_id = r.id 
    ORDER BY b.created_at DESC 
    LIMIT 5
")->fetchAll();

require_once '../includes/header.php';
?>

<h1 class="page-title">Dashboard Admin</h1>
<p class="page-subtitle">Ringkasan sistem dan statistik booking saat ini.</p>

<div class="grid grid-cols-3">
    <div class="stat-card">
        <div class="stat-icon">🎓</div>
        <div class="stat-details">
            <h3>Total Pengguna</h3>
            <p><?= $total_users ?></p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">🏢</div>
        <div class="stat-details">
            <h3>Total Ruangan</h3>
            <p><?= $total_rooms ?></p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon" style="background-color: #fef3c7; color: #d97706;">⏳</div>
        <div class="stat-details">
            <h3>Booking Menunggu Approval</h3>
            <p><?= $total_pending ?></p>
        </div>
    </div>
</div>

<div class="card" style="margin-top: 2rem;">
    <h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1rem;">Pengajuan Booking Terbaru</h2>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Pemohon</th>
                    <th>Ruangan</th>
                    <th>Tanggal</th>
                    <th>Waktu</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($recent_bookings) > 0): ?>
                    <?php foreach ($recent_bookings as $b): ?>
                        <tr>
                            <td><?= escape($b['nama_lengkap']) ?></td>
                            <td><?= escape($b['nama_ruangan']) ?></td>
                            <td><?= date('d M Y', strtotime($b['tanggal_booking'])) ?></td>
                            <td><?= date('H:i', strtotime($b['jam_mulai'])) ?> - <?= date('H:i', strtotime($b['jam_selesai'])) ?></td>
                            <td>
                                <?php
                                    $bClass = $b['status'] == 'pending' ? 'badge-pending' : ($b['status'] == 'approved' ? 'badge-approved' : 'badge-rejected');
                                    $bText = ucfirst($b['status']);
                                ?>
                                <span class="badge <?= $bClass ?>"><?= $bText ?></span>
                            </td>
                            <td>
                                <a href="manage_bookings.php" class="btn btn-sm btn-primary">Lihat Detail</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center;">Belum ada data booking.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
