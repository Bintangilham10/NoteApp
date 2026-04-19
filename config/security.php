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
 * Konfigurasi session yang aman untuk menghindari Session Hijacking
 */
function start_secure_session() {
    // Set keamanan cookies (memerlukan PHP dan pengaturan Server HTTP)
    $cookieParams = session_get_cookie_params();
    session_set_cookie_params([
        'lifetime' => $cookieParams["lifetime"],
        'path' => '/',
        'domain' => $cookieParams["domain"],
        'secure' => false, // Set ke true PADA PRODUKSI dimana HTTPS tersedia. Agar cookie hanya dikirim via sambungan internet ter-enkripsi.
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
?>
