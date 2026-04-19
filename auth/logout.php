<?php
// auth/logout.php
require_once '../config/security.php';
start_secure_session();

// Hapus secara manual semua data dalam variabel global sesion
$_SESSION = array();

// Berjaga-jaga jika session membutuhkan pengaturan cookie untuk dihapus
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Kemudian hancurkan seutuhnya
session_destroy();

// Redirect ke halaman utama / index
header("Location: ../index.php");
exit;
?>
