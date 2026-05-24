<?php
// config/security.php

/**
 * Fungsi ini digunakan untuk mencegah Serangan Cross-Site Scripting (XSS).
 * Fungsi ini akan meng-encode karakter khusus pada input, mengubahnya
 * secara aman menjadi entitas HTML.
 * Sebagai contoh: skrip `<script>alert(1);</script>` menjadi `&lt;script&gt;alert(1);&lt;/script&gt;`
 * 
 * @param string $data Teks mentah dari database atau form
 * @return string Teks aman dari XSS
 */
function escape($data) {
    // Mengecek apakah datanya tidak null atau kosong
    if ($data === null || $data === '') {
        return '';
    }
    
    // ENT_QUOTES: Mengkonversi karakter ' dan " ke dalam entitas
    // UTF-8: Set karakternya ke UTF-8
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/**
 * Header keamanan dasar yang aman untuk aplikasi ini.
 */
function send_security_headers() {
    static $sent = false;

    if ($sent || headers_sent()) {
        return;
    }

    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');

    $sent = true;
}

/**
 * Konfigurasi session yang aman untuk menghindari Session Hijacking
 */
function start_secure_session() {
    send_security_headers();

    // Set keamanan cookies (memerlukan PHP dan pengaturan Server HTTP)
    $cookieParams = session_get_cookie_params();
    $isHttps = (
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['SERVER_PORT'] ?? null) == 443)
        || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
    );

    session_set_cookie_params([
        'lifetime' => $cookieParams["lifetime"],
        'path' => '/',
        'domain' => $cookieParams["domain"],
        'secure' => $isHttps, // Aktif otomatis di HTTPS, tetap berfungsi di lingkungan HTTP lokal.
        'httponly' => true, // Menolak akses manipulasi cookie melaui JavaScript (Mencegah Pencurian Sesi via XSS).
        'samesite' => 'Strict' // Cookie tidak akan dikirimkan pada permintaan lintas situs.
    ]);

    // Memulai session hanya jika belum di-start sebelumnya
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Melakukan regenerasi ID Session tiap saat menghindari Session Fixation Fix (Optional)
    // Jangan letakkan di semua reload agar tak merepotkan logika, 
    // Tapi sebaiknya diubah pada saat proses krusial seperti Login/Logout.
}

/**
 * Token CSRF untuk melindungi form POST dari request lintas situs.
 */
function csrf_token() {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        start_secure_session();
    }

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_input() {
    return '<input type="hidden" name="csrf_token" value="' . escape(csrf_token()) . '">';
}

function is_valid_csrf_token($token) {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        start_secure_session();
    }

    return is_string($token)
        && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}
?>
