# Panduan Lengkap Instalasi dan Konfigurasi Server (Ubuntu)

Dokumen ini berisi panduan tahap demi tahap untuk mempublikasikan proyek **Sistem Booking Ruangan Kampus (Kamsis)** pada OS **Ubuntu Server** menggunakan *stack* LAMP (Linux, Apache, MySQL, PHP). Sistem ini dikonfigurasi secara profesional menangani *Self-Signed HTTPS*, IP Statis, serta proteksi keamanan basis data. Semuanya dijelaskan dengan **langkah pengecekan (verifikasi)** di setiap tahap.

---

## Tahap 0: Persiapan Virtual Machine (VM) dan Jaringan Statis

Sesuai dengan ketentuan tugas, 3 digit akhir NIM Anda adalah **016**, sehingga *IP Address* kelas C yang dialokasikan adalah **`192.168.1.16`**.

### Langkah A: Setting Network VirtualBox (Di Windows)
Lakukan instruksi ini saat VirtualBox VM Ubuntu Anda dalam kondisi mati (Power Off):
1. Buka VirtualBox, klik kanan pada VM bernama **Nama+NIM** Anda, pilih **Settings (Pengaturan)**.
2. Di menu kiri, klik **Network (Jaringan)**.
3. Pada tab **Adapter 1**, centang *Enable Network Adapter*.
4. Di bagian *Attached to (Terpasang pada)*, pilih **Bridged Adapter**.
5. Klik **OK** dan nyalakan (Start) VM Ubuntu Server Anda.

### Langkah B: Setting IP Statis di Terminal Ubuntu
1. Login ke terminal Ubuntu. Cari nama *Network Interface* (misal `enp0s3`) dengan perintah:
   ```bash
   ip a
   ```
2. Buka dan edit file konfigurasi jaringan `netplan`:
   ```bash
   sudo nano /etc/netplan/00-installer-config.yaml
   ```
   *(Catatan: nama file bisa juga 01-netcfg.yaml atau 50-cloud-init.yaml. Apabila kosong, gunakan CTRL+X dan cek isinya via `ls /etc/netplan/`)*
3. Sesuaikan isinya persis seperti format berikut (Gunakan *spasi* untuk tabulasi):
   ```yaml
   network:
     ethernets:
       enp0s3:                     
         dhcp4: false              
         addresses:
           - 192.168.1.16/24       
         routes:
           - to: default
             via: 192.168.1.1      
         nameservers:
           addresses: [8.8.8.8, 1.1.1.1]
     version: 2
   ```
4. Simpan (Ctrl+O, Enter), dan keluar (Ctrl+X). Terapkan pengaturan dengan perintah:
   ```bash
   sudo netplan apply
   ```

> **🔍 Pengecekan Tahap 0:**
> 1. Ketik perintah: `ip a`. Pastikan IP **192.168.1.16** muncul di antarmuka `enp0s3`.
> 2. Ketik perintah: `ping 8.8.8.8 -c 4`. Jika membalas (Reply), berarti internet Anda sudah jalan dan siap mengunduh paket.

---

## Tahap 1: Instalasi Paket LAMP Dasar

Terminal SSH/Ubuntu Server Anda kini butuh paket pondasi server web.

1. **Update dan Install semua paket inti:**
   ```bash
   sudo apt update && sudo apt upgrade -y
   sudo apt install apache2 mysql-server php libapache2-mod-php php-mysql php-cli php-common php-json -y
   ```

> **🔍 Pengecekan Tahap 1:**
> - Cek Status Web Server: `sudo systemctl status apache2` *(Harus tertulis **active (running)**)*.
> - Cek Status Database: `sudo systemctl status mysql` *(Harus **active (running)**)*.
> - Cek Versi PHP: `php -v` *(Akan muncul keterangan versi PHP yang terinstal)*.

---

## Tahap 2: Mengunduh Proyek dari GitHub

Karena file kode tersimpan di GitHub, kita akan melakukan *clone* langsung ke `/var/www/html`.

1. **Memulai *Clone* Repositori:**
   ```bash
   sudo apt install git -y
   cd /var/www/html
   sudo git clone https://github.com/Bintangilham10/NoteApp.git Kamsis
   ```
2. **Berikan Hak Akses Penuh ke Apache:**
   ```bash
   sudo chown -R www-data:www-data /var/www/html/Kamsis
   sudo chmod -R 755 /var/www/html/Kamsis
   ```

> **🔍 Pengecekan Tahap 2:**
> Ketik: `ls -la /var/www/html/Kamsis`
> *Pastikan ada nama file seperti `index.php`, `auth`, `config`, dan folder pendukung lainnya.*

---

## Tahap 3: Konfigurasi Database MySQL

Kita perlu membuat database kosong dan mengimpor struktur tabel bawaan.

1. **Masuk ke MySQL sebagai root:**
   ```bash
   sudo mysql -u root
   ```
2. **Jalankan kueri pembuatan (di MySQL prompt `mysql>`):**
   ```sql
   CREATE DATABASE kamsis_db;
   
   -- (Opsional) Khusus jika MySQL butuh integrasi password root
   ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY 'password_aman_anda';
   FLUSH PRIVILEGES;
   
   EXIT;
   ```
3. **Mengimpor Skema:**
   Teruskan ke terminal bash, lalu salin struktur `.sql` langsung ke database:
   ```bash
   sudo mysql -u root -p kamsis_db < /var/www/html/Kamsis/database/schema.sql
   ```

> **🔍 Pengecekan Tahap 3:**
> 1. Ketik: `sudo mysql -u root -e "SHOW DATABASES;"` -> *Pastikan ada `kamsis_db`*.
> 2. Ketik: `sudo mysql -u root -e "USE kamsis_db; SHOW TABLES;"` -> *Pastikan tabel `users`, `rooms`, dan `bookings` terdaftar di sana.*

---

## Tahap 4: Konfigurasi Keamanan (HTTPS dengan Sertifikat Mandiri)

Untuk mengamankan session (mencegah pencurian) dan mengamankan kata sandi yang dikirimkan.

1. **Aktifkan Modul Inti:**
   ```bash
   sudo a2enmod ssl
   sudo a2enmod rewrite
   ```
2. **Buat File Sertifikat SSL Mandiri (Umur 365 Hari):**
   ```bash
   sudo openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout /etc/ssl/private/apache-selfsigned.key -out /etc/ssl/certs/apache-selfsigned.crt
   ```
   *(Tekan "Enter" terus-menerus untuk membiarkan pengisian identitas nama instansi default).*

3. **Ubah Konfigurasi Virtual Host Apache HTTPS:**
   ```bash
   sudo nano /etc/apache2/sites-available/default-ssl.conf
   ```
   Pastikan baris path mengarah ke root standar:
   ```apache
   <VirtualHost _default_:443>
       ServerAdmin webmaster@localhost
       DocumentRoot /var/www/html

       SSLEngine on
       SSLCertificateFile      /etc/ssl/certs/apache-selfsigned.crt
       SSLCertificateKeyFile /etc/ssl/private/apache-selfsigned.key
       
       <Directory /var/www/html/Kamsis>
           Options Indexes FollowSymLinks
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   ```
4. **Aktifkan Profil SSL:**
   ```bash
   sudo a2ensite default-ssl.conf
   ```

> **🔍 Pengecekan Tahap 4:**
> Ketik perintah pengecekan sintaksis Apache: `sudo apache2ctl configtest`
> *Wajib menghasilkan output tertulis **Syntax OK**.*

---

## Tahap 5: Paksa Redirect (Mengalihkan HTTP ke HTTPS)

1. Tulis instruksi Redirect di port 80:
   ```bash
   sudo nano /etc/apache2/sites-available/000-default.conf
   ```
2. Tambahkan pengaturan berikut ke bagian dalam blok `<VirtualHost *:80>`:
   ```apache
   <VirtualHost *:80>
       ServerAdmin webmaster@localhost
       DocumentRoot /var/www/html
       
       # Memaksa HTTP lompat ke sambungan SSL HTTPS (Masukkan IP 16 Anda)
       Redirect permanent "/" "https://192.168.1.16/"
   </VirtualHost>
   ```
3. **Restart Penuh Apache:**
   ```bash
   sudo systemctl restart apache2
   ```

> **🔍 Pengecekan Terakhir (Tahap 5):**
> 1. Buka browser (Chrome/Firefox) di Laptop Windows Anda.
> 2. Ketikkan `http://192.168.1.16/Kamsis/` (Tanpa huruf S di HTTP).
> 3. *Perhatikan indikator loading di atas*. Ia harus seketika berubah menjadi `https://...` dan memunculkan Peringatan Keamanan *(Privacy error yang merupakan sifat natural self-signed SSL)*.
> 4. Klik tombol **Advanced (Lanjutan)** > lalu **"Proceed to 192.168.1.16"**. Selesai.

### Menguji Program & Login (Demonstrasi Demo)
Laman Web akhirnya terbuka dengan aman!
Login saja memakai akun dummy otomatis ini (Sisi Admin/User):
- **Admins:** User: `admin123` | Password: `password123`
- **Anggota:** User: `12345678` | Password: `password123`
