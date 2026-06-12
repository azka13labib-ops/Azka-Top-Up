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
