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
