<?php
// user/booking.php
require_once '../config/database.php';
require_once '../config/security.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../auth/login.php");
    exit;
}

$error = '';
$success = '';
$room_id_selected = $_GET['room_id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room_id = (int) $_POST['room_id'];
    $tanggal = $_POST['tanggal_booking'];
    $jam_mulai = $_POST['jam_mulai'];
    $jam_selesai = $_POST['jam_selesai'];
    $keperluan = trim($_POST['keperluan']);
    $user_id = $_SESSION['user_id'];

    if (empty($room_id) || empty($tanggal) || empty($jam_mulai) || empty($jam_selesai) || empty($keperluan)) {
        $error = "Semua field wajib diisi.";
    } elseif (strtotime($jam_selesai) <= strtotime($jam_mulai)) {
        $error = "Jam selesai harus setelah jam mulai.";
    } else {
        // Cek clash (Bentrok Jadwal) pada ruangan dan tanggal yang sama
        // Rumus sederhana overlap: (StartA < EndB) AND (EndA > StartB)
        $stmt_clash = $pdo->prepare("
            SELECT id FROM bookings 
            WHERE room_id = :room_id 
              AND tanggal_booking = :tanggal 
              AND status IN ('pending', 'approved') 
              AND (jam_mulai < :jam_selesai AND jam_selesai > :jam_mulai)
        ");
        
        $stmt_clash->execute([
            'room_id' => $room_id,
            'tanggal' => $tanggal,
            'jam_mulai' => $jam_mulai,
            'jam_selesai' => $jam_selesai
        ]);

        if ($stmt_clash->fetch()) {
            $error = "Periode jadwal penuh/bentrok dengan booking lain (Pending/Approved). Harap pilih jam atau hari lain.";
        } else {
            // Insert Booking
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO bookings (user_id, room_id, tanggal_booking, jam_mulai, jam_selesai, keperluan, status) 
                    VALUES (?, ?, ?, ?, ?, ?, 'pending')
                ");
                $stmt->execute([$user_id, $room_id, $tanggal, $jam_mulai, $jam_selesai, $keperluan]);
                
                $_SESSION['success_msg'] = "Pengajuan booking berhasil dibuat dan sedang menunggu approval.";
                header("Location: my_bookings.php");
                exit;
            } catch (PDOException $e) {
                $error = "Terjadi kesalahan pada sistem saat menyimpan data.";
            }
        }
    }
}

// Ambil daftar ruangan untuk form dropdown
$rooms = $pdo->query("SELECT id, nama_ruangan, kapasitas FROM rooms ORDER BY nama_ruangan ASC")->fetchAll();

require_once '../includes/header.php';
?>

<div style="margin-bottom: 2rem;">
    <h1 class="page-title">Form Booking Ruangan</h1>
    <p class="text-muted">Isi formulir ini untuk mengajukan peminjaman ruangan. Sistem secara otomatis mengecek ketersediaan jam.</p>
</div>

<div class="card" style="max-width: 600px; margin: 0 auto;">
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= escape($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="booking.php">
        <div class="form-group">
            <label class="form-label">Pilih Ruangan</label>
            <select name="room_id" class="form-control" required>
                <option value="">-- Pilih Ruangan --</option>
                <?php foreach ($rooms as $r): ?>
                    <option value="<?= $r['id'] ?>" <?= ($r['id'] == $room_id_selected) ? 'selected' : '' ?>>
                        <?= escape($r['nama_ruangan']) ?> (Kapasitas: <?= $r['kapasitas'] ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">Tanggal Booking</label>
            <input type="date" name="tanggal_booking" class="form-control" required min="<?= date('Y-m-d') ?>">
        </div>

        <div class="grid grid-cols-2" style="gap: 1rem;">
            <div class="form-group">
                <label class="form-label">Jam Mulai</label>
                <input type="time" name="jam_mulai" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">Jam Selesai</label>
                <input type="time" name="jam_selesai" class="form-control" required>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Keperluan</label>
            <textarea name="keperluan" class="form-control" required placeholder="Jelaskan untuk agenda apa ruangan ini digunakan..."></textarea>
        </div>

        <button type="submit" class="btn btn-primary" style="margin-top: 1rem;">Ajukan Booking</button>
        <a href="rooms.php" class="btn" style="border: 1px solid var(--border-color); margin-top: 1rem; margin-left: 0.5rem;">Batal</a>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>
