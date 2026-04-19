CREATE DATABASE IF NOT EXISTS kamsis_db;
USE kamsis_db;

-- Tabel Users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_lengkap VARCHAR(100) NOT NULL,
    nim_nip VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Rooms
CREATE TABLE IF NOT EXISTS rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_ruangan VARCHAR(100) NOT NULL,
    kapasitas INT NOT NULL,
    fasilitas TEXT NOT NULL,
    foto VARCHAR(255) DEFAULT NULL
);

-- Tabel Bookings
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    room_id INT NOT NULL,
    tanggal_booking DATE NOT NULL,
    jam_mulai TIME NOT NULL,
    jam_selesai TIME NOT NULL,
    keperluan TEXT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE
);

-- Dummy Data untuk Admin dan User
-- Password untuk semua akun default adalah: password123
-- (Hash BCRYPT untuk 'password123')
INSERT INTO users (nama_lengkap, nim_nip, password, role) VALUES 
('Administrator Kampus', 'admin123', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Mahasiswa Bintang', '12345678', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user');

-- Dummy Data untuk Ruangan
INSERT INTO rooms (nama_ruangan, kapasitas, fasilitas, foto) VALUES 
('Ruang Rapat Utama (R1)', 20, 'Proyektor, AC, Papan Tulis, Kursi Nyaman', 'r1.jpg'),
('Laboratorium Komputer A', 40, '40 PC, AC, Proyektor, LAN', 'lab_a.jpg'),
('Ruang Kelas Reguler (A-101)', 50, 'AC, Papan Tulis, Proyektor', 'kelas_a101.jpg');
