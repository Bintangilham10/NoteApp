<?php
// config/database.php

$host = 'localhost';
$dbname = 'kamsis_db';
$username = getenv('KAMSIS_DB_USER') ?: 'root'; // Override di server dengan env var.
$password = getenv('KAMSIS_DB_PASS');

if ($password === false) {
    $password = ''; // Default lokal; jangan commit password produksi ke source.
}

try {
    // Membuat koneksi ke database dengan PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    
    // Set response agar selalu menampilkan exception secara default (untuk proses debug)
    // Di mode production, error tidak dimunculkan ke end user namun dicatat di error log
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Mengatur PDO fetch mode default sebagai array asosiatif
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Mematikan emulasi prepared statements agar murni ditangani oleh driver DB (Aman dari injeksi tipe data)
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

} catch (PDOException $e) {
    // Tangani error secara diam-diam / general untuk end user
    die("Koneksi database gagal. Silakan hubungi administrator server.");
}
?>
