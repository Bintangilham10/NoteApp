# Project 3 - Suricata pada VM Terpisah

Panduan ini dipakai jika project 2 sudah berjalan pada VM Ubuntu web server, lalu project 3 dibuat dengan VM Ubuntu kedua khusus IDS. VM IDS diberi nama sesuai nama mahasiswa, misalnya `Bintang-Suricata`.

## 1. Topologi

```text
Windows Host / Attacker
        |
        | ping flood / TCP port 880
        |
VM 1: Kamsis Web Server
IP  : 192.168.18.16
App : Apache, MySQL, PHP dari project 2

VM 2: Bintang-Suricata
IP  : 192.168.18.17
App : Suricata IDS
```

Default IP mengikuti project 2: web server berada di `192.168.18.16`. Jika IP dari dosen berbeda, gunakan subnet kelas C yang sama untuk web dan IDS, misalnya:

```text
Web Server : 192.168.18.16/24
IDS VM     : 192.168.18.17/24
Gateway    : 192.168.18.1
```

## 2. Setting VirtualBox

Gunakan jaringan yang sama untuk kedua VM. Pilihan paling stabil untuk demo satu laptop adalah `Host-only Adapter` atau `Bridged Adapter`.

### Opsi A - Host-only Adapter

Gunakan opsi ini jika serangan dilakukan dari Windows host ke VM web server.

1. Buka VirtualBox.
2. Pastikan VM web server dan VM IDS memakai adapter yang sama, misalnya `VirtualBox Host-Only Ethernet Adapter`.
3. Pada VM IDS `Bintang-Suricata`, buka `Settings > Network > Adapter`.
4. Set `Promiscuous Mode` menjadi `Allow All`.
5. Jalankan kedua VM.

### Opsi B - Bridged Adapter

Gunakan opsi ini jika project 2 sudah memakai bridged network dan web server sudah bisa dibuka dari perangkat lain.

1. VM web server dan VM IDS sama-sama pakai `Bridged Adapter`.
2. Adapter harus bridge ke kartu jaringan yang sama.
3. Pada VM IDS `Bintang-Suricata`, set `Promiscuous Mode` menjadi `Allow All`.
4. Jika memakai attacker VM lain, letakkan attacker VM di jaringan yang sama.

Catatan penting: tanpa promiscuous mode, VM IDS biasanya hanya melihat traffic miliknya sendiri. Berdasarkan manual VirtualBox, mode `Allow All` membuat adapter VM dapat melihat traffic lain pada jaringan virtual yang sama.

## 3. Konfigurasi IP VM IDS

Di VM IDS, cek nama interface:

```bash
ip -br a
```

Misalnya interface bernama `enp0s3`. Edit netplan:

```bash
sudo nano /etc/netplan/00-installer-config.yaml
```

Contoh isi untuk VM IDS:

```yaml
network:
  version: 2
  renderer: networkd
  ethernets:
    enp0s3:
      dhcp4: false
      addresses:
        - 192.168.18.17/24
      routes:
        - to: default
          via: 192.168.18.1
      nameservers:
        addresses: [8.8.8.8, 1.1.1.1]
```

Terapkan konfigurasi:

```bash
sudo netplan apply
ip a
ping -c 4 192.168.18.16
```

Jika `ping` ke web server berhasil, lanjut.

## 4. Install Suricata Terbaru di VM IDS

Di VM IDS:

```bash
sudo apt update
sudo apt install software-properties-common jq tcpdump netcat-openbsd -y
sudo add-apt-repository ppa:oisf/suricata-stable -y
sudo apt update
sudo apt install suricata -y
```

Cek versi dan status:

```bash
suricata --build-info
sudo systemctl status suricata
```

## 5. Konfigurasi Suricata

Backup konfigurasi:

```bash
sudo cp /etc/suricata/suricata.yaml /etc/suricata/suricata.yaml.bak
```

Edit konfigurasi:

```bash
sudo nano /etc/suricata/suricata.yaml
```

Cari bagian `vars` lalu set `HOME_NET` ke IP web server:

```yaml
vars:
  address-groups:
    HOME_NET: "[192.168.18.16]"
```

Cari bagian `af-packet` lalu pastikan interface sama dengan interface VM IDS. Contoh:

```yaml
af-packet:
  - interface: enp0s3
    cluster-id: 99
    cluster-type: cluster_flow
    defrag: yes
    use-mmap: yes
    tpacket-v3: yes
```

Pastikan juga rule path menggunakan hasil `suricata-update`:

```yaml
default-rule-path: /var/lib/suricata/rules
rule-files:
  - suricata.rules
```

## 6. Tambahkan Rule Manual Project 3

Buat file rule lokal:

```bash
sudo nano /etc/suricata/rules/project3-local.rules
```

Isi:

```suricata
alert icmp any any -> $HOME_NET any (msg:"PROJECT3 ICMP ping flood attempt to Kamsis web server"; itype:8; threshold:type threshold, track by_src, count 20, seconds 1; classtype:attempted-dos; sid:1000001; rev:1;)
alert tcp any any -> $HOME_NET 880 (msg:"PROJECT3 TCP connection attempt to blocked port 880 on Kamsis web server"; flags:S; flow:to_server,stateless; classtype:attempted-recon; sid:1000002; rev:1;)
```

Rule pertama mendeteksi ping ICMP flooding. Rule kedua mendeteksi koneksi TCP ke port `880`.

## 7. Update Rule Terbaru

Jalankan `suricata-update` agar rule terbaru dari Emerging Threats Open digabung dengan rule manual:

```bash
sudo suricata-update --local /etc/suricata/rules/project3-local.rules
```

Cek rule manual sudah masuk:

```bash
sudo grep "PROJECT3" /var/lib/suricata/rules/suricata.rules
```

Tes konfigurasi:

```bash
sudo suricata -T -c /etc/suricata/suricata.yaml -v
```

Restart Suricata:

```bash
sudo systemctl restart suricata
sudo systemctl status suricata
sudo tail -n 30 /var/log/suricata/suricata.log
```

Jika log memuat `engine started`, Suricata sudah berjalan.

## 8. Pastikan IDS Melihat Traffic Web Server

Sebelum menguji rule, pastikan VM IDS benar-benar melihat paket menuju web server.

Di VM IDS:

```bash
sudo tcpdump -ni enp0s3 host 192.168.18.16
```

Dari Windows host atau attacker VM, jalankan:

```bash
ping 192.168.18.16
```

Jika `tcpdump` menampilkan paket ICMP, setting VirtualBox sudah benar. Jika tidak muncul, periksa lagi `Promiscuous Mode`, tipe adapter, dan apakah web server serta IDS berada pada network yang sama.

## 9. Tes Ping ICMP Flooding

Jika attacker memakai Ubuntu/Kali:

```bash
sudo ping -f -c 500 192.168.18.16
```

Alternatif dengan `hping3`:

```bash
sudo apt install hping3 -y
sudo hping3 --icmp --flood 192.168.18.16
```

Tekan `Ctrl+C` untuk menghentikan `hping3`.

Jika attacker memakai Windows PowerShell:

```powershell
1..300 | ForEach-Object { ping -n 1 -w 100 192.168.18.16 }
```

Cek alert di VM IDS:

```bash
sudo grep "PROJECT3 ICMP" /var/log/suricata/fast.log
```

Output yang diharapkan:

```text
PROJECT3 ICMP ping flood attempt to Kamsis web server
```

## 10. Tes TCP Connection ke Port 880

Jika attacker memakai Ubuntu/Kali:

```bash
for i in $(seq 1 10); do nc -vz -w 1 192.168.18.16 880 || true; done
```

Alternatif SYN flood ringan untuk port `880`:

```bash
sudo hping3 -S -p 880 -c 50 192.168.18.16
```

Jika attacker memakai Windows PowerShell:

```powershell
1..10 | ForEach-Object { Test-NetConnection 192.168.18.16 -Port 880 }
```

Cek alert:

```bash
sudo grep "PROJECT3 TCP" /var/log/suricata/fast.log
```

Output yang diharapkan:

```text
PROJECT3 TCP connection attempt to blocked port 880 on Kamsis web server
```

## 11. Bukti untuk Laporan

Ambil screenshot atau salin output dari perintah berikut:

```bash
hostname
ip -br a
suricata --build-info | head -n 20
sudo grep "PROJECT3" /var/lib/suricata/rules/suricata.rules
sudo tail -n 30 /var/log/suricata/fast.log
```

Di VM web server project 2, ambil bukti:

```bash
hostname
ip -br a
sudo systemctl status apache2
```

## 12. Catatan IDS vs IPS

Konfigurasi ini adalah mode IDS pasif. Suricata mendeteksi dan mencatat serangan, tetapi tidak memblokir paket secara langsung. Jika dosen meminta pemblokiran nyata, Suricata harus dijadikan IPS inline/gateway dengan dua interface, atau web server diberi firewall tambahan seperti `ufw deny 880/tcp`.

Untuk ketentuan project ini, bukti paling penting adalah:

- Suricata terpasang di VM terpisah bernama mahasiswa.
- IP web server dan IDS berada dalam kelas C yang sama.
- Rule terbaru dijalankan dengan `suricata-update`.
- Rule manual ICMP flooding dan TCP port `880` aktif.
- Serangan menghasilkan alert di `/var/log/suricata/fast.log`.

## 13. Referensi

- Suricata Ubuntu Package Installation: <https://docs.suricata.io/en/suricata-8.0.3/install/ubuntu.html>
- Suricata Quickstart: <https://docs.suricata.io/en/suricata-7.0.3/quickstart.html>
- Suricata Rule Management with Suricata-Update: <https://docs.suricata.io/en/suricata-8.0.1/rule-management/suricata-update.html>
- Suricata AF_PACKET IPS mode: <https://docs.suricata.io/en/latest/ips/setting-up-ipsinline-for-linux.html>
- Oracle VirtualBox User Manual, Promiscuous Mode: <https://download.virtualbox.org/virtualbox/6.1.16/UserManual.pdf>
