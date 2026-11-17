# 🍚 Saung Liwet — Sistem Pemesanan Digital Berbasis QR

[![Release](https://img.shields.io/badge/Release-v1.0.0--rc-2E7D32?style=for-the-badge)](https://github.com/mzeen008-dev/saung-liwet1-app/releases)
[![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net)
[![Database](https://img.shields.io/badge/MySQL-MariaDB-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mariadb.org)

> Proyek Uji Kompetensi Keahlian (UKK) Rekayasa Perangkat Lunak — Sprint 4: Finalization & Release Candidate
> **Demo online (staging):** http://saungliwet.rf.gd

---

## 📌 1. Latar Belakang dan Solusi

Rumah makan Sunda seperti **Saung Liwet** umumnya mencatat pesanan secara manual: pelayan menulis di kertas, lalu mengantarkannya ke kasir dan dapur. Cara ini rawan salah catat, pesanan terlambat sampai ke dapur, dan menyulitkan rekap pendapatan.

**Saung Liwet** menggantikannya dengan pemesanan mandiri lewat **kode QR di setiap meja**:

1. **Pesan dari ponsel tanpa akun.** Pelanggan memindai QR meja, memilih menu, menulis catatan khusus (mis. "sambal dipisah"), lalu checkout.
2. **Tiga metode pembayaran.** Tunai di kasir, QRIS, atau Transfer Bank, dengan unggah bukti bayar untuk QRIS dan Transfer.
3. **Alur dapur yang tertib.** Kasir memverifikasi pembayaran, lalu pesanan muncul di layar dapur dan statusnya terpantau pelanggan.
4. **Laporan pendapatan.** Rekap per hari dan per metode pembayaran, bisa dicetak atau diekspor ke CSV.

---

## ✨ 2. Fitur per Peran

| Peran | Fitur utama |
| :-- | :-- |
| **Pelanggan** (tanpa login) | Menu dari QR meja, catatan per item, checkout, pilih metode bayar, unggah bukti, pantau status pesanan |
| **Kasir** | Daftar pesanan masuk, verifikasi pembayaran, input uang diterima dan kembalian (tunai), cetak struk |
| **Koki** | Layar dapur (KDS), ubah status: diterima → dimasak → siap disajikan → selesai |
| **Admin** | Kelola menu dan stok, meja, akun staf, laporan pendapatan (cetak dan CSV) |

---

## 🛠️ 3. Tech Stack

| Lapisan | Teknologi |
| :-- | :-- |
| Backend | PHP 8.2+, Laravel 12 (arsitektur MVC) |
| Autentikasi | Session (web) dan Laravel Sanctum 4 (token REST API) |
| Frontend | Blade, CSS, JavaScript vanilla |
| Basis data | MySQL / MariaDB 10.4 (InnoDB) |
| Keamanan | Bcrypt, perlindungan CSRF, middleware `SecurityHeaders`, validasi input dan unggahan |
| Hosting staging | InfinityFree (PHP + MySQL) |

---

## 🚀 4. Panduan Instalasi Lokal

### Prasyarat
- PHP **8.2 atau lebih baru** dan [Composer](https://getcomposer.org)
- MySQL / MariaDB (XAMPP atau Laragon)
- Git

### Langkah instalasi (Windows PowerShell / terminal)

```bash
# 1. Klon repositori
git clone https://github.com/mzeen008-dev/saung-liwet1-app.git
cd saung-liwet1-app

# 2. Pasang dependensi
composer install

# 3. Salin berkas environment
cp .env.example .env          # PowerShell: Copy-Item .env.example .env

# 4. Buat application key
php artisan key:generate

# 5. Buat database kosong bernama db_saung_liwet_baru (phpMyAdmin), lalu cek .env
#    DB_DATABASE=db_saung_liwet_baru
#    DB_USERNAME=root
#    DB_PASSWORD=

# 6. Jalankan migrasi dan data awal
php artisan migrate --seed

# 7. Jalankan server
php artisan serve
```

Buka **http://127.0.0.1:8000**. Halaman login staf ada di `/login`. Halaman pelanggan untuk meja nomor 1: `/order/1`.

> Pastikan **MySQL di XAMPP sudah menyala** sebelum menjalankan `php artisan migrate`.

### Menjalankan di jaringan lokal (cadangan offline)

Bila internet restoran bermasalah, jalankan di laptop kasir lalu akses dari ponsel pelanggan di Wi-Fi/hotspot yang sama:

```bash
php artisan serve --host=0.0.0.0 --port=8000
```

Di Windows, izinkan port 8000 di Firewall, lalu buka `http://<IP-laptop>:8000/order/<nomor-meja>`. Skrip siap pakai: `jalankan-offline.bat`.

---

## ⚙️ 5. Contoh Konfigurasi `.env`

```dotenv
APP_NAME="Saung Liwet"
APP_ENV=local
APP_KEY=                       # diisi oleh php artisan key:generate
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=db_saung_liwet_baru
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=local
```

**Produksi:** gunakan `APP_ENV=production`, `APP_DEBUG=false`, `LOG_LEVEL=error`, dan kredensial basis data hosting. **Jangan pernah** mengunggah berkas `.env` ke GitHub.

---

## 🔑 6. Akun Uji Coba Default

| Peran | Username | Password | Hak akses utama |
| :-- | :-- | :-- | :-- |
| **Admin** | `[ISI]` | `[ISI]` | Menu, stok, meja, akun, laporan |
| **Kasir** | `[ISI]` | `[ISI]` | Verifikasi pembayaran, struk |
| **Koki** | `[ISI]` | `[ISI]` | Layar dapur, ubah status |
| **Pelanggan** | tanpa login | tanpa login | Pesan lewat `/order/{nomor meja}` |

> ⚠️ Akun di atas hanya untuk uji coba. Ganti password sebelum dipakai sungguhan.

---

## 📚 7. Dokumentasi Lengkap

| Dokumen | Lokasi |
| :-- | :-- |
| Buku panduan pengguna | [`docs/USER_MANUAL.pdf`](docs/USER_MANUAL.pdf) |
| Dokumentasi teknis (ERD, MVC, rute, API) | [`docs/TECHNICAL_DOCS.pdf`](docs/TECHNICAL_DOCS.pdf) |
| Dokumentasi API | `docs/04-api/` |

### Ringkasan REST API

Autentikasi memakai **Bearer token Sanctum** (`POST /api/login`). Endpoint utama: `GET/POST/PUT/DELETE /api/menu`, `POST /api/pesanan`, `GET /api/pesanan/kode/{kode}`, dan pembaruan status serta verifikasi pembayaran untuk staf. Rincian lengkap ada di dokumentasi teknis.

---

## 🗂️ 8. Struktur Singkat Proyek

```text
app/Http/Controllers/   Order, Kasir, Koki, Admin*, Laporan, Api/*
app/Http/Middleware/    SecurityHeaders
app/Models/             Meja, Menu, Pesanan, DetailPesanan, User
database/migrations/    skema basis data (7 tabel)
resources/views/        Blade: order, kasir, koki, admin
public/js, public/css   skrip dan gaya halaman
routes/                 web.php, api.php
docs/                   USER_MANUAL.pdf, TECHNICAL_DOCS.pdf
```

---

## 👥 9. Anggota Tim Pengembang

| Nama | Peran | Fokus |
| :-- | :-- | :-- |
| `[ISI NAMA SISWA 1]` | Siswa 1 | `[mis. Frontend dan UI/UX]` |
| `[ISI NAMA SISWA 2]` | Siswa 2 | `[mis. Backend dan Database]` |

*Pembimbing UKK:* `[ISI]`
*Sekolah:* `[ISI]` — 2026
