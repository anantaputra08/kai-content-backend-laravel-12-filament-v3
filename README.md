<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

# KAI Content - Aplikasi Streaming Interaktif
Aplikasi web streaming berbasis Laravel yang dirancang untuk menyajikan konten video melalui HLS (HTTP Live Streaming). Fitur utamanya adalah sistem voting interaktif di mana pengguna dapat memilih konten selanjutnya yang akan tayang. Sistem ini dirancang untuk berjalan dalam siklus yang berkelanjutan dan otomatis untuk setiap "gerbong" (carriage), menciptakan pengalaman seperti channel TV yang mandiri.

## Fitur Utama

1. Streaming Video HLS: Konversi video on-the-fly ke format HLS yang efisien untuk streaming.
2. Sistem Voting Real-time: Pengguna dapat memilih konten berikutnya yang ingin ditonton.
3. Siklus Otomatis: Sistem secara otomatis menayangkan konten pemenang setelah voting berakhir, dan memulai voting baru saat konten tersebut tayang.
4. Manajemen Konten: Panel admin yang dibuat dengan Filament untuk mengelola gerbong, kategori, dan konten video.
5. Proses Latar Belakang: Menggunakan Laravel Queue untuk menangani konversi video tanpa mengganggu interaksi pengguna.

## Tumpukan Teknologi
- Laravel Framework
- PHP 8.1+
- Filament 3 (Panel Admin)
- MySQL / MariaDB
- FFmpeg untuk pemrosesan video
- HLS (HTTP Live Streaming)

## Panduan Instalasi dan Penyiapan
Ikuti langkah-langkah berikut untuk menginstal dan menjalankan aplikasi di lingkungan lokal Anda.

### Prasyarat

Sebelum memulai, pastikan sistem Anda telah memenuhi persyaratan berikut:

- PHP (versi 8.1 atau lebih tinggi)
- Composer
- Node.js & NPM
- Database (Contoh: MySQL, MariaDB)
- FFmpeg: Perangkat lunak ini wajib ada untuk proses konversi video.

## Konfigurasi PHP untuk Upload Video Besar

Karena aplikasi ini menangani file video yang berukuran besar, Anda perlu mengonfigurasi PHP untuk mendukung upload dan pemrosesan file tersebut.

### Lokasi File php.ini

Terlebih dahulu, temukan lokasi file `php.ini` di sistem Anda:

**Untuk XAMPP/WAMP (Windows):**
- Biasanya terletak di `C:\xampp\php\php.ini` atau `C:\wamp64\bin\php\php[version]\php.ini`

**Untuk MAMP (macOS):**
- Terletak di `/Applications/MAMP/bin/php/php[version]/conf/php.ini`

**Untuk Linux:**
- Gunakan perintah `php --ini` untuk menemukan lokasi file

**Untuk mengetahui lokasi pasti:**
```bash
php --ini
```

### Pengaturan yang Diperlukan

Buka file `php.ini` dan ubah atau tambahkan konfigurasi berikut:

```ini
; Ukuran maksimum file yang dapat di-upload (sesuaikan dengan kebutuhan)
upload_max_filesize = 2G

; Ukuran maksimum data POST (harus lebih besar atau sama dengan upload_max_filesize)
post_max_size = 2G

; Batas waktu eksekusi script (dalam detik) - penting untuk konversi video
max_execution_time = 3600

; Batas waktu untuk input (dalam detik)
max_input_time = 3600

; Batas memori PHP (untuk pemrosesan file besar)
memory_limit = 1G

; Jumlah maksimum file yang dapat di-upload secara bersamaan
max_file_uploads = 20
```

### Penjelasan Konfigurasi:

- **upload_max_filesize**: Ukuran maksimum file yang dapat di-upload. Set ke 2G untuk video besar.
- **post_max_size**: Harus lebih besar dari upload_max_filesize karena mencakup seluruh data POST.
- **max_execution_time**: Waktu maksimum script dapat berjalan. 3600 detik (1 jam) cukup untuk konversi video.
- **max_input_time**: Waktu maksimum untuk menerima input data.
- **memory_limit**: Batas memori yang dapat digunakan PHP untuk pemrosesan.

### Restart Web Server

Setelah mengubah konfigurasi, restart web server Anda:

**XAMPP/WAMP:**
- Restart Apache melalui Control Panel

**MAMP:**
- Klik tombol "Stop" kemudian "Start"

**Linux (Apache):**
```bash
sudo systemctl restart apache2
```

**Linux (Nginx dengan PHP-FPM):**
```bash
sudo systemctl restart php8.1-fpm
sudo systemctl restart nginx
```

### Verifikasi Konfigurasi

Untuk memverifikasi bahwa konfigurasi sudah benar, buat file PHP sederhana:

```php
<?php
phpinfo();
?>
```

Atau gunakan perintah terminal:
```bash
php -i | grep -E "(upload_max_filesize|post_max_size|max_execution_time)"
```

# Langkah-langkah Instalasi

### 1. Clone Repository
Buka terminal Anda dan jalankan perintah berikut untuk meng-clone proyek ini.
```bash
git clone [https://github.com/anantaputra08/kai-content-backend-laravel-12-filament-v3.git]
```

### 2. Instalasi Dependensi
Instal semua dependensi PHP yang dibutuhkan menggunakan Composer.
```bash
composer install
```

### 3. Konfigurasi Environment
Salin file environment contoh dan buat file .env Anda sendiri.
```bash
cp .env.example .env
```
Setelah itu, buat kunci aplikasi yang unik.
```bash
php artisan key:generate
```

### 4. Konfigurasi Database
Buka file .env dan sesuaikan pengaturan database sesuai dengan konfigurasi lokal Anda.

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kai_stream   # Ganti dengan nama database Anda
DB_USERNAME=root         # Ganti dengan username database Anda
DB_PASSWORD=             # Ganti dengan password database Anda

# Pastikan URL ini benar sesuai dengan alamat server lokal Anda
APP_URL=http://127.0.0.1:8000
```

Jangan lupa untuk membuat database dengan nama yang sama seperti yang Anda tulis di DB_DATABASE.

### 5. Migrasi Database
Jalankan migrasi untuk membuat semua tabel yang dibutuhkan.
```bash
php artisan migrate --seed
```

### 6. Buat Symbolic Link untuk Storage
Perintah ini penting agar file yang di-upload (video, thumbnail) dapat diakses secara publik.
```bash
php artisan storage:link
```

## Instalasi FFmpeg

FFmpeg adalah komponen krusial. Cara instalasinya berbeda untuk setiap sistem operasi.

### Untuk Windows:

1. Unduh FFmpeg dari situs resmi.
2. Ekstrak file zip ke lokasi yang mudah diakses (misal: C:\ffmpeg).
3. Tambahkan lokasi folder bin dari FFmpeg (misal: C:\ffmpeg\bin) ke dalam Environment Variables PATH sistem Anda.
4. Restart terminal atau command prompt Anda.

### Untuk macOS:
Cara termudah adalah menggunakan Homebrew.
```bash
brew install ffmpeg
```

### Untuk Linux (Ubuntu/Debian):
Gunakan manajer paket apt.
```bash
sudo apt update && sudo apt install ffmpeg
```

### Verifikasi Instalasi
Untuk memastikan FFmpeg sudah terinstal dengan benar, jalankan perintah ini di terminal.
```bash
ffmpeg -version
```

# Menjalankan Aplikasi
Aplikasi ini memiliki 3 proses utama yang perlu berjalan secara bersamaan. Anda disarankan untuk membuka 3 tab terminal terpisah di direktori proyek.

## 🖥️ Terminal 1: Menjalankan Web Server
Proses ini akan menjalankan server web Laravel untuk melayani API dan halaman admin Filament.
```bash
php artisan serve
```
Aplikasi Anda akan dapat diakses di http://127.0.0.1:8000. Panel admin ada di /admin.

## 🔄 Terminal 2: Menjalankan Queue Worker
Proses ini WAJIB berjalan. Ia bertugas "mendengarkan" dan mengeksekusi tugas-tugas berat di latar belakang, seperti mengonversi video ke HLS setelah di-upload.
```bash
php artisan queue:work
```
Biarkan terminal ini tetap terbuka selama Anda menggunakan aplikasi.

## ⏱️ Terminal 3: Menjalankan Scheduler (untuk Development)
Proses ini akan menjalankan tugas-tugas terjadwal (seperti memeriksa voting yang sudah selesai) setiap menit.
```bash
php artisan schedule:work
```

**Catatan untuk Produksi:** Di server produksi, Anda tidak menjalankan schedule:work. Sebagai gantinya, Anda hanya perlu menambahkan satu baris Cron Job ke server Anda.
```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

# Troubleshooting Upload Video

Jika Anda mengalami masalah saat upload video, periksa hal-hal berikut:

## Error "Maximum execution time exceeded"
- Pastikan `max_execution_time` di php.ini sudah diset cukup tinggi (3600 atau lebih)
- Restart web server setelah mengubah konfigurasi

## Error "File too large" atau "Upload failed"
- Periksa `upload_max_filesize` dan `post_max_size` di php.ini
- Pastikan keduanya diset lebih besar dari ukuran file video Anda

## Error "Memory limit exceeded"
- Tingkatkan `memory_limit` di php.ini
- Untuk video sangat besar, pertimbangkan upload melalui chunk atau streaming

## Verifikasi Queue Processing
Pastikan queue worker berjalan dengan baik:
```bash
php artisan queue:failed  # Cek job yang gagal
php artisan queue:restart # Restart queue worker jika diperlukan
```

#  Alur Kerja Penggunaan
Setelah ketiga proses di atas berjalan, aplikasi Anda siap digunakan:

1. Akses panel admin di /admin dan login.
2. Buat data awal seperti Carriages dan Categories.
3. Masuk ke menu Content dan buat konten baru. Upload file video dan thumbnail. Klik "Create".
4. Proses konversi video akan berjalan di latar belakang (diproses oleh Terminal 2). Anda bisa menambahkan kolom status di Filament untuk memantaunya.
5. Setelah konversi selesai, klien (aplikasi Android/web) dapat mulai mengakses API GET /api/stream/status/{carriage_id} untuk memulai siklus streaming dan voting.

## Lisensi
Aplikasi ini berlisensi di bawah Lisensi MIT.