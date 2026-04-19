</main> <!-- Penutup tag main dari header.php -->

<footer>
    <p>&copy; <?= date("Y") ?> Sistem Booking Ruangan Kampus - Tugas Cloud Computing & Pemrograman</p>
    <p style="font-size: 0.75rem; margin-top: 0.5rem; color: var(--text-muted);">
        Aplikasi ini dilindungi dari XSS & SQL Injection. Password di-*hash* dengan BCRYPT.
    </p>
</footer>

<!-- Tambahan file JavaScript eksternal jika nantinya diperlukan -->
<script src="<?= $base_url ?>assets/js/script.js"></script>
</body>
</html>
