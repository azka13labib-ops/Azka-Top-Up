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
                                ┌────────▼──┐  ┌──▼────────────────┐
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