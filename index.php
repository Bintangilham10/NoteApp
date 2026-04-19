<?php
// index.php
require_once 'config/security.php';
start_secure_session();

// Redirect otomatis sesuai role jika sudah login
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin/dashboard_admin.php");
    } else {
        header("Location: user/dashboard_user.php");
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kamsis - Booking Ruangan Kampus Lebih Cerdas</title>
    <!-- CSS Utama -->
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .hero-section {
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 6rem 5% 2rem;
            position: relative;
            overflow: hidden;
        }
        .hero-grid {
            width: 100%;
            max-width: 1250px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            gap: 4rem;
            align-items: center;
            position: relative;
            z-index: 10;
        }
        .hero-title {
            font-size: 4.5rem;
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 1.5rem;
            font-family: 'Outfit', sans-serif;
            color: var(--text-dark);
            letter-spacing: -1.5px;
        }
        .hero-text {
            font-size: 1.25rem;
            color: var(--text-muted);
            margin-bottom: 2.5rem;
            line-height: 1.8;
            max-width: 90%;
        }
        .glass-mockup {
            position: relative;
            background: rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(30px);
            -webkit-backdrop-filter: blur(30px);
            border: 1px solid rgba(255, 255, 255, 0.9);
            border-radius: 32px;
            padding: 2.5rem;
            box-shadow: 0 30px 60px -12px rgba(99, 102, 241, 0.15);
            transform: rotate(-2deg) translateY(0);
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: default;
        }
        .glass-mockup:hover {
            transform: rotate(0deg) translateY(-10px);
            box-shadow: 0 40px 80px -12px rgba(99, 102, 241, 0.25);
            border-color: #fff;
        }
        .gradient-text {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .floating-blob-1 { position: absolute; top: -100px; right: -100px; width: 400px; height: 400px; background: var(--secondary-color); filter: blur(120px); opacity: 0.3; border-radius: 50%; animation: floatSlow 10s infinite alternate; }
        .floating-blob-2 { position: absolute; bottom: -50px; left: -100px; width: 350px; height: 350px; background: var(--primary-color); filter: blur(100px); opacity: 0.3; border-radius: 50%; animation: floatSlow 12s infinite alternate-reverse; }
        
        @keyframes floatSlow {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(50px, 30px) scale(1.1); }
        }

        @media (max-width: 1024px) {
            .hero-grid { grid-template-columns: 1fr; text-align: center; gap: 3rem; }
            .hero-title { font-size: 3.5rem; }
            .hero-text { margin: 0 auto 2.5rem; }
            .hero-actions { justify-content: center; }
            .glass-mockup { transform: rotate(0); max-width: 500px; margin: 0 auto; }
            .glass-mockup:hover { transform: translateY(-5px); }
        }
        @media (max-width: 600px) {
            .hero-title { font-size: 2.5rem; letter-spacing: -0.5px; }
            .nav-actions { display: none; }
        }
    </style>
</head>
<body>
    
    <!-- Navbar -->
    <nav style="display: flex; justify-content: space-between; align-items: center; padding: 1.5rem 5%; position: absolute; width: 100%; top: 0; z-index: 50;">
        <div style="font-size: 1.5rem; font-weight: 800; display: flex; align-items: center; gap: 0.5rem; color: var(--text-dark);">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 32px; height: 32px; color: var(--primary-color);">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z" />
            </svg>
            <span class="gradient-text">Kamsis</span>
        </div>
        <div class="nav-actions" style="display: flex; gap: 1rem;">
            <a href="auth/login.php" class="btn" style="background: white; color: var(--text-dark); box-shadow: var(--shadow-soft); hover: box-shadow: var(--shadow-hover);">Masuk Akun</a>
            <a href="auth/register.php" class="btn btn-primary" style="border-radius: 99px; padding: 0.75rem 1.75rem;">Daftar Gratis</a>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="hero-section">
        <!-- Floating Ambient Blobs -->
        <div class="floating-blob-1"></div>
        <div class="floating-blob-2"></div>

        <div class="hero-grid">
            <!-- Left Info -->
            <div style="animation: slideUpFade 0.8s ease-out forwards;">
                <div style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; background: rgba(99, 102, 241, 0.1); color: var(--primary-color); border-radius: 99px; font-weight: 600; font-size: 0.875rem; margin-bottom: 2rem; border: 1px solid rgba(99, 102, 241, 0.2); box-shadow: 0 4px 15px rgba(99, 102, 241, 0.05);">
                    <span style="display: block; width: 8px; height: 8px; border-radius: 50%; background: var(--primary-color);"></span>
                    Platform Booking #1 di Kampus
                </div>
                
                <h1 class="hero-title">
                    Reservasi Ruang Kampus <span class="gradient-text">Lebih Cerdas.</span>
                </h1>
                
                <p class="hero-text">
                    Tinggalkan cara manual yang merepotkan. Pesan auditorium, ruang rapat, atau laboratorium secara <i>real-time</i>, kelola jadwal lebih cerdas, dan hindari bentrok jadwal dalam hitungan detik.
                </p>
                
                <div class="hero-actions" style="display: flex; gap: 1rem; flex-wrap: wrap;">
                    <a href="auth/register.php" class="btn btn-primary" style="padding: 1.15rem 2.5rem; font-size: 1.125rem; border-radius: 99px;">
                        Mulai Reservasi
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 20px; height: 20px;"><path stroke-linecap="round" stroke-linejoin="round" d="M17.25 8.25L21 12m0 0l-3.75 3.75M21 12H3" /></svg>
                    </a>
                </div>
                

            </div>

            <!-- Right Glass Mockup -->
            <div style="position: relative; animation: slideUpFade 1s ease-out forwards; animation-delay: 0.2s;">
                <!-- Glowing Orb Behind Mockup -->
                <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 100%; height: 100%; background: conic-gradient(from 180deg at 50% 50%, var(--primary-color) -93.75deg, #0ea5e9 33.75deg, var(--accent-color) 151.87deg, var(--primary-color) 266.25deg); border-radius: 50%; filter: blur(70px); opacity: 0.4;"></div>
                
                <div class="glass-mockup">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 2.5rem;">
                        <div>
                            <h3 style="font-size: 1.4rem; font-weight: 800; font-family: 'Outfit', sans-serif;">Auditorium Utama</h3>
                            <p style="color: var(--text-muted); font-size: 0.95rem; margin-top: 0.25rem;">Gedung Pusat, Lt. 1</p>
                        </div>
                        <span style="background: rgba(16, 185, 129, 0.1); color: #059669; padding: 0.5rem 1rem; border-radius: 99px; font-size: 0.85rem; font-weight: 700; display: flex; align-items: center; gap: 0.25rem;">
                            <span style="width: 6px; height: 6px; border-radius: 50%; background: #059669;"></span>
                            Tersedia
                        </span>
                    </div>

                    <div style="display: grid; gap: 1.25rem;">
                        <div style="display: flex; align-items: center; gap: 1.25rem; padding: 1.25rem; background: rgba(255,255,255,0.9); border-radius: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.02);">
                            <div style="width: 52px; height: 52px; background: var(--primary-light); color: var(--primary-color); border-radius: 14px; display: flex; align-items: center; justify-content: center;">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 26px; height: 26px;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                            <div style="flex: 1;">
                                <h4 style="font-weight: 700; font-size: 1rem;">10:00 - 12:00 WIB</h4>
                                <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 2px;">Jadwal Kosong</p>
                            </div>
                            <a href="#" style="color: var(--primary-color); font-weight: 700; font-size: 0.9rem;">Pesan</a>
                        </div>

                        <div style="display: flex; align-items: center; gap: 1.25rem; padding: 1.25rem; background: rgba(255,255,255,0.9); border-radius: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.02);">
                            <div style="width: 52px; height: 52px; background: #fee2e2; color: #ef4444; border-radius: 14px; display: flex; align-items: center; justify-content: center;">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 26px; height: 26px;"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" /></svg>
                            </div>
                            <div style="flex: 1;">
                                <h4 style="font-weight: 700; font-size: 1rem; color: #94a3b8;">13:00 - 15:00 WIB</h4>
                                <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 2px;">Kuliah Umum Fisika</p>
                            </div>
                            <span style="color: #cbd5e1; font-weight: 700; font-size: 0.9rem;">Penuh</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
