<?php
// user/rooms.php
require_once '../config/database.php';
require_once '../config/security.php';

start_secure_session();
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
                        <img src="../assets/images/<?= escape($r['foto']) ?>" alt="<?= escape($r['nama_ruangan']) ?>" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.style.display='none'; this.parentElement.innerHTML='<svg xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' viewBox=\'0 0 24 24\' stroke-width=\'1.5\' stroke=\'currentColor\' style=\'width: 48px; height: 48px; color: var(--primary-color);\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z\' /></svg>';">
                    <?php else: ?>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 48px; height: 48px; color: var(--primary-color);">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z" />
                        </svg>
                    <?php endif; ?>
                </div>
                
                <div class="room-content">
                    <h3 class="room-title"><?= escape($r['nama_ruangan']) ?></h3>
                    
                    <div class="room-meta">
                        <span style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 5px;">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 18px; height: 18px;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                            </svg>
                            Kapasitas: <b><?= $r['kapasitas'] ?> Orang</b>
                        </span>
                        <span style="display: flex; align-items: flex-start; gap: 0.5rem;">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 18px; height: 18px; margin-top: 3px;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456z" />
                            </svg>
                            <span>Fasilitas: <br><?= escape($r['fasilitas']) ?></span>
                        </span>
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
