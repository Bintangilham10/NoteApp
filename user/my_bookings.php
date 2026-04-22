<?php
// user/my_bookings.php
require_once '../config/database.php';
require_once '../config/security.php';

start_secure_session();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$success = '';

if (isset($_SESSION['success_msg'])) {
    $success = $_SESSION['success_msg'];
    unset($_SESSION['success_msg']);
}

// Ambil riwayat booking user
$bookings = $pdo->prepare("
    SELECT b.*, r.nama_ruangan 
    FROM bookings b 
    JOIN rooms r ON b.room_id = r.id 
    WHERE b.user_id = ? 
    ORDER BY b.created_at DESC
");
$bookings->execute([$user_id]);
$bookings = $bookings->fetchAll();

require_once '../includes/header.php';
?>

<div style="margin-bottom: 2rem;">
    <h1 class="page-title">Riwayat Booking</h1>
    <p class="text-muted">Pantau status persetujuan dari pengajuan ruangan yang Anda buat.</p>
</div>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= escape($success) ?></div>
<?php endif; ?>

<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Ruangan</th>
                    <th>Tanggal Booking</th>
                    <th>Waktu</th>
                    <th>Keperluan</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($bookings) > 0): ?>
                    <?php foreach ($bookings as $b): ?>
                        <tr>
                            <td>#<?= $b['id'] ?></td>
                            <td><?= escape($b['nama_ruangan']) ?></td>
                            <td><?= date('d M Y', strtotime($b['tanggal_booking'])) ?></td>
                            <td><?= date('H:i', strtotime($b['jam_mulai'])) ?> - <?= date('H:i', strtotime($b['jam_selesai'])) ?></td>
                            <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?= escape($b['keperluan']) ?>">
                                <?= escape($b['keperluan']) ?>
                            </td>
                            <td>
                                <?php
                                    $bClass = $b['status'] == 'pending' ? 'badge-pending' : ($b['status'] == 'approved' ? 'badge-approved' : 'badge-rejected');
                                    $bText = ucfirst($b['status']);
                                ?>
                                <span class="badge <?= $bClass ?>"><?= $bText ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" style="text-align:center;">Anda belum pernah mengajukan booking.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
