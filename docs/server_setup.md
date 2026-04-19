# Panduan Instalasi dan Konfigurasi Server (Ubuntu)

Dokumen ini berisi panduan tahap demi tahap untuk mempublikasikan proyek Sistem Booking Ruangan Kampus (Kamsis) pada OS **Ubuntu Server** (Linux) menggunakan *stack* LAMP (Linux, Apache, MySQL, PHP). Sistem ini dikonfigurasi untuk menangani sertifikat *Self-Signed HTTPS* demi standar keamanan tinggi dan enkripsi HTTP.

---

## Tahap 0: Persiapan Virtual Machine (VM) dan Jaringan

Sesuai dengan ketentuan tugas:
1. **Instalasi VM:** Buat Virtual Machine (misalnya menggunakan VirtualBox atau VMware) dan install OS Linux (disarankan **Ubuntu Server**).
2. **Penamaan VM:** Namai instance *Virtual Machine* Anda dengan format **Nama+NIM** (Contoh: `Bintang12345678`).
3. **Konfigurasi IP Address Statis (Kelas C):** Konfigurasikan Network Adapter VM (sebaiknya *Bridged* atau *Host-Only*) dan atur IP address secara statis menggunakan kelas C (misal `192.168.1.xxx`). Pastikan **octet terakhir adalah 3 digit NIM akhir** Anda (Contoh jika 3 digit terakhir NIM adalah `123`, maka atur IP sebagai `192.168.1.123`).

---

## Tahap 1: Instalasi Paket LAMP Dasar

Buka terminal SSH pada Ubuntu Server Anda, jalankan perintah berikut:

```bash
# Update repository
sudo apt update && sudo apt upgrade -y

# Install Apache2
sudo apt install apache2 -y

# Install MySQL Server
sudo apt install mysql-server -y

# Install PHP (Native) dan PDO (PHP Data Objects) beserta ekstensi penting
sudo apt install php libapache2-mod-php php-mysql php-cli php-common php-json -y

# Restart apache untuk memastikan terload dengan baik
sudo systemctl restart apache2
```

---

## Tahap 2: Konfigurasi Database MySQL

Kita perlu membuat database dan mengimpor tabel-tabel utama menggunakan file `database/schema.sql`.

1. **Masuk ke konsol MySQL sebagai root:**
   ```bash
   sudo mysql -u root
   ```

2. **Jalankan skrip pembentukan database (di dalam prompt MariaDB/MySQL):**
   ```sql
   -- Membuat database
   CREATE DATABASE kamsis_db;
   
   -- Membuat User khusus (Opsional untuk keamanan. Default di script config PHP yang kita buat adalah 'root')
   -- Jika Anda menggunakan password untuk root, masukkan query ini, jika tidak, abaikan pembuatan user dan biarkan config sesuai root.
   ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY 'password_aman_anda';
   FLUSH PRIVILEGES;
   
   EXIT;
   ```

3. **Mengimpor Skema (`schema.sql`):**
   Pindahkan folder *Kamsis* dari *repository/zip* Anda ke`/var/www/Kamsis`. Kemudian impor schema:
   ```bash
   # Masuk ke direktori
   cd /var/www/Kamsis/database
   
   # Impor database (masukkan password mysql jika diminta)
   sudo mysql -u root -p kamsis_db < schema.sql
   ```

---

## Tahap 3: Memindahkan File Proyek ke Direktori Web Apache

1. Pindahkan atau salin folder proyek **Kamsis** ke `/var/www/html/Kamsis`.
   ```bash
   sudo cp -r /var/www/Kamsis /var/www/html/Kamsis
   ```
2. Berikan hak akses kepada *user group* Apache (`www-data`):
   ```bash
   sudo chown -R www-data:www-data /var/www/html/Kamsis/
   sudo chmod -R 755 /var/www/html/Kamsis/
   ```

*Catatan: Pastikan mengubah kredensial koneksi (Username & Password) pada file `/var/www/html/Kamsis/config/database.php` sesuai dengan konfigurasi user MySQL di Tahap 2.*

---

## Tahap 4: Konfigurasi Keamanan (HTTPS dengan Self-Signed Certificate)

Untuk mengenkripsi jalur login, sesi, dan XSS, kita akan mengonfigurasi Apache dengan *Self-Signed Certificate* (SSL).

1. **Aktifkan Modul SSL pada Apache:**
   ```bash
   sudo a2enmod ssl
   sudo a2enmod rewrite
   ```

2. **Buat Sertifikat SSL Mandiri (Berlaku 365 hari):**
   ```bash
   sudo openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout /etc/ssl/private/apache-selfsigned.key -out /etc/ssl/certs/apache-selfsigned.crt
   ```
   *(Tekan "Enter" untuk membiarkan pengisian identitas default, atau isi sesuai identitas kampus Anda).*

3. **Konfigurasi Virtual Host Apache:**
   Ubah file Virtual Host bawaan SSL agar mengarah pada *DocumentRoot* bawaan:

   ```bash
   sudo nano /etc/apache2/sites-available/default-ssl.conf
   ```
   Pastikan baris `DocumentRoot`, `SSLCertificateFile`, dan `SSLCertificateKeyFile` mengarah ke:
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

4. **Aktifkan Situs SSL dan Restart Apache:**
   ```bash
   sudo a2ensite default-ssl.conf
   sudo systemctl restart apache2
   ```

---

## Tahap 5: Memaksa Pengalihan (Redirect) HTTP ke HTTPS

Agar semua trafik menuju halaman login dialihkan secara otomatis ke sambungan HTTPS yang aman, atur VirtualHost default:

1. Buka konfigurasi port 80:
   ```bash
   sudo nano /etc/apache2/sites-available/000-default.conf
   ```
2. Tambahkan *Redirect* di dalam blok `<VirtualHost *:80>`:
   ```apache
   <VirtualHost *:80>
       ServerAdmin webmaster@localhost
       DocumentRoot /var/www/html
       
       # Paksa Redirect secara permanen
       Redirect permanent "/" "https://<ALAMAT_IP_SERVER_ANDA>/"
   </VirtualHost>
   ```
3. **Restart Apache Terakhir:**
   ```bash
   sudo systemctl restart apache2
   ```

---

## Selesai! Cara Menjalankan Proyek 🚀

1. Buka browser web (Chrome/Firefox).
2. Kunjungi alamat IP Server Ubuntu Anda: `http://<ALAMAT_IP_SERVER_ANDA>/Kamsis/`
3. Karena Anda menggunakan Self-Signed Certificate, di kunjungan pertama browser Anda akan menampilkan **Peringatan Keamanan** (Security Warning). Ini adalah hal wajar. Cukup klik **Advanced (Lanjutan) -> "Proceed to <ALAMAT_IP> (Unsafe)"**.
4. Sistem akan otomatis diarahkan dan memuat aplikasi secara aman pada sambungan HTTPS (Tergembok).
5. Anda dapat mencoba login dengan **Akun Dummy** yang sudah disiapkan:
   - Admin: `admin123` / Password: `password123`
   - User: `12345678` / Password: `password123`
