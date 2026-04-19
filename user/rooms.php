<?php
// user/rooms.php
require_once '../config/database.php';
require_once '../config/security.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../auth/login.php");
    exit;
}

$rooms = $pdo->query("SELECT * FROM rooms ORDER BY nama_ruangan ASC")->fetchAll();

require_once '../includes/header.php';
?>

<div style="margin-bottom: 2rem;">
    <h1 class="page-title">Daftar Ruangan</h1>
    <p class="text-muted">Pilih ruangan yang sesuai dengan kebutuhan dan kapasitas acara Anda.</p>
</div>

<div class="grid grid-cols-3">
    <?php if (count($rooms) > 0): ?>
        <?php foreach ($rooms as $r): ?>
            <div class="card room-card">
                <!-- Simulasi gambar ruangan, bisa disesuaikan dengan folder upload -->
                <div class="room-image" style="display: flex; align-items: center; justify-content: center; background-color: var(--border-color); font-size: 3rem; color: var(--text-muted);">
                    <?php if (!empty($r['foto'])): ?>
                        <img src="../assets/images/<?= escape($r['foto']) ?>" alt="<?= escape($r['nama_ruangan']) ?>" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.style.display='none'; this.parentElement.innerText='🏢';">
                    <?php else: ?>
                        🏢
                    <?php endif; ?>
                </div>
                
                <div class="room-content">
                    <h3 class="room-title"><?= escape($r['nama_ruangan']) ?></h3>
                    
                    <div class="room-meta">
                        <span style="display: block; margin-bottom: 5px;">👥 Kapasitas: <b><?= $r['kapasitas'] ?> Orang</b></span>
                        <span>✨ Fasilitas: <br><?= escape($r['fasilitas']) ?></span>
                    </div>
                    
                    <div class="room-actions">
                        <a href="booking.php?room_id=<?= $r['id'] ?>" class="btn btn-primary btn-block">Pesan Ruangan Ini</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="card" style="grid-column: span 3; text-align: center;">
            <p>Belum ada ruangan yang tersedia di sistem.</p>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
