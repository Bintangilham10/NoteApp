<?php
// admin/manage_bookings.php
require_once '../config/database.php';
require_once '../config/security.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$error = '';
$success = '';

// Proses approval / rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = $_POST['booking_id'] ?? 0;
    $status = $_POST['status'] ?? ''; // 'approved' atau 'rejected'

    if ($booking_id > 0 && in_array($status, ['approved', 'rejected'])) {
        try {
            $stmt = $pdo->prepare("UPDATE bookings SET status = :status WHERE id = :id");
            $stmt->execute(['status' => $status, 'id' => $booking_id]);
            $_SESSION['success_msg'] = "Status booking berhasil diubah menjadi " . ucfirst($status) . ".";
            header("Location: manage_bookings.php");
            exit;
        } catch (PDOException $e) {
            $error = "Terjadi kesalahan sistem saat memperbarui status.";
        }
    } else {
        $error = "Request tidak valid.";
    }
}

if (isset($_SESSION['success_msg'])) {
    $success = $_SESSION['success_msg'];
    unset($_SESSION['success_msg']);
}

// Ambil semua data booking
$bookings = $pdo->query("
    SELECT b.*, u.nama_lengkap, u.nim_nip, r.nama_ruangan 
    FROM bookings b 
    JOIN users u ON b.user_id = u.id 
    JOIN rooms r ON b.room_id = r.id 
    ORDER BY b.created_at DESC
")->fetchAll();

require_once '../includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h1 class="page-title">Approval Booking Ruangan</h1>
        <p class="text-muted">Tinjau, setujui, atau tolak permohonan booking ruangan dari mahasiswa.</p>
    </div>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= escape($error) ?></div>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= escape($success) ?></div>
<?php endif; ?>

<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Pemohon</th>
                    <th>Ruangan</th>
                    <th>Waktu Pengajuan</th>
                    <th>Keperluan</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($bookings) > 0): ?>
                    <?php foreach ($bookings as $b): ?>
                        <tr>
                            <td>#<?= $b['id'] ?></td>
                            <td><?= escape($b['nama_lengkap']) ?><br><small class="text-muted"><?= escape($b['nim_nip']) ?></small></td>
                            <td><?= escape($b['nama_ruangan']) ?></td>
                            <td>
                                <b><?= date('d M Y', strtotime($b['tanggal_booking'])) ?></b><br>
                                <?= date('H:i', strtotime($b['jam_mulai'])) ?> - <?= date('H:i', strtotime($b['jam_selesai'])) ?>
                            </td>
                            <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis;">
                                <?= escape($b['keperluan']) ?>
                            </td>
                            <td>
                                <?php
                                    $bClass = $b['status'] == 'pending' ? 'badge-pending' : ($b['status'] == 'approved' ? 'badge-approved' : 'badge-rejected');
                                    $bText = ucfirst($b['status']);
                                ?>
                                <span class="badge <?= $bClass ?>"><?= $bText ?></span>
                            </td>
                            <td>
                                <?php if ($b['status'] === 'pending'): ?>
                                    <form method="POST" style="display: inline-block;">
                                        <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                                        <input type="hidden" name="status" value="approved">
                                        <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Setujui booking ini?');">Approve</button>
                                    </form>
                                    <form method="POST" style="display: inline-block;">
                                        <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                                        <input type="hidden" name="status" value="rejected">
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Tolak booking ini?');">Reject</button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-muted" style="font-size: 0.875rem;">Selesai diulas</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7" style="text-align:center;">Belum ada riwayat booking.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
