# AZKA TOP UP — Product Requirements Document (PRD)

---

| Field | Detail |
|---|---|
| **Versi** | 1.0 |
| **Tanggal** | Juni 2026 |
| **Status** | Approved for Development |
| **Stack** | Next.js 14 + Laravel 13 + MySQL + Redis |
| **Provider** | Digiflazz (H2H) + Midtrans (Payment) |
| **Tim** | Solo Developer |
| **Target Live** | 8 Minggu |

---

## Daftar Isi

1. [Project Overview](#1-project-overview)
2. [Target Audience & User Personas](#2-target-audience--user-personas)
3. [User Flows](#3-user-flows)
4. [Functional Requirements](#4-functional-requirements)
5. [Non-Functional Requirements](#5-non-functional-requirements)
6. [Tech Stack & Architecture](#6-tech-stack--architecture)
7. [Milestones & Development Phases](#7-milestones--development-phases)

---

## 1. Project Overview

### 1.1 Latar Belakang

Indonesia adalah pasar game mobile terbesar ke-4 di dunia dengan 67+ juta active gamers. Top-up in-game item — diamond, robux, UC, voucher — merupakan kebutuhan rutin dengan frekuensi tinggi. Namun sebagian besar platform yang ada menghadapi masalah struktural: proses manual atau semi-manual, UI tidak mobile-friendly, harga tidak transparan, dan response time lambat yang merusak pengalaman pengguna.

AZKA TOP UP hadir sebagai platform **top-up otomatis 100%** berbasis teknologi H2H (Host-to-Host) langsung ke Digiflazz, dengan Midtrans sebagai payment gateway utama. Arsitektur decoupled (Next.js frontend + Laravel REST API) dirancang untuk menskalakan 100–500 transaksi per hari sejak hari pertama launch, tanpa intervensi manual per transaksi.

### 1.2 Tujuan Bisnis

1. Membangun platform automated top-up dengan success rate ≥ 98%
2. Memproses end-to-end transaksi (bayar → top-up terkirim) dalam < 30 detik
3. Melayani 100–500 transaksi/hari pada bulan pertama operasional
4. Menghasilkan revenue dari **markup harga** atas cost H2H Digiflazz (flat Rp atau persentase per produk)
5. Mencapai break-even dalam 3 bulan pasca-launch

### 1.3 Scope v1 (Wajib Launch)

**Termasuk dalam scope:**
- Storefront produk top-up: Free Fire, Mobile Legends, Roblox, PUBG Mobile, dan game lain dari katalog Digiflazz
- Checkout otomatis: Guest mode dan Registered User mode
- Integrasi H2H Digiflazz: eksekusi top-up fully automated
- Payment gateway Midtrans: QRIS, E-Wallet (GoPay, DANA, ShopeePay), Virtual Account
- Webhook-driven automation: zero human intervention per transaksi
- Admin dashboard: manajemen produk, markup, monitoring, analytics

**Out of scope (v2+):**
- Loyalty point / reward system
- Live chat support
- Mobile app native (iOS/Android)
- Multi-provider failover (Apigames, dll.)
- Reseller dashboard dengan sub-akun
- Bulk order / multi-item checkout

---

## 2. Target Audience & User Personas

### 2.1 Segmentasi User

| Segment | Profil | Volume Est. | Prioritas |
|---|---|---|---|
| Casual Gamer Mobile | Pelajar, transaksi Rp 5k–50k, e-wallet only | 60% traffic | Tinggi |
| Regular Gamer | Mahasiswa/kerja, familiar top-up, butuh histori | 30% traffic | Tinggi |
| Reseller/Bulk Buyer | Wirausaha, beli ulang, butuh rekap & invoice | 10% traffic | Menengah |

### 2.2 Persona Detail

---

**PERSONA 1 — Rizky (Casual Gamer)**

- **Usia:** 16–19 tahun | Pelajar SMA/SMK
- **Device:** Smartphone Android mid-range (Redmi, Samsung A-series)
- **Game utama:** Free Fire, Mobile Legends
- **Metode bayar:** DANA, GoPay (tidak punya rekening bank sendiri)
- **Perilaku:** Impulsif, beli saat mau main, tidak suka form panjang, ingin instant
- **Pain point:** Gagal bayar QRIS, proses lama tidak jelas, bingung cara cari User ID
- **Kebutuhan dari platform:**
  - UI simpel, 3 klik dari landing ke checkout
  - Panduan inline cara menemukan Game User ID
  - Konfirmasi nama akun sebelum bayar (trust signal)
  - Loading cepat di koneksi 4G standar

---

**PERSONA 2 — Anisa (Regular Gamer)**

- **Usia:** 20–25 tahun | Mahasiswa / fresh graduate
- **Device:** iPhone 13 atau Android flagship
- **Game utama:** Roblox, Genshin Impact, PUBG Mobile
- **Metode bayar:** ShopeePay, BCA Virtual Account
- **Perilaku:** Membandingkan harga, mau daftar akun untuk repeat purchase lebih mudah
- **Pain point:** Platform tidak reliable, tidak ada notifikasi status real-time, tidak ada histori
- **Kebutuhan dari platform:**
  - Akun user dengan dashboard riwayat transaksi
  - Email konfirmasi + status order real-time
  - Tombol "Pesan Lagi" untuk produk favorit
  - Platform terasa profesional dan terpercaya

---

**PERSONA 3 — Doni (Reseller)**

- **Usia:** 25–35 tahun | Wirausaha digital
- **Device:** PC + Smartphone
- **Volume:** 20–50 transaksi/hari untuk dijual kembali
- **Metode bayar:** BCA VA, BNI VA (nominal besar, butuh bukti transfer)
- **Perilaku:** Price-sensitive, butuh rekap untuk pembukuan, loyal jika harga kompetitif
- **Pain point:** Tidak ada export histori, markup tidak jelas, susah lacak order gagal
- **Kebutuhan dari platform:**
  - Dashboard transaksi dengan filter dan export CSV
  - Email receipt otomatis per order
  - Status order jelas dan cepat
  - Harga stabil dan kompetitif

---

### 2.3 Admin/Operator

- **Profil:** Pemilik platform (solo developer / tim kecil)
- **Kebutuhan:** Monitor transaksi real-time, kelola produk & markup, pantau saldo Digiflazz, tangani order bermasalah dengan minimum effort
- **Critical:** Alert saldo Digiflazz rendah agar tidak ada failed order karena deposit habis

---

## 3. User Flows

### 3.1 Alur Guest Checkout (Primary Flow — Target: ≤ 5 tap ke payment page)

```
[Landing Page]
      │
      ▼
[Pilih Kategori Game]  ──────────────────────────────────────
      │                                                       │
      ▼                                                       ▼
[Halaman Game]                                     [Search → Game Page]
[List Denominasi/Produk]
      │
      ▼
[Klik Produk → Form Order]
  ├── Input: Game User ID  (required, validasi format)
  ├── Input: Zone/Server ID  (conditional — hanya ML & game tertentu)
  ├── Input: Email  (required, untuk receipt)
  └── [Cek Nama Akun]  (opsional, via game API jika tersedia)
      │
      ▼
[Pilih Metode Pembayaran]
  ├── QRIS  (expire: 15 menit)
  ├── E-Wallet: GoPay / DANA / ShopeePay  (expire: 15 menit)
  └── Virtual Account: BCA / BNI / Mandiri  (expire: 24 jam)
      │
      ▼
[Order Summary — Review & Konfirmasi]
  └── Tampilkan: Produk, Game ID, Harga, Metode, Kode Order
      │
      ▼
[Klik "Bayar Sekarang" → Midtrans Snap.js Popup]
      │
      ▼ (User selesai bayar)
[Midtrans Webhook → POST /api/webhook/midtrans]
  └── Validasi signature SHA512
  └── Update payment_status = paid
  └── Dispatch: ExecuteTopupJob → Redis Queue
      │
      ▼
[ExecuteTopupJob Worker]
  └── POST ke Digiflazz Prepaid API
      ├── Response: Sukses → update DB + dispatch SendEmailJob (sukses)
      ├── Response: Pending → retry dalam 30 detik (max 3x)
      └── Response: Gagal → update DB + flag refund + dispatch SendEmailJob (gagal)
      │
      ▼
[Halaman /order/{order_code} — Real-time Polling setiap 5 detik]
  ├── Status: ⏳ Menunggu Pembayaran
  ├── Status: 🔄 Diproses
  ├── Status: ✅ Sukses  →  tampilkan SN/token, detail lengkap
  └── Status: ❌ Gagal   →  informasi refund, kontak support
      │
      ▼
[Email Receipt otomatis terkirim via SendEmailJob]
```

---

### 3.2 Alur Registered User (Extended)

```
[Login / Register]
      │
      ▼  (alur sama persis dengan Guest Checkout di atas)
      │
      ▼
[Order tersimpan ke tabel orders dengan user_id]
      │
      ▼
[Dashboard User /dashboard/orders]
  ├── Tabel histori transaksi dengan filter
  ├── Klik baris → modal detail order
  └── [Tombol "Pesan Lagi"] → pre-fill Game ID & Produk ke form baru
```

---

### 3.3 Alur Admin — Operasional Harian

```
[Admin Login → /admin/login]
      │
      ▼
[Dashboard Overview]
  ├── Revenue hari ini vs kemarin (card)
  ├── Transaksi: sukses / pending / gagal (card)
  ├── Success rate % (card)
  └── ⚠️ Alert: Saldo Digiflazz < threshold
      │
      ├──▶ [Manajemen Produk]
      │       ├── [🔄 Sync Pricelist Digiflazz] → update base_price semua produk
      │       ├── Set markup per produk (flat/persen) + preview selling_price
      │       └── Toggle enable/disable per produk atau bulk per game
      │
      └──▶ [Monitor Transaksi]
              ├── Filter: status, tanggal, game, metode bayar
              ├── Search: order_code, email, game ID
              ├── Klik baris → detail + raw Digiflazz payload + order log
              ├── [🔁 Manual Retry] untuk order stuck/failed
              ├── [🚩 Flag Refund] + tambah catatan
              └── [📥 Export CSV]
```

---

## 4. Functional Requirements

> Format: **FR-XX | Nama Fitur** | Priority: 🔴 Must Have / 🟡 Should Have / 🟢 Nice to Have (v1)

---

### 4.1 Customer-Facing Features

#### FR-01 | Homepage & Navigasi 🔴

- Grid kategori game: thumbnail + nama game, sorted by sort_order
- Section "Produk Populer": 6 produk dengan order count tertinggi
- Search bar: real-time filter game atau produk (client-side)
- Next.js SSG untuk semua halaman kategori game (re-generate via ISR setiap 1 jam)
- Meta title format: `Top Up {Game} Murah & Instan | AZKA TOP UP`
- Semua halaman mobile-first; breakpoint wajib: 375px, 768px, 1280px

#### FR-02 | Halaman Produk & Form Order 🔴

- List denominasi/produk dengan harga jual (post-markup) dan badge ketersediaan
- Harga tampil jelas, tidak menyembunyikan markup sebagai "biaya admin"
- Input Game User ID: required, validasi format per game (numerik untuk FF, dll.)
- Input Zone/Server ID: tampil **kondisional** hanya untuk game yang memerlukan (Mobile Legends, Honor of Kings, dll.) — dikontrol via field `needs_zone` di tabel games
- Panduan menemukan User ID: modal/tooltip inline per game dengan screenshot/ilustrasi
- Input Email: validasi format, required untuk guest checkout
- Preview nama akun (opsional): jika game support validasi ID, tampilkan nama karakter setelah input UID

#### FR-03 | Pembayaran via Midtrans 🔴

- **Snap.js popup** — embedded di halaman, tidak redirect keluar
- Metode aktif: QRIS, GoPay, DANA, ShopeePay, BCA VA, BNI VA, Mandiri Bill
- Expiry: QRIS/E-Wallet 15 menit, VA 24 jam
- Jika user tutup popup sebelum bayar: halaman kembali ke form order, order_code tersimpan, snap_token masih valid hingga expire
- Halaman **Menunggu Pembayaran** dengan:
  - Countdown timer sesuai expiry
  - Instruksi pembayaran sesuai metode yang dipilih
  - Tombol "Batalkan" (set order status = cancelled)
- `order_id` yang dikirim ke Midtrans = `order_code` sistem (format: `AZKA-YYYYMMDD-XXXXX`)
- `gross_amount` = `selling_price` yang ter-lock saat order dibuat — tidak bisa berubah

#### FR-04 | Eksekusi Top-Up via Digiflazz 🔴

- **Trigger:** Midtrans webhook `payment.success` → dispatch `ExecuteTopupJob` ke Redis queue
- **Request ke Digiflazz Prepaid API:**
  - `username` = Digiflazz username
  - `buyer_sku_code` = `products.digiflazz_sku`
  - `customer_no` = game User ID (+ Zone ID jika diperlukan, format sesuai game)
  - `ref_id` = `order_code` (unique per order, gunakan sebagai idempotency key)
  - `sign` = MD5(username + api_key_produksi + ref_id)
- **Idempotency:** sebelum request, cek apakah `ref_id` sudah pernah dikirim ke Digiflazz. Jika ya, skip dan ambil status dari response Digiflazz via inquiry.
- **Penanganan status response:**

| Status Digiflazz | Aksi Sistem |
|---|---|
| `Sukses` | Update `topup_status = completed`, simpan `sn` (serial number), dispatch `SendEmailJob` (sukses) |
| `Pending` | Update `topup_status = processing`, schedule retry dalam 30 detik via `dispatch()->delay(30)`, max 3 retry |
| `Gagal` | Update `topup_status = failed`, simpan `failure_reason`, set `refund_flagged_at`, dispatch `SendEmailJob` (gagal) |

- **Timeout handling:** Jika Digiflazz tidak merespons dalam 10 detik → perlakukan sebagai `Pending`, bukan `Gagal`
- **Webhook Digiflazz:** endpoint `POST /api/webhook/digiflazz` untuk menerima callback status final. Validasi signature: MD5(username + api_key + trx_id)

#### FR-05 | Halaman Status Order 🔴

- URL: `/order/{order_code}` — dapat diakses tanpa login
- Polling ke `GET /api/v1/orders/{order_code}/status` setiap 5 detik via SWR
- Hentikan polling otomatis jika status sudah `completed` atau `failed`
- Status visual dengan badge + animasi:
  - ⏳ Menunggu Pembayaran (kuning)
  - 🔄 Sedang Diproses (biru + spinner)
  - ✅ Top-up Berhasil (hijau)
  - ❌ Top-up Gagal (merah)
- **Halaman Sukses:** tampilkan game, denominasi, Game ID, timestamp, SN/token jika tersedia
- **Halaman Gagal:** informasi kegagalan + langkah refund + kontak support (email/WA)
- Email receipt otomatis terkirim (sudah di-dispatch dari FR-04)
- Tombol "Pesan Lagi" untuk produk yang sama

#### FR-06 | Autentikasi User 🟡

- **Guest Checkout (Utama):** Transaksi dapat dilakukan 100% tanpa login. Form checkout langsung terbuka di landing page.
- **Login Opsional:** User dapat mendaftar/login secara opsional untuk mengakses dashboard histori transaksi dan menggunakan fitur "Pesan Lagi".
- **Register:** nama, email (unique), password (min 8 karakter). Dapat dilakukan di halaman khusus atau setelah checkout sukses.
- **Login:** email + password, opsi "Ingat Saya" (extend token).
- **Forgot Password:** kirim reset link via email (expire 60 menit).
- **Session:** Laravel Sanctum token untuk user terdaftar, disimpan sebagai **HttpOnly cookie** (bukan localStorage).
- **Protected Routes di Next.js:** Untuk halaman dashboard user, redirect ke login jika tidak ada session/token. Halaman order status dan landing page tetap terbuka publik.

#### FR-07 | Dashboard & Histori User 🟡

- Tabel riwayat transaksi: Kode Order, Game, Produk, Harga, Status, Tanggal
- Filter: status (semua/sukses/gagal/pending), range tanggal, game
- Pagination: 20 item per halaman
- Klik baris → modal/halaman detail order lengkap
- Tombol "Pesan Lagi" → pre-fill checkout form dengan produk dan game ID yang sama
- Export CSV histori transaksi user

---

### 4.2 Admin Dashboard Features

#### FR-08 | Autentikasi & Access Control Admin 🔴

- **Terisolasi:** Akun admin dicatat pada tabel terpisah `admin_users` (Model `AdminUser`), terpisah penuh dari tabel customer `users` untuk mencegah kebocoran hak akses.
- **Sanctum Multi-Guard:** Autentikasi API admin menggunakan token Sanctum yang divalidasi khusus terhadap guard/provider `admin_users`.
- **URL terpisah:** Halaman login admin terisolasi di `/admin/login` (frontend) dan tidak terekspos di navigasi publik.
- **Dua role:** `super_admin` (akses penuh), `operator` (view + manual retry, tidak bisa ubah produk/harga).
- **Brute force protection:** lockout 15 menit setelah 5 gagal login berturut-turut.
- **Activity log** wajib untuk setiap aksi: edit produk, ubah markup, manual retry, flag refund — log: `admin_id`, `action`, `old_value` (JSON), `new_value` (JSON), `ip_address`, `timestamp`.
- **IP Whitelist:** Opsi IP whitelist via `.env` (`ADMIN_ALLOWED_IPS`) untuk hardening akses route admin.

#### FR-09 | Manajemen Produk 🔴

- Tombol **"🔄 Sync Pricelist Digiflazz"**:
  - Fetch dari Digiflazz endpoint `price-list` (brand = game_topup)
  - Upsert ke tabel `products` berdasarkan `buyer_sku_code`
  - Produk baru otomatis set `is_active = false` (admin harus manually enable)
  - Log: berapa produk baru ditambah, berapa harga berubah
- Tabel produk: tampilkan `name`, `digiflazz_sku`, `base_price` (H2H cost — hanya admin yang lihat), `selling_price` (setelah markup), `margin` (Rp & %), `is_active`
- **Set markup per produk:**
  - Flat: `selling_price = base_price + markup_value` (dalam Rp)
  - Persen: `selling_price = base_price × (1 + markup_value / 100)`
  - Preview `selling_price` real-time sebelum save
- **Bulk set markup per game:** apply aturan markup yang sama ke semua produk dalam 1 game sekaligus
- Toggle `is_active` per produk (toggle switch di tabel)
- Bulk enable/disable berdasarkan game category (checkbox + action)
- Upload thumbnail per game category (simpan ke Cloudflare R2)

#### FR-10 | Monitor Transaksi 🔴

- Tabel transaksi real-time: auto-refresh 30 detik atau manual refresh button
- Kolom: Kode Order, Email, Game, Produk, Metode Bayar, Total, Status Bayar, Status Top-up, Waktu
- **Filter kombinasi:** status pembayaran, status top-up, range tanggal, game
- **Search:** order_code, email customer, game User ID
- Klik baris → **modal detail** berisi:
  - Semua field order
  - Raw request yang dikirim ke Digiflazz (JSON)
  - Raw response dari Digiflazz (JSON)
  - Timeline order_logs (setiap event dengan timestamp)
- Tombol **"🔁 Manual Retry"** (super_admin & operator): re-dispatch `ExecuteTopupJob` untuk order dengan `topup_status = failed` atau `processing`
- Tombol **"🚩 Flag Refund"**: set `refund_flagged_at`, form catatan refund. Tidak ada otomasi refund di v1 — proses manual via Midtrans dashboard
- Export hasil filter ke CSV

#### FR-11 | Analytics & Revenue Dashboard 🟡

- **Summary cards:** Revenue hari ini, 7 hari, 30 hari (dalam Rp)
- **Chart pendapatan:** line chart 30 hari (tanggal vs revenue harian), render via Recharts
- **Breakdown status:** pie chart atau bar — sukses / pending / gagal
- **Top 10 produk terlaris:** berdasarkan qty dalam 30 hari
- **Metrik kunci:** Average Order Value, Success Rate %
- Semua data dari agregasi database query, di-cache Redis dengan TTL 5 menit

#### FR-12 | Konfigurasi Platform 🟡

- **Widget saldo Digiflazz:** fetch dari Digiflazz balance endpoint, tampil di dashboard sidebar
- **Alert saldo rendah:** threshold configurable (default Rp 500.000), kirim email + Telegram bot jika saldo < threshold
- **Maintenance mode toggle:** saat aktif, semua halaman publik tampilkan halaman maintenance; admin masih bisa akses
- **Banner pengumuman:** editor teks (plain text, aman dari XSS) untuk ditampilkan di homepage
- **Toggle metode pembayaran:** enable/disable per metode (QRIS, GoPay, dll.) tanpa deploy ulang
- **Konfigurasi SMTP:** host, port, username, password via UI (disimpan terenkripsi di `site_settings`)
- Semua settings di tabel `site_settings` (key-value store)

---

## 5. Non-Functional Requirements

### 5.1 Performance

| Metrik | Target | Strategi |
|---|---|---|
| First Contentful Paint (Mobile) | < 1.5 detik | Next.js SSG + Vercel Edge CDN |
| Largest Contentful Paint | < 2.5 detik | Image WebP + lazy load + next/image |
| Time to Interactive | < 3.5 detik | Code splitting per route (App Router) |
| API P95 Response Time | < 500 ms | Index DB + query optimization |
| Webhook → Job Dispatch | < 1 detik | Queue consumer langsung consume |
| Job Dispatch → Digiflazz Submit | < 5 detik | Worker selalu standby (Supervisor) |
| DB Query P95 | < 100 ms | Composite index pada orders table |
| Google PageSpeed (Mobile) | ≥ 90 | Lighthouse audit wajib sebelum launch |

**Indeks database wajib di tabel `orders`:**
- `(order_code)` — UNIQUE INDEX
- `(user_id, created_at)` — histori user
- `(payment_status, topup_status, created_at)` — monitoring & filter admin
- `(digiflazz_ref_id)` — idempotency check

### 5.2 Reliability & Availability

- **Uptime target:** 99.5%/bulan (~3.6 jam downtime maksimum)
- **Webhook idempotency:** Setiap webhook yang masuk dicek terlebih dahulu — jika order sudah dalam status yang sama, return `200 OK` tanpa proses ulang
- **Queue persistence:** Redis dengan `appendfsync everysec` — job tidak hilang jika Redis restart
- **Dead Letter Queue:** Job yang gagal setelah 3 retry masuk ke tabel `failed_jobs` (Laravel bawaan). Monitoring wajib via admin alert
- **Graceful degradation:** Jika Digiflazz timeout → status `Pending` + scheduled retry, bukan langsung `Gagal`. User tidak tahu ada masalah sementara
- **Database backup:** Cron job backup MySQL ke Cloudflare R2 setiap jam 03.00, retensi 30 hari
- **Polling fallback:** Untuk kasus webhook Midtrans gagal deliver, cron job tiap 5 menit cek order dengan `payment_status = pending` dan `created_at > 15 menit` — query status ke Midtrans API

### 5.3 Security

| Aspek | Implementasi |
|---|---|
| Transport | HTTPS-only, TLS 1.2+, HSTS header `max-age=31536000` |
| Midtrans Webhook Validation | `SHA512(order_id + status_code + gross_amount + server_key)` — tolak jika tidak match |
| Digiflazz Webhook Validation | `MD5(username + api_key + trx_id)` — tolak jika tidak match |
| Public API Rate Limit | 60 req/menit per IP (Laravel throttle middleware) |
| Order Creation Rate Limit | 10 req/menit per IP (antisipasi spam order) |
| Admin Brute Force | Lockout 15 menit setelah 5 gagal login |
| Input Sanitization | Laravel Form Request Validation pada semua endpoint |
| SQL Injection Prevention | Laravel Eloquent ORM (parameterized queries — tidak ada raw query dengan input user) |
| XSS Prevention | Next.js built-in escaping + `Content-Security-Policy` header |
| CSRF | Tidak relevan untuk API-only backend. Frontend Next.js tidak render form PHP |
| Secrets Management | Semua kredensial di `.env`, tidak pernah di-commit ke VCS. `.gitignore` ketat |
| Log Redaction | Tidak log `password`, `api_key`, atau payment card details dalam plain text di log file |
| Admin IP Whitelist | Middleware cek `ADMIN_ALLOWED_IPS` dari `.env` (opsional, aktifkan di production) |

### 5.4 Scalability

- **Stateless API:** Tidak ada session state di memory server; auth via Sanctum token di database. Siap scale horizontal
- **Queue workers:** Jumlah Supervisor worker configurable via `.env` (`QUEUE_WORKERS=3`). Naik tanpa deploy ulang
- **Redis:** Satu instance cukup untuk 500 tx/hari. Siap upgrade ke Redis Cluster di masa depan
- **Database:** Siap tambah read replica untuk query analytics tanpa ganggu write path transaksi
- **CDN:** Static asset (gambar game, font) via Cloudflare. Frontend via Vercel Edge Network (99+ lokasi POP)

### 5.5 SEO

- Next.js SSG untuk semua halaman game kategori, regenerate via ISR setiap 1 jam
- `generateMetadata()` dinamis per halaman game dan produk
- JSON-LD structured data: `BreadcrumbList` di semua halaman, `Product` di halaman denominasi
- `sitemap.xml` auto-generate via `next-sitemap` (include semua halaman game, exclude `/admin`, `/api`)
- `robots.txt`: allow semua publik, disallow `/admin` dan `/api`
- Open Graph + Twitter Card meta tags untuk social sharing

---

## 6. Tech Stack & Architecture

### 6.1 Stack Summary

| Layer | Teknologi | Versi | Alasan Pemilihan |
|---|---|---|---|
| Frontend | Next.js App Router | 14+ | SSG/ISR untuk SEO, RSC untuk performa, App Router modern |
| Styling | Tailwind CSS | 3.x | Utility-first, Neobrutalism + Glassmorphism mudah diimplementasi |
| Data Fetching | SWR | 2.x | Order status polling + stale-while-revalidate pattern |
| Backend | Laravel | 13 (PHP 8.4+) | Mature, built-in Queue, mudah integrasi Digiflazz |
| Database | MySQL | 8.0 | Relational — integritas transaksional kritis untuk order |
| Cache & Queue | Redis | 7.x | Laravel Queue driver, rate limiting, analytics cache |
| Payment Gateway | Midtrans Snap | Latest | Market leader Indonesia, support semua metode yang diperlukan |
| H2H Provider | Digiflazz | H2H API | Coverage game luas, reliable, dokumentasi jelas |
| Email | Mailable + SMTP | — | Dev: Mailtrap. Prod: SendGrid (6.000 email/bulan gratis) |
| File Storage | Cloudflare R2 | — | Thumbnail game, CSV export, backup. Gratis 10 GB/bulan |
| Deploy Frontend | Vercel | — | Zero-config Next.js, CDN otomatis, free tier cukup untuk awal |
| Deploy Backend | VPS (Ubuntu 22.04) | — | Cost-effective, kontrol penuh. Pilihan: Contabo €5/bulan atau DigitalOcean $12/bulan |
| Web Server | Nginx + PHP-FPM | Latest stable | Standard production setup Laravel |
| Process Manager | Supervisor | — | Daemon untuk queue workers + auto-restart |
| SSL/TLS | Let's Encrypt | — | Auto-renew via Certbot |
| DNS & Proxy | Cloudflare | Free | WAF, DDoS protection, SSL termination, proxy ke VPS |
| Error Monitoring | Sentry | Free tier | Exception tracking + performance monitoring |

### 6.2 Arsitektur Sistem (Decoupled / Headless)

```
┌────────────────────────────────────────────────────────────────────┐
│                      USER BROWSER / MOBILE                          │
│                    (375px–1280px, 4G/WiFi)                          │
└──────────────────────────────┬─────────────────────────────────────┘
                               │  HTTPS
                               ▼
┌──────────────────────────────────────────────────────────────────┐
│              NEXT.JS FRONTEND  (Vercel + Edge CDN)                │
│                                                                    │
│   App Router     │  SSG: halaman game (ISR 1 jam)                 │
│   Tailwind CSS   │  CSR: checkout, order status                   │
│   SWR            │  Neobrutalism + Glassmorphism                  │
└──────────────────────────────┬───────────────────────────────────┘
                               │  REST API — HTTPS
                               │  Authorization: Bearer {token}
                               ▼
┌──────────────────────────────────────────────────────────────────┐
│           LARAVEL REST API  (VPS — Nginx + PHP-FPM)               │
│                                                                    │
│   /api/v1/*         │  Sanctum Token Auth                         │
│   /api/webhook/*    │  Form Request Validation                    │
│                     │  Rate Limiting (throttle)                   │
│                     │  Webhook Signature Validation               │
└────────┬─────────────────────────────────┬─────────────────────┘
         │                                 │
         ▼                                 ▼
┌────────────────┐              ┌─────────────────────────┐
│   MySQL 8.0    │              │       Redis 7.x          │
│                │              │                          │
│  users         │              │  Queue: default          │
│  games         │              │  ├── ExecuteTopupJob     │
│  products      │              │  ├── SendEmailJob        │
│  orders        │              │  └── RetryTopupJob       │
│  order_logs    │              │                          │
│  admin_users   │              │  Cache: analytics (5m)   │
│  site_settings │              │  Rate limit counters     │
└────────────────┘              └────────────┬────────────┘
                                             │
                                ┌────────────▼────────────┐
                                │   SUPERVISOR WORKERS     │
                                │   (3 proses paralel)     │
                                │                          │
                                │  php artisan queue:work  │
                                │  --queue=default         │
                                │  --tries=3               │
                                │  --backoff=30            │
                                └────────┬────────┬────────┘
                                         │        │
                               ┌─────────▼──┐  ┌──▼────────────────┐
                               │  MIDTRANS  │  │    DIGIFLAZZ       │
                               │  Payment   │  │    H2H Top-up      │
                               │  Gateway   │  │    API             │
                               └─────┬──────┘  └──────┬────────────┘
                                     │ Webhook         │ Webhook
                                     ▼                 ▼
                               POST /api/webhook/midtrans
                               POST /api/webhook/digiflazz
                               (signature validated, idempotent)
```

### 6.3 Webhook Flow (Critical Path)

```
[Midtrans Server]
      │
      │  POST /api/webhook/midtrans
      │  Body: { order_id, status_code, gross_amount, signature_key, ... }
      ▼
[Laravel WebhookController]
      │
      ├─ Validasi: SHA512(order_id + status_code + gross_amount + server_key) == signature_key
      ├─ Load order by order_code (= order_id dari Midtrans)
      ├─ Idempotency: jika payment_status sudah 'paid' → return 200 OK, stop
      ├─ Update orders: payment_status = 'paid', paid_at = now()
      ├─ Log ke order_logs: { event: 'payment_confirmed', payload: full_webhook_body }
      └─ Dispatch: ExecuteTopupJob::dispatch($order)->onQueue('default')

[Redis Queue Consumer — Worker Process]
      │
      ├─ Load order dari database
      ├─ Idempotency: jika topup_status != 'pending' → skip, return
      ├─ Update: topup_status = 'processing'
      │
      └─ POST ke Digiflazz Prepaid:
           { username, buyer_sku_code, customer_no, ref_id, sign }
                │
                ├── Response.status = "Sukses"
                │     ├─ Update: topup_status = 'completed', sn = response.sn
                │     ├─ Log ke order_logs
                │     └─ Dispatch: SendEmailJob (template sukses)
                │
                ├── Response.status = "Pending"
                │     ├─ Update: topup_status = 'processing' (tetap)
                │     ├─ Log ke order_logs
                │     └─ Dispatch: RetryTopupJob::dispatch($order)->delay(30s)
                │          (max 3 kali total, setelah itu → Gagal)
                │
                └── Response.status = "Gagal"
                      ├─ Update: topup_status = 'failed', failure_reason = response.message
                      ├─ Update: refund_flagged_at = now()
                      ├─ Log ke order_logs
                      └─ Dispatch: SendEmailJob (template gagal)
```

### 6.4 Database Schema Lengkap

```sql
-- =============================================
-- CORE TABLES
-- =============================================

CREATE TABLE users (
  id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name          VARCHAR(100) NOT NULL,
  email         VARCHAR(255) UNIQUE NOT NULL,
  password      VARCHAR(255) NOT NULL,  -- bcrypt hashed
  email_verified_at TIMESTAMP NULL,
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE games (
  id                   BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name                 VARCHAR(100) NOT NULL,
  slug                 VARCHAR(100) UNIQUE NOT NULL,
  thumbnail_url        VARCHAR(500),
  description          TEXT,
  id_field_label       VARCHAR(50) DEFAULT 'User ID',
  id_field_placeholder VARCHAR(100),
  zone_field_label     VARCHAR(50) DEFAULT 'Zone/Server ID',
  needs_zone           BOOLEAN DEFAULT FALSE,
  is_active            BOOLEAN DEFAULT TRUE,
  sort_order           SMALLINT DEFAULT 0,
  created_at           TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at           TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE products (
  id               BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  game_id          BIGINT UNSIGNED NOT NULL,
  digiflazz_sku    VARCHAR(100) UNIQUE NOT NULL,
  name             VARCHAR(200) NOT NULL,
  description      VARCHAR(500),
  base_price       DECIMAL(15,2) NOT NULL,    -- H2H cost (hanya admin yang lihat)
  selling_price    DECIMAL(15,2) NOT NULL,    -- harga jual ke customer
  markup_type      ENUM('flat','percent') DEFAULT 'flat',
  markup_value     DECIMAL(10,2) DEFAULT 0,
  is_active        BOOLEAN DEFAULT FALSE,     -- new products default inactive
  created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (game_id) REFERENCES games(id),
  INDEX idx_game_active (game_id, is_active)
);

CREATE TABLE orders (
  id                BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_code        VARCHAR(30) UNIQUE NOT NULL,  -- AZKA-YYYYMMDD-XXXXX
  user_id           BIGINT UNSIGNED NULL,          -- NULL = guest
  product_id        BIGINT UNSIGNED NOT NULL,
  customer_no       VARCHAR(100) NOT NULL,         -- Game User ID
  zone_id           VARCHAR(50) NULL,              -- Zone/Server ID (opsional)
  email             VARCHAR(255) NOT NULL,
  phone             VARCHAR(20) NULL,
  selling_price     DECIMAL(15,2) NOT NULL,        -- price-locked saat order dibuat
  payment_method    VARCHAR(50),                   -- qris, gopay, dana, shopeepay, bca_va, bni_va, mandiri_bill
  payment_status    ENUM('pending','paid','expired','failed','cancelled') DEFAULT 'pending',
  topup_status      ENUM('pending','processing','completed','failed') DEFAULT 'pending',
  midtrans_order_id VARCHAR(100),
  midtrans_snap_token VARCHAR(500),
  digiflazz_ref_id  VARCHAR(100),                 -- = order_code
  digiflazz_sn      VARCHAR(500) NULL,             -- serial number jika ada
  failure_reason    TEXT NULL,
  paid_at           TIMESTAMP NULL,
  completed_at      TIMESTAMP NULL,
  refund_flagged_at TIMESTAMP NULL,
  refund_notes      TEXT NULL,
  created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  FOREIGN KEY (product_id) REFERENCES products(id),
  INDEX idx_order_code (order_code),
  INDEX idx_user_created (user_id, created_at),
  INDEX idx_status_monitor (payment_status, topup_status, created_at),
  INDEX idx_digiflazz_ref (digiflazz_ref_id)
);

CREATE TABLE order_logs (
  id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id   BIGINT UNSIGNED NOT NULL,
  event      VARCHAR(100) NOT NULL,
  payload    JSON,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (order_id) REFERENCES orders(id),
  INDEX idx_order_id (order_id)
);

-- =============================================
-- ADMIN TABLES
-- =============================================

CREATE TABLE admin_users (
  id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name          VARCHAR(100) NOT NULL,
  email         VARCHAR(255) UNIQUE NOT NULL,
  password      VARCHAR(255) NOT NULL,
  role          ENUM('super_admin','operator') DEFAULT 'operator',
  last_login_at TIMESTAMP NULL,
  is_active     BOOLEAN DEFAULT TRUE,
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE admin_activity_logs (
  id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  admin_id    BIGINT UNSIGNED NOT NULL,
  action      VARCHAR(100) NOT NULL,
  target_type VARCHAR(100),        -- 'product', 'order', 'setting', dll.
  target_id   BIGINT UNSIGNED NULL,
  old_value   JSON NULL,
  new_value   JSON NULL,
  ip_address  VARCHAR(45),
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (admin_id) REFERENCES admin_users(id)
);

CREATE TABLE site_settings (
  id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  key        VARCHAR(100) UNIQUE NOT NULL,
  value      TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Default site_settings records (seed):
-- maintenance_mode = 0
-- announcement_banner = ''
-- digiflazz_balance_alert_threshold = 500000
-- payment_methods_enabled = {"qris":1,"gopay":1,"dana":1,"shopeepay":1,"bca_va":1,"bni_va":1,"mandiri_bill":1}
```

### 6.5 API Endpoint Registry

**Public Endpoints (no auth required)**

| Method | Endpoint | Deskripsi | Rate Limit |
|---|---|---|---|
| GET | `/api/v1/games` | List semua game aktif | 60/menit |
| GET | `/api/v1/games/{slug}/products` | List produk aktif per game | 60/menit |
| POST | `/api/v1/orders` | Buat order baru (guest/auth) | 10/menit |
| GET | `/api/v1/orders/{order_code}` | Status order (polling-safe) | 60/menit |
| POST | `/api/v1/auth/register` | Daftar user baru | 5/menit |
| POST | `/api/v1/auth/login` | Login user | 10/menit |
| POST | `/api/v1/auth/forgot-password` | Kirim reset email | 3/menit |
| POST | `/api/v1/auth/reset-password` | Reset password dengan token | 5/menit |

**Authenticated User Endpoints (Sanctum token required)**

| Method | Endpoint | Deskripsi |
|---|---|---|
| POST | `/api/v1/auth/logout` | Logout (revoke token) |
| GET | `/api/v1/user/orders` | Histori transaksi user (paginated) |
| GET | `/api/v1/user/orders/{order_code}` | Detail order milik user |

**Webhook Endpoints (signature-protected, no rate limit by user IP)**

| Method | Endpoint | Validator |
|---|---|---|
| POST | `/api/webhook/midtrans` | SHA512 signature check |
| POST | `/api/webhook/digiflazz` | MD5 signature check |

**Admin Endpoints (Bearer token + role check)**

| Method | Endpoint | Role | Deskripsi |
|---|---|---|---|
| POST | `/api/v1/admin/auth/login` | — | Admin login |
| GET | `/api/v1/admin/products` | Operator+ | List produk dengan base_price |
| PUT | `/api/v1/admin/products/{id}` | Super Admin | Update markup, is_active |
| POST | `/api/v1/admin/products/sync` | Super Admin | Sync pricelist Digiflazz |
| PUT | `/api/v1/admin/products/bulk-status` | Super Admin | Bulk enable/disable |
| GET | `/api/v1/admin/orders` | Operator+ | List transaksi + filter |
| GET | `/api/v1/admin/orders/{id}` | Operator+ | Detail order + logs |
| POST | `/api/v1/admin/orders/{id}/retry` | Operator+ | Manual retry top-up |
| POST | `/api/v1/admin/orders/{id}/flag-refund` | Operator+ | Flag order untuk refund |
| GET | `/api/v1/admin/analytics/summary` | Operator+ | Dashboard stats |
| GET | `/api/v1/admin/balance` | Operator+ | Saldo Digiflazz |
| GET/PUT | `/api/v1/admin/settings` | Super Admin | Site settings |

### 6.6 UI/UX Design System

**Design Language: Neobrutalism × Glassmorphism Fusion**

Platform menggunakan pendekatan gabungan dua gaya yang kontras namun saling melengkapi:

---

**Neobrutalism** (diaplikasikan pada elemen action & struktur):

```css
/* Card Neobrutalist */
.neo-card {
  border: 2px solid #0F0F0F;
  box-shadow: 4px 4px 0px #0F0F0F;
  background: #FFFFFF;
}

/* Hover effect — tombol bergeser */
.neo-button:hover {
  transform: translate(-2px, -2px);
  box-shadow: 6px 6px 0px #0F0F0F;
}

/* Tailwind equivalents */
/* border-2 border-black shadow-[4px_4px_0px_#000] */
/* hover:-translate-x-0.5 hover:-translate-y-0.5 hover:shadow-[6px_6px_0px_#000] */
```

Elemen Neobrutalism: produk denomination cards, CTA buttons ("Beli Sekarang", "Bayar"), form inputs, badge status

---

**Glassmorphism** (diaplikasikan pada background card & overlay):

```css
/* Glass Card */
.glass-card {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(12px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 12px;
}

/* Tailwind equivalents */
/* bg-white/10 backdrop-blur-md border border-white/20 rounded-xl */
```

Elemen Glassmorphism: game category cards di hero section, order summary card, payment method selector overlay, modal backdrop

---

**Color System:**

| Token | Hex | Penggunaan |
|---|---|---|
| `primary` | `#FF6B00` | CTA button, active state, badge "Tersedia" |
| `accent` | `#FFD700` | Harga, highlight, border active card |
| `success` | `#22C55E` | Status sukses |
| `error` | `#EF4444` | Status gagal, error form |
| `warning` | `#F59E0B` | Status pending, alert |
| `dark` | `#0F0F0F` | Border Neobrutalism, teks utama |
| `light` | `#F9FAFB` | Background page |

**Context Rule:**
- **Homepage hero:** dark gradient background + glassmorphism cards → immersive gaming atmosphere
- **Game listing & checkout:** light background + neobrutalist cards → maximum clarity dan contrast
- **Admin dashboard:** minimal light UI dengan accent orange, no glassmorphism

**Typography:**
- Heading: `font-black uppercase tracking-tight` (font: Inter atau Space Grotesk)
- Body: `font-medium` (font: Inter, size 16px base)
- Price: `font-bold text-accent` (besar, menonjol)

---

### 6.7 Backend Folder Structure (Repository & Service Pattern)

Struktur direktori di bawah folder `be/` didesain menggunakan **Repository Pattern + Service Layer** untuk menjaga kode tetap modular, testable, dan bersih.

```
be/ (Laravel 13)
├── app/
│   ├── Contracts/
│   │   └── Interfaces/
│   │       ├── data/
│   │       │   ├── GameInterface.php            # Kontrak Repository Game
│   │       │   ├── ProductInterface.php         # Kontrak Repository Produk
│   │       │   ├── OrderInterface.php           # Kontrak Repository Order
│   │       │   ├── OrderLogInterface.php        # Kontrak Repository OrderLog
│   │       │   ├── AdminUserInterface.php       # Kontrak Repository AdminUser
│   │       │   └── SiteSettingInterface.php     # Kontrak Repository SiteSetting
│   │       └── Eloquent/
│   │           ├── PaginateInterface.php        # Kontrak pagination
│   │           └── SearchInterface.php          # Kontrak search query
│   ├── Repositories/
│   │   ├── GameRepository.php                   # Implementasi Eloquent Game
│   │   ├── ProductRepository.php                # Implementasi Eloquent Produk
│   │   ├── OrderRepository.php                  # Implementasi Eloquent Order
│   │   ├── OrderLogRepository.php               # Implementasi Eloquent OrderLog
│   │   ├── AdminUserRepository.php              # Implementasi Eloquent AdminUser
│   │   └── SiteSettingRepository.php            # Implementasi Eloquent SiteSetting
│   ├── Services/
│   │   ├── GameService.php                      # Logika bisnis Game & Kategori
│   │   ├── ProductService.php                   # Logika bisnis produk & markup (Sync Digiflazz)
│   │   ├── OrderService.php                     # Logika bisnis checkout & status polling
│   │   ├── AdminService.php                     # Logika bisnis untuk monitoring & settings admin
│   │   ├── DigiflazzService.php                 # Integrasi API H2H Digiflazz
│   │   └── MidtransService.php                  # Integrasi Payment Gateway Midtrans
│   ├── Enums/
│   │   ├── PaymentStatusEnum.php                # Enum status bayar: pending, paid, dll.
│   │   ├── TopupStatusEnum.php                  # Enum status topup: pending, completed, dll.
│   │   ├── MarkupTypeEnum.php                   # Enum tipe markup: flat, percent
│   │   └── AdminRoleEnum.php                    # Enum role admin: super_admin, operator
│   ├── Helpers/
│   │   └── ResponseHelpers.php                  # Format standar response JSON API
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Customer/
│   │   │   │   ├── GameController.php           # Memanggil GameService
│   │   │   │   ├── OrderController.php          # Memanggil OrderService
│   │   │   │   ├── AuthController.php           # Auth customer
│   │   │   │   └── ProfileController.php        # Histori order customer
│   │   │   ├── Admin/
│   │   │   │   ├── AdminAuthController.php      # Auth admin
│   │   │   │   ├── AdminProductController.php   # Edit markup, sync product
│   │   │   │   ├── AdminOrderController.php     # Monitor order, retry, flag refund
│   │   │   │   └── AdminSettingController.php   # Dynamic settings
│   │   │   ├── Webhook/
│   │   │   │   ├── MidtransWebhookController.php# Callback Midtrans -> panggil OrderService
│   │   │   │   └── DigiflazzWebhookController.php# Callback Digiflazz -> panggil OrderService
│   │   │   └── Controller.php
│   │   ├── Requests/
│   │   │   ├── StoreOrderRequest.php            # Validasi input order
│   │   │   ├── LoginRequest.php                 # Validasi input login
│   │   │   └── UpdateProductRequest.php         # Validasi input edit markup
│   │   └── Resources/
│   │       ├── GameResource.php                 # API Resource format response Game
│   │       ├── ProductResource.php              # API Resource format response Produk
│   │       ├── OrderResource.php                # API Resource format response Order
│   │       ├── OrderLogResource.php             # API Resource format response Timeline Order
│   │       └── AdminUserResource.php            # API Resource format response Admin
│   ├── Models/
│   │   ├── User.php
│   │   ├── Game.php
│   │   ├── Product.php
│   │   ├── Order.php
│   │   ├── OrderLog.php
│   │   ├── AdminUser.php
│   │   ├── AdminActivityLog.php
│   │   └── SiteSetting.php
│   └── Providers/
│       └── AppServiceProvider.php               # Bind Interface ke Repository Eloquent
```

---

## 7. Milestones & Development Phases

> **Asumsi:** Solo developer, ~8 jam/hari, 5 hari/minggu. Total: **8 minggu / 40 hari kerja**.
> Provider: Digiflazz (sandbox aktif) + Midtrans (sandbox aktif).

---

### Phase 0 — Setup & Foundation `Hari 1–5`

**Goal:** Semua infrastruktur dan scaffolding siap, tidak ada hambatan di fase berikutnya.

| # | Task | Hari |
|---|---|---|
| 0.1 | Git setup: struktur repo (monorepo atau 2 repo), `.gitignore`, branch strategy (`main`, `develop`, `feature/*`) | 0.5 |
| 0.2 | Laravel 13 scaffold: `laravel new azka-backend`, konfigurasi `.env`, base middleware stack | 1.0 |
| 0.3 | Next.js 14 scaffold: `create-next-app`, App Router, Tailwind CSS 3, konfigurasi `tailwind.config.js` dengan custom tokens | 1.0 |
| 0.4 | Docker Compose local: MySQL 8 + Redis 7 + phpMyAdmin. Validasi koneksi dari Laravel | 0.5 |
| 0.5 | Laravel API foundation: prefix `/api/v1/`, base JSON response format (`success`, `data`, `message`, `errors`), Exception Handler | 0.5 |
| 0.6 | Daftar + aktivasi akun: **Digiflazz sandbox** dan **Midtrans sandbox**. Simpan semua kredensial | 0.5 |
| 0.7 | GitHub Actions CI dasar: PHP lint (phpstan) + Jest/ESLint pada setiap push ke `develop` | 0.5 |

**Exit criteria:** Laravel `api/ping` mengembalikan 200. Next.js homepage render. Kredensial sandbox tersedia di `.env`.

---

### Phase 1 — Backend Core `Hari 6–18`

**Goal:** Semua business logic API berjalan dan ter-unit-test. Tidak bergantung pada third-party.

| # | Task | Hari |
|---|---|---|
| 1.1 | Database migrations: semua tabel sesuai schema section 6.4 | 1.0 |
| 1.2 | Eloquent models: `Game`, `Product`, `Order`, `OrderLog`, `User`. Relationships + fillable + casts | 1.0 |
| 1.3 | Seeders: 3 game sample + 5 produk per game (harga dummy), 1 admin user | 0.5 |
| 1.4 | Public API: `GET /games`, `GET /games/{slug}/products` | 0.5 |
| 1.5 | Order creation `POST /orders`: Form Request validation, price-lock logic, generate `order_code`, simpan `midtrans_snap_token` placeholder | 1.5 |
| 1.6 | User Auth via Laravel Sanctum: register, login, logout, forgot password, reset password | 1.0 |
| 1.7 | Order status API `GET /orders/{order_code}`: response untuk polling frontend | 0.5 |
| 1.8 | Laravel Queue: konfigurasi Redis driver di `.env`, test `artisan queue:work` | 0.5 |
| 1.9 | Job classes (stub — belum ada logic): `ExecuteTopupJob`, `RetryTopupJob`, `SendEmailJob` | 0.5 |
| 1.10 | Mailable classes: `OrderSuccessMail`, `OrderFailedMail` — HTML template responsif, preview di Mailtrap | 1.0 |
| 1.11 | Admin API: `GET/PUT /admin/products`, `PUT /admin/products/bulk-status`, `GET /admin/orders` (paginated + filter) | 1.5 |
| 1.12 | Unit tests (Pest): price calculation, markup logic, `order_code` format, idempotency check | 1.0 |

**Exit criteria:** Semua endpoint tertest via Postman collection. Unit test 100% pass. Queue worker berjalan. Email template preview benar di Mailtrap.

---

### Phase 2 — Third-Party Integration `Hari 19–30`

**Goal:** Alur pembayaran → top-up berjalan otomatis end-to-end di lingkungan sandbox.

| # | Task | Hari |
|---|---|---|
| 2.1 | Digiflazz service class: `DigiflazzService` dengan method `fetchPricelist()`, `createTransaction()`, `checkBalance()` | 1.0 |
| 2.2 | Admin endpoint `POST /admin/products/sync`: panggil `fetchPricelist()`, upsert ke DB, return summary (new/updated/unchanged) | 1.0 |
| 2.3 | `ExecuteTopupJob` implementation: load order, cek idempotency via `digiflazz_ref_id`, POST ke Digiflazz, handle semua status | 1.5 |
| 2.4 | `RetryTopupJob`: re-dispatch `ExecuteTopupJob` dengan counter, max 3 retry, setelah itu set `topup_status = failed` | 0.5 |
| 2.5 | Digiflazz webhook endpoint `POST /api/webhook/digiflazz`: MD5 signature validation, update order status final | 1.0 |
| 2.6 | Midtrans service class: `MidtransService` dengan method `createSnapToken()`, `checkTransactionStatus()` | 1.0 |
| 2.7 | Update `POST /orders`: generate Midtrans Snap token via `createSnapToken()`, simpan ke order | 0.5 |
| 2.8 | Midtrans webhook endpoint `POST /api/webhook/midtrans`: SHA512 validation, idempotency, update payment status, dispatch `ExecuteTopupJob` | 1.0 |
| 2.9 | Polling fallback cron: `artisan schedule:run` cek order `payment_status=pending` yang expired, query Midtrans API untuk status terkini | 0.5 |
| 2.10 | `SendEmailJob` implementation: connect ke Mailable classes, kirim email sukses/gagal | 0.5 |
| 2.11 | Admin endpoint: `GET /admin/balance`, `POST /admin/orders/{id}/retry`, `POST /admin/orders/{id}/flag-refund` | 1.0 |
| 2.12 | **Integration test end-to-end (sandbox):** buat order → simulasi webhook Midtrans → cek ExecuteTopupJob ter-dispatch → cek email terkirim | 1.5 |

**Exit criteria:** End-to-end flow sukses di sandbox. Webhook dua arah (Midtrans + Digiflazz) validated. Retry logic berfungsi. Email terkirim.

---

### Phase 3 — Frontend Customer-Facing `Hari 31–44`

**Goal:** Semua halaman customer berjalan di staging, mobile-first, checkout flow functional.

| # | Task | Hari |
|---|---|---|
| 3.1 | Design system setup: extend `tailwind.config.js` dengan custom color tokens, shadow tokens, base components: `Button`, `Card`, `Badge`, `Input`, `Modal` | 1.0 |
| 3.2 | Layout komponen: `Navbar`, `Footer`, `MobileNav` | 0.5 |
| 3.3 | Homepage (SSG): hero section (Glassmorphism dark), game category grid (SSG dari API), "Produk Populer" section | 1.5 |
| 3.4 | Halaman game SSG `/topup/[slug]`: product listing, denomination cards (Neobrutalism) | 1.5 |
| 3.5 | Komponen form order: input Game User ID (+ Zone ID conditional), email input, validasi client-side, panduan User ID modal | 1.0 |
| 3.6 | Halaman checkout: order summary card (Glassmorphism), payment method selector, tombol "Bayar Sekarang" | 1.0 |
| 3.7 | Integrasi Midtrans Snap.js: load script `midtrans-js`, buka popup on button click, handle callback `onSuccess`, `onPending`, `onError`, `onClose` | 1.0 |
| 3.8 | Halaman Menunggu Pembayaran: countdown timer, instruksi pembayaran per metode, tombol "Batalkan" | 1.0 |
| 3.9 | Halaman status order `/order/[order_code]`: polling SWR setiap 5 detik, status badges dengan animasi, hentikan polling saat final state | 1.5 |
| 3.10 | Halaman sukses & gagal: tampilan detail lengkap, SN display, tombol "Pesan Lagi" | 0.5 |
| 3.11 | Auth pages: login, register, forgot password, reset password | 1.0 |
| 3.12 | Dashboard user: tabel histori (filter + pagination), detail modal, tombol "Pesan Lagi", export CSV | 1.5 |
| 3.13 | SEO: `generateMetadata()` per halaman game, sitemap.xml via `next-sitemap`, JSON-LD breadcrumb | 0.5 |
| 3.14 | Mobile responsiveness audit menyeluruh di 375px, 768px, 1280px. Fix semua overflow/layout issue | 1.0 |

**Exit criteria:** Semua halaman render benar di mobile & desktop. Checkout flow functional end-to-end di staging. Lighthouse mobile score ≥ 85.

---

### Phase 4 — Admin Dashboard `Hari 45–52`

**Goal:** Admin dapat mengelola produk, memantau transaksi, dan melihat analytics secara mandiri.

| # | Task | Hari |
|---|---|---|
| 4.1 | Admin auth: login page `/admin/login`, protected route wrapper, role-based component rendering | 0.5 |
| 4.2 | Layout admin: sidebar navigasi, topbar dengan nama admin + logout | 0.5 |
| 4.3 | Dashboard overview: revenue cards (hari ini, 7 hari, 30 hari), jumlah transaksi per status, success rate | 1.0 |
| 4.4 | Chart pendapatan 30 hari: Recharts `LineChart`, data dari `/admin/analytics/summary` | 0.5 |
| 4.5 | Halaman manajemen produk: tabel dengan base_price (tersembunyi dari publik), toggle is_active, form set markup inline, tombol Sync, bulk action | 2.0 |
| 4.6 | Halaman monitor transaksi: tabel dengan filter kombinasi, pagination, search, detail modal lengkap (payload + logs), tombol Manual Retry & Flag Refund | 1.5 |
| 4.7 | Widget saldo Digiflazz di sidebar/dashboard, badge warning jika < threshold | 0.5 |
| 4.8 | Halaman settings: maintenance toggle, banner editor, toggle metode bayar | 0.5 |

**Exit criteria:** Admin dapat sync produk dari Digiflazz, set markup, filter dan lihat detail transaksi, trigger manual retry — semua via UI tanpa akses langsung ke database.

---

### Phase 5 — Hardening, QA & Deployment `Hari 53–60`

**Goal:** Platform live di production, aman, monitored, dan siap menerima transaksi nyata.

| # | Task | Hari |
|---|---|---|
| 5.1 | Security review: pastikan semua webhook signature ter-enforce, rate limiting aktif di semua public endpoint, HTTPS header (HSTS, CSP, X-Frame-Options) | 1.0 |
| 5.2 | Error monitoring: setup Sentry (Laravel + Next.js), alert ke email/Telegram untuk exception `critical` dan `error` | 0.5 |
| 5.3 | Performance audit: Lighthouse mobile, API response benchmark (Postman runner atau k6), fix bottleneck | 0.5 |
| 5.4 | VPS setup: install Nginx, PHP 8.4, PHP-FPM, Composer, Node.js, Redis. Konfigurasi Nginx virtual host. Supervisor untuk queue worker (3 proses) | 1.0 |
| 5.5 | Let's Encrypt SSL via Certbot + auto-renew. Cloudflare DNS proxy ke VPS. Konfigurasi WAF rules dasar | 0.5 |
| 5.6 | Vercel deployment: push frontend ke Vercel, set semua env vars production, validasi build berhasil | 0.5 |
| 5.7 | Automated backup: bash script cron job (crontab) backup MySQL ke Cloudflare R2 jam 03.00, retensi 30 hari | 0.5 |
| 5.8 | Smoke testing: 20 transaksi end-to-end via sandbox (berbagai game, metode bayar, skenario: sukses, pending, gagal) | 0.5 |
| 5.9 | **Go-live checklist:** switch `.env` ke Midtrans production + Digiflazz production. Verifikasi saldo Digiflazz cukup. Matikan sandbox mode | 0.5 |
| 5.10 | Monitoring 24 jam pertama pasca-launch: pantau queue worker, error log Sentry, saldo Digiflazz, transaksi pertama | 0.5 |

**Exit criteria:** Platform live di URL production. Semua smoke test sukses. Monitoring aktif. Transaksi pertama real berhasil diproses.

---

### Ringkasan Timeline

| Phase | Hari | Fokus | Output Utama |
|---|---|---|---|
| **0** — Setup | 1–5 | Infrastruktur & scaffold | Kedua app running, sandbox aktif |
| **1** — Backend Core | 6–18 | Models, APIs, Auth, Queue | Semua endpoint dasar tested |
| **2** — Integration | 19–30 | Midtrans + Digiflazz + Webhook | E2E sandbox berfungsi |
| **3** — Frontend | 31–44 | UI customer, checkout, status | Mobile-first UI live di staging |
| **4** — Admin | 45–52 | Dashboard, produk, analytics | Admin operasional penuh |
| **5** — Deployment | 53–60 | Security, QA, go-live | 🚀 Production live |

---

### Risk Register

| Risiko | Impact | Probabilitas | Mitigasi |
|---|---|---|---|
| Digiflazz sandbox tidak akurat | Medium | Tinggi | Test dengan transaksi real nominal terkecil (Rp 1.000) 1 minggu sebelum launch |
| Midtrans webhook gagal delivery | High | Rendah | Polling fallback via cron job + tombol manual retry di admin |
| Queue worker crash / job stuck | High | Menengah | Supervisor auto-restart, `failed_jobs` monitoring, Telegram alert |
| Saldo Digiflazz habis tengah hari | High | Menengah | Low-balance alert (email + Telegram) di threshold Rp 500.000 |
| Scope creep menggeser deadline | High | Tinggi | Lock semua fitur v1 setelah Phase 0. Semua ide baru masuk backlog v2 |
| Digiflazz API rate limiting | Medium | Rendah | Job dispatch sequential per order. Tidak ada bulk-concurrent dispatch |
| VPS down saat traffic tinggi | High | Rendah | Cloudflare proxy (buffer), queue-based decoupling (order tidak hilang), backup restore procedure terdokumentasi |

---

### Backlog v2 (Post-Launch)

Fitur-fitur berikut **tidak** masuk scope v1 namun sudah dipertimbangkan dalam arsitektur sehingga tidak perlu refactor besar untuk menambahkannya:

- Loyalty point / cashback system
- Multi-provider failover (tambah Apigames sebagai backup Digiflazz)
- Reseller dashboard dengan markup tier
- Notifikasi WhatsApp otomatis via Fonnte/WA API
- Live chat support (Tawk.to integration)
- Mobile app native (React Native dengan API yang sama)
- Webhook retry dashboard dengan replay capability
- A/B testing pricing markup

---

*AZKA TOP UP — PRD v1.0 | Juni 2026*
*Tech: Next.js 14 + Laravel 13 + MySQL + Redis | Provider: Digiflazz + Midtrans*