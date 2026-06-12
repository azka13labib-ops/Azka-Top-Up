# 🎮 AZKA TOP UP — Platform Top-Up Game Otomatis & Aman

[![Laravel Version](https://img.shields.io/badge/Laravel-11.x%20%2F%2013.x-red?style=for-the-badge&logo=laravel)](https://laravel.com)
[![Next.js Version](https://img.shields.io/badge/Next.js-14%20%2F%2015-black?style=for-the-badge&logo=next.js)](https://nextjs.org)
[![TailwindCSS](https://img.shields.io/badge/Tailwind_CSS-3.4+-38bdf8?style=for-the-badge&logo=tailwind-css)](https://tailwindcss.com)
[![PHP Version](https://img.shields.io/badge/PHP-8.4+-777bb4?style=for-the-badge&logo=php)](https://php.net)
[![TypeScript](https://img.shields.io/badge/TypeScript-5.x-blue?style=for-the-badge&logo=typescript)](https://www.typescriptlang.org)

**AZKA TOP UP** adalah platform top-up game otomatis, responsif, dan super aman yang menghubungkan langsung pelanggan dengan provider distributor game (**Digiflazz H2H**) dan gerbang pembayaran (**Midtrans Payment Gateway**).

Proyek ini menggunakan arsitektur **Decoupled (Separated)** dengan struktur Monorepo:
* 🖥️ **Backend**: Laravel API (PHP 8.4+) yang berfokus pada kecepatan transaksi, keamanan ketat, dan keandalan antrean (queue).
* 🎨 **Frontend**: Next.js (TypeScript & Tailwind CSS) untuk performa visual maksimal, SEO prima, dan UX yang mulus (dalam pengembangan).

---

## ✨ Fitur Utama

### 🔒 Lapisan Keamanan API Terintegrasi
Untuk melindungi transaksi dan data sensitif, backend telah dilengkapi dengan **6 Lapisan Keamanan**:
1. **Admin Guarding (Token Separation)**: Pemisahan token akses antara Customer dan Administrator menggunakan middleware `EnsureUserIsAdmin` untuk mencegah eksploitasi hak akses (*privilege escalation*).
2. **HTTP Security Headers**: Penerapan header perlindungan modern seperti *Content Security Policy (CSP)*, *X-Frame-Options* (anti-clickjacking), dan *X-XSS-Protection*.
3. **Webhook IP Whitelisting**: Hanya menerima *callback* notifikasi transaksi yang valid dari IP resmi **Midtrans** dan **Digiflazz**.
4. **Strict Rate Limiting**: Membatasi laju request pada *endpoint* krusial (Register, Login, Admin Login, dan Pembuatan Order) guna mencegah serangan DDoS dan Brute Force.
5. **Short-lived Admin Tokens**: Masa berlaku token administrator dibatasi maksimal **8 jam** saja, sedangkan token customer diatur kedaluwarsa dalam **7 hari** (dapat diubah via `.env`).
6. **Automated Status Checking**: Perintah terjadwal (`CheckExpiredPayments`) untuk membatalkan transaksi yang kedaluwarsa secara otomatis.

### ⚡ Fitur Transaksi Otomatis
* **Digiflazz H2H Integration**: Sinkronisasi produk, harga, dan pengiriman voucher/top-up otomatis instan setelah pembayaran sukses.
* **Midtrans Gateway Integration**: Mendukung pembayaran melalui e-Wallet (GOPAY, ShopeePay, dll), Virtual Account, dan retail outlet secara otomatis menggunakan mekanisme Webhook.
* **Robust Job Queue**: Pengiriman top-up ke Digiflazz diproses secara asinkron menggunakan Laravel Queue dengan opsi percobaan ulang (*auto-retry*) jika terjadi kegagalan jaringan distributor.

---

## 📂 Struktur Direktori Workspace

```text
/be topup (root)
├── Docs/                  # Pusat Dokumentasi & Spesifikasi (PRD)
│   ├── README.md          # Indeks utama dokumentasi proyek
│   ├── prd.md             # Dokumen Master PRD Proyek
│   ├── Task/              # Roadmap Tugas Terpisah
│   │   ├── be/            # Roadmap & PRD Backend (Laravel)
│   │   └── fe/            # Roadmap & PRD Frontend (Next.js)
│   └── Implementation/    # Catatan Teknis Implementasi & Pengujian
├── be/                    # Kode Backend (Laravel API Core)
└── fe/                    # Kode Frontend (Next.js Application) - [Akan di-init]
```

---

## 🚀 Panduan Memulai Cepat

### 1. Prasyarat Sistem
* **PHP** >= 8.4
* **Composer** >= 2.x
* **MySQL / MariaDB** >= 8.0
* **Node.js** >= 18.x (untuk frontend)
* **Laragon / XAMPP** (disarankan untuk Windows dev environment)

### 2. Konfigurasi Backend (Laravel)

```bash
# Masuk ke direktori backend
cd be

# Install dependensi PHP
composer install

# Salin konfigurasi environment
cp .env.example .env

# Generate Application Key
php artisan key:generate

# Jalankan migrasi database beserta data dummy awal
php artisan migrate --seed

# Jalankan Laravel Development Server
php artisan serve
```

> **Catatan Penting (.env):** 
> Pastikan Anda telah mengisi konfigurasi API Key untuk `MIDTRANS_SERVER_KEY`, `DIGIFLAZZ_USERNAME`, dan `DIGIFLAZZ_API_KEY` di file `.env` lokal Anda. File `.env` ini telah diabaikan (`ignored`) oleh Git agar tidak terekspos ke publik.

### 3. Konfigurasi Frontend (Next.js)
*(Segera hadir: Langkah-langkah inisialisasi dan instalasi framework frontend di direktori `fe/`)*

---

## 🧪 Pengujian API (API Testing)

Anda dapat menggunakan koleksi Postman yang telah kami sediakan untuk menguji alur registrasi, login, pembuatan order, hingga pengecekan status transaksi:
* File Postman: `[Docs/Task/be/AZKA_TOPUP_API.postman_collection.json](Docs/Task/be/AZKA_TOPUP_API.postman_collection.json)`

Untuk menjalankan pengujian unit/fitur secara lokal pada Laravel:
```bash
cd be
php artisan test
```

---

## 📝 Kontak & Kontribusi

Dikembangkan oleh **Azka** (azka13labib-ops). 
