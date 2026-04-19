<?php
// admin/manage_rooms.php
require_once '../config/database.php';
require_once '../config/security.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$action = $_GET['action'] ?? 'list';
$error = '';
$success = '';

// Proses Delete
if ($action === 'delete') {
    $id = $_GET['id'] ?? 0;
    try {
        $stmt = $pdo->prepare("DELETE FROM rooms WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $_SESSION['success_msg'] = "Ruangan berhasil dihapus.";
        header("Location: manage_rooms.php");
        exit;
    } catch (PDOException $e) {
        $error = "Gagal menghapus ruangan karena masih ada data booking terkait.";
    }
}

// Proses Create / Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_ruangan = trim($_POST['nama_ruangan']);
    $kapasitas = (int) $_POST['kapasitas'];
    $fasilitas = trim($_POST['fasilitas']);
    $foto = trim($_POST['foto']); // Hanya menampung nama file/URL mockup sementara
    $id = $_POST['id'] ?? 0;

    if (empty($nama_ruangan) || $kapasitas <= 0 || empty($fasilitas)) {
        $error = "Nama ruangan, fasilitas, dan kapasitas (di atas 0) wajib diisi.";
    } else {
        try {
            if ($id > 0) {
                // Update
                $stmt = $pdo->prepare("UPDATE rooms SET nama_ruangan=?, kapasitas=?, fasilitas=?, foto=? WHERE id=?");
                $stmt->execute([$nama_ruangan, $kapasitas, $fasilitas, $foto, $id]);
                $_SESSION['success_msg'] = "Data ruangan diperbarui.";
            } else {
                // Insert
                $stmt = $pdo->prepare("INSERT INTO rooms (nama_ruangan, kapasitas, fasilitas, foto) VALUES (?, ?, ?, ?)");
                $stmt->execute([$nama_ruangan, $kapasitas, $fasilitas, $foto]);
                $_SESSION['success_msg'] = "Ruangan baru berhasil ditambahkan.";
            }
            header("Location: manage_rooms.php");
            exit;
        } catch (PDOException $e) {
            $error = "Terjadi kesalahan sistem saat menyimpan ke database.";
        }
    }
}

if (isset($_SESSION['success_msg'])) {
    $success = $_SESSION['success_msg'];
    unset($_SESSION['success_msg']);
}

require_once '../includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h1 class="page-title">Kelola Ruangan</h1>
        <p class="text-muted">Tambah, ubah, atau hapus data ruangan kampus.</p>
    </div>
    <?php if ($action === 'list'): ?>
        <a href="manage_rooms.php?action=add" class="btn btn-primary">+ Tambah Ruangan</a>
    <?php else: ?>
        <a href="manage_rooms.php" class="btn" style="border: 1px solid var(--border-color);">Kembali ke Daftar</a>
    <?php endif; ?>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= escape($error) ?></div>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= escape($success) ?></div>
<?php endif; ?>

<?php if ($action === 'list'): ?>
    <div class="card">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Ruangan</th>
                        <th>Kapasitas</th>
                        <th>Fasilitas</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $rooms = $pdo->query("SELECT * FROM rooms ORDER BY id DESC")->fetchAll();
                    if (count($rooms) > 0):
                        foreach ($rooms as $r):
                    ?>
                        <tr>
                            <td><?= $r['id'] ?></td>
                            <td><?= escape($r['nama_ruangan']) ?></td>
                            <td><?= $r['kapasitas'] ?> Orang</td>
                            <td><?= escape($r['fasilitas']) ?></td>
                            <td>
                                <a href="manage_rooms.php?action=edit&id=<?= $r['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                                <a href="manage_rooms.php?action=delete&id=<?= $r['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus ruangan ini secara permanen?');">Hapus</a>
                            </td>
                        </tr>
                    <?php 
                        endforeach;
                    else: ?>
                        <tr><td colspan="5" style="text-align:center;">Belum ada ruangan yang didaftarkan.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php elseif ($action === 'add' || $action === 'edit'): 
    $room = null;
    if ($action === 'edit') {
        $id = $_GET['id'] ?? 0;
        $stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ?");
        $stmt->execute([$id]);
        $room = $stmt->fetch();
        if (!$room) {
            echo "<div class='alert alert-danger'>Data tidak ditemukan.</div>";
            require_once '../includes/footer.php';
            exit;
        }
    }
?>
    <div class="card" style="max-width: 600px; margin: 0 auto;">
        <h2><?= $action === 'add' ? 'Tambah Ruangan Baru' : 'Edit Ruangan' ?></h2>
        <form method="POST" action="manage_rooms.php?action=<?= $action ?>" style="margin-top: 1.5rem;">
            <?php if ($room): ?>
                <input type="hidden" name="id" value="<?= $room['id'] ?>">
            <?php endif; ?>
            
            <div class="form-group">
                <label class="form-label">Nama Ruangan</label>
                <input type="text" name="nama_ruangan" class="form-control" required value="<?= $room ? escape($room['nama_ruangan']) : '' ?>">
            </div>
            
            <div class="form-group">
                <label class="form-label">Kapasitas (Orang)</label>
                <input type="number" name="kapasitas" class="form-control" required min="1" value="<?= $room ? escape($room['kapasitas']) : '' ?>">
            </div>
            
            <div class="form-group">
                <label class="form-label">Fasilitas Lengkap</label>
                <textarea name="fasilitas" class="form-control" required><?= $room ? escape($room['fasilitas']) : '' ?></textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Nama File Foto (Opsional)</label>
                <input type="text" name="foto" class="form-control" placeholder="r1.jpg" value="<?= $room ? escape($room['foto']) : '' ?>">
            </div>

            <button type="submit" class="btn btn-success">Simpan Data</button>
        </form>
    </div>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>
