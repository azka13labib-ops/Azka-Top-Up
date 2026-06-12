## 7. Backend Milestones & Development Phases

> **Asumsi:** Solo developer, ~8 jam/hari, 5 hari/minggu.
> Provider: Digiflazz (sandbox aktif) + Midtrans (sandbox aktif).

---

### Phase 0 — Setup & Foundation `Hari 1–5`

**Goal:** Semua infrastruktur dan scaffolding siap, tidak ada hambatan di fase berikutnya.

| Status | # | Task | Hari |
| :---: |---|---|---|
| [ ] | 0.1 | Git setup: struktur repo (monorepo atau 2 repo), `.gitignore`, branch strategy (`main`, `develop`, `feature/*`) | 0.5 |
| [ ] | 0.2 | Laravel 13 scaffold: `laravel new azka-backend`, konfigurasi `.env`, base middleware stack | 1.0 |
| [ ] | 0.4 | Docker Compose local: MySQL 8 + Redis 7 + phpMyAdmin. Validasi koneksi dari Laravel | 0.5 |
| [ ] | 0.5 | Laravel API foundation: prefix `/api/v1/`, base JSON response format (`success`, `data`, `message`, `errors`), Exception Handler | 0.5 |
| [ ] | 0.6 | Daftar + aktivasi akun: **Digiflazz sandbox** dan **Midtrans sandbox**. Simpan semua kredensial | 0.5 |
| [ ] | 0.7 | GitHub Actions CI dasar: PHP lint (phpstan) pada setiap push ke `develop` | 0.5 |

**Exit criteria:** Laravel `api/ping` mengembalikan 200. Kredensial sandbox tersedia di `.env`.

---

### Phase 1 — Backend Core `Hari 6–18`

**Goal:** Semua business logic API berjalan dan ter-unit-test. Tidak bergantung pada third-party.

| Status | # | Task | Hari |
| :---: |---|---|---|
| [x] | 1.1 | Database migrations: semua tabel sesuai schema section 6.4 | 1.0 |
| [x] | 1.2 | Eloquent models: `Game`, `Product`, `Order`, `OrderLog`, `User`. Relationships + fillable + casts | 1.0 |
| [x] | 1.3 | Seeders: 3 game sample + 5 produk per game (harga dummy), 1 admin user | 0.5 |
| [x] | 1.4 | Public API: `GET /games`, `GET /games/{slug}/products` | 0.5 |
| [x] | 1.5 | Order creation `POST /orders`: Form Request validation, price-lock logic, generate `order_code`, simpan `midtrans_snap_token` placeholder | 1.5 |
| [x] | 1.6 | User Auth via Laravel Sanctum: register, login, logout, forgot password, reset password | 1.0 |
| [x] | 1.7 | Order status API `GET /orders/{order_code}`: response untuk polling frontend | 0.5 |
| [x] | 1.8 | Laravel Queue: konfigurasi Redis driver di `.env`, test `artisan queue:work` | 0.5 |
| [x] | 1.9 | Job classes (stub — belum ada logic): `ExecuteTopupJob`, `RetryTopupJob`, `SendEmailJob` | 0.5 |
| [x] | 1.10 | Mailable classes: `OrderSuccessMail`, `OrderFailedMail` — HTML template responsif, preview di Mailtrap | 1.0 |
| [x] | 1.11 | Admin API: `GET/PUT /admin/products`, `PUT /admin/products/bulk-status`, `GET /admin/orders` (paginated + filter) | 1.5 |
| [x] | 1.12 | Unit tests (Pest): price calculation, markup logic, `order_code` format, idempotency check | 1.0 |

**Exit criteria:** Semua endpoint tertest via Postman collection. Unit test 100% pass. Queue worker berjalan. Email template preview benar di Mailtrap.

---

### Phase 2 — Third-Party Integration `Hari 19–30`

**Goal:** Alur pembayaran → top-up berjalan otomatis end-to-end di lingkungan sandbox.

| Status | # | Task | Hari |
| :---: |---|---|---|
| [ ] | 2.1 | Digiflazz service class: `DigiflazzService` dengan method `fetchPricelist()`, `createTransaction()`, `checkBalance()` | 1.0 |
| [ ] | 2.2 | Admin endpoint `POST /admin/products/sync`: panggil `fetchPricelist()`, upsert ke DB, return summary (new/updated/unchanged) | 1.0 |
| [ ] | 2.3 | `ExecuteTopupJob` implementation: load order, cek idempotency via `digiflazz_ref_id`, POST ke Digiflazz, handle semua status | 1.5 |
| [ ] | 2.4 | `RetryTopupJob`: re-dispatch `ExecuteTopupJob` dengan counter, max 3 retry, setelah itu set `topup_status = failed` | 0.5 |
| [ ] | 2.5 | Digiflazz webhook endpoint `POST /api/webhook/digiflazz`: MD5 signature validation, update order status final | 1.0 |
| [ ] | 2.6 | Midtrans service class: `MidtransService` dengan method `createSnapToken()`, `checkTransactionStatus()` | 1.0 |
| [ ] | 2.7 | Update `POST /orders`: generate Midtrans Snap token via `createSnapToken()`, simpan ke order | 0.5 |
| [ ] | 2.8 | Midtrans webhook endpoint `POST /api/webhook/midtrans`: SHA512 validation, idempotency, update payment status, dispatch `ExecuteTopupJob` | 1.0 |
| [ ] | 2.9 | Polling fallback cron: `artisan schedule:run` cek order `payment_status=pending` yang expired, query Midtrans API untuk status terkini | 0.5 |
| [ ] | 2.10 | `SendEmailJob` implementation: connect ke Mailable classes, kirim email sukses/gagal | 0.5 |
| [ ] | 2.11 | Admin endpoint: `GET /admin/balance`, `POST /admin/orders/{id}/retry`, `POST /admin/orders/{id}/flag-refund` | 1.0 |
| [ ] | 2.12 | **Integration test end-to-end (sandbox):** buat order → simulasi webhook Midtrans → cek ExecuteTopupJob ter-dispatch → cek email terkirim | 1.5 |

**Exit criteria:** End-to-end flow sukses di sandbox. Webhook dua arah (Midtrans + Digiflazz) validated. Retry logic berfungsi. Email terkirim.

---

### Phase 5 — Hardening, QA & Deployment `Hari 53–60`

**Goal:** Platform live di production, aman, monitored, dan siap menerima transaksi nyata.

| Status | # | Task | Hari |
| :---: |---|---|---|
| [ ] | 5.1 | Security review: pastikan semua webhook signature ter-enforce, rate limiting aktif di semua public endpoint, HTTPS header (HSTS, CSP, X-Frame-Options) | 1.0 |
| [ ] | 5.2 | Error monitoring: setup Sentry (Laravel), alert ke email/Telegram untuk exception `critical` dan `error` | 0.5 |
| [ ] | 5.3 | Performance audit: API response benchmark (Postman runner or k6), fix bottleneck | 0.5 |
| [ ] | 5.4 | VPS setup: install Nginx, PHP 8.4, PHP-FPM, Composer, Node.js, Redis. Konfigurasi Nginx virtual host. Supervisor untuk queue worker (3 proses) | 1.0 |
| [ ] | 5.5 | Let's Encrypt SSL via Certbot + auto-renew. Cloudflare DNS proxy ke VPS. Konfigurasi WAF rules dasar | 0.5 |
| [ ] | 5.7 | Automated backup: bash script cron job (crontab) backup MySQL ke Cloudflare R2 jam 03.00, retensi 30 hari | 0.5 |
| [ ] | 5.8 | Smoke testing: 20 transaksi end-to-end via sandbox (berbagai game, metode bayar, skenario: sukses, pending, gagal) | 0.5 |
| [ ] | 5.9 | **Go-live checklist:** switch `.env` ke Midtrans production + Digiflazz production. Verifikasi saldo Digiflazz cukup. Matikan sandbox mode | 0.5 |
| [ ] | 5.10 | Monitoring 24 jam pertama pasca-launch: pantau queue worker, error log Sentry, saldo Digiflazz, transaksi pertama | 0.5 |

**Exit criteria:** Backend live di VPS production. Semua smoke test sukses. Monitoring aktif. Transaksi pertama real berhasil diproses.

---

*AZKA TOP UP — PRD v1.0 | Juni 2026*
*Tech: Next.js 14 + Laravel 13 + MySQL + Redis | Provider: Digiflazz + Midtrans*
