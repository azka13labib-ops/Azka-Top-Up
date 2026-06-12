# AZKA TOP UP — Frontend PRD v1.0
> **Tech Stack:** Next.js 14+ (App Router) · Tailwind CSS · SWR · TypeScript  
> **Scope:** Customer Store + Admin Dashboard (satu proyek, satu repo)  
> **Target:** Responsive sempurna 375px – 1280px+  
> **Bahasa:** Bilingual Indonesia / English (toggle)  
> **Tanggal:** Juni 2026

---

## 1. Ringkasan Proyek

Frontend AZKA TOP UP adalah aplikasi Next.js yang melayani dua persona:

1. **Customer** — Membeli top-up game secara instan tanpa login (guest checkout) atau dengan akun terdaftar.
2. **Admin** — Mengelola produk, markup harga, memantau transaksi, dan mengkonfigurasi platform.

Keduanya berada dalam **satu proyek Next.js** dengan routing terpisah:
- `/` → customer store (publik)
- `/admin` → admin dashboard (protected, hanya AdminUser)

Backend: Laravel REST API di `http://localhost:8000/api/v1` (dev) / `https://api.azkatopup.com/api/v1` (prod).

---

## 2. Struktur Direktori

```
fe/
├── app/
│   ├── (store)/                  # Customer-facing layout
│   │   ├── page.tsx              # Homepage
│   │   ├── games/[slug]/         # Halaman produk per game
│   │   ├── order/[order_code]/   # Status order
│   │   ├── dashboard/            # Histori transaksi user (protected)
│   │   ├── login/
│   │   ├── register/
│   │   └── layout.tsx
│   │
│   ├── (admin)/                  # Admin layout (terpisah)
│   │   ├── admin/
│   │   │   ├── login/
│   │   │   ├── dashboard/        # Overview & analytics
│   │   │   ├── products/         # Manajemen produk
│   │   │   ├── orders/           # Monitor transaksi
│   │   │   └── settings/         # Konfigurasi platform
│   │   └── layout.tsx
│   │
│   ├── api/                      # Next.js Route Handlers (proxy tipis ke Laravel)
│   │   └── auth/[...nextauth]/
│   │
│   └── layout.tsx                # Root layout
│
├── components/
│   ├── store/                    # Komponen customer
│   ├── admin/                    # Komponen admin
│   └── ui/                       # Shared UI (Button, Badge, Modal, dll.)
│
├── lib/
│   ├── api.ts                    # Axios/fetch wrapper ke Laravel API
│   ├── auth.ts                   # Token management (HttpOnly cookie)
│   └── i18n.ts                   # Bilingual strings (ID/EN)
│
├── hooks/
│   ├── useOrderStatus.ts         # SWR polling order status
│   └── useAuth.ts                # Auth state
│
└── public/
    └── games/                    # Fallback thumbnail game lokal
```

---

## 3. Design System

### 3.1 Filosofi Visual
- **Customer Store:** Modern & vibrant — warna cerah, gradient, animasi hover smooth. Kesan "premium tapi tetap Indonesia": familiar untuk gamer muda.
- **Admin Dashboard:** Professional & dense — dark sidebar, tabel data-heavy, warna informatif (hijau sukses, merah gagal, kuning pending).

### 3.2 Warna Utama

| Token | Hex | Penggunaan |
|---|---|---|
| `brand-primary` | `#6C3AEF` | Tombol utama, highlight, link aktif |
| `brand-secondary` | `#F59E0B` | Badge promo, aksen panas |
| `brand-success` | `#10B981` | Status sukses |
| `brand-danger` | `#EF4444` | Status gagal, error |
| `brand-warning` | `#F59E0B` | Status pending |
| `brand-info` | `#3B82F6` | Status processing |
| `bg-dark` | `#0F0F1A` | Background admin dark mode |
| `bg-surface` | `#1A1A2E` | Card admin dark mode |

### 3.3 Typography
- **Font:** `Inter` (Google Fonts) — fallback: `system-ui`
- `font-size-base`: 16px
- Skala: `text-xs` (12) → `text-sm` (14) → `text-base` (16) → `text-xl` (20) → `text-3xl` (30) → `text-5xl` (48)

### 3.4 Komponen UI Shared

```
<Button variant="primary|secondary|ghost|danger" size="sm|md|lg" loading={bool} />
<Badge variant="success|danger|warning|info|neutral" />
<Modal>
<Spinner />
<Toast /> (via react-hot-toast)
<DataTable columns={[]} data={[]} pagination loading />
<StatusBadge status="pending|paid|completed|failed|expired" />
<LanguageToggle /> (ID/EN)
```

---

## 4. Halaman & Fitur — Customer Store

### 4.1 Homepage `/`

**Tujuan:** Landing page yang meyakinkan dan cepat menuju checkout.

**Sections:**
1. **Hero Banner** — Headline, subheadline, CTA "Top Up Sekarang". Bisa diisi gambar/ilustrasi game.
2. **Grid Game** — Card per game: thumbnail, nama, badge "Instan". Sorted by `sort_order`. Click → `/games/[slug]`.
3. **Produk Populer** — 6 produk dengan order count tertinggi (data dari API).
4. **Cara Kerja** — 3 langkah: Pilih → Bayar → Masuk Game. Visual steps.
5. **FAQ Singkat** — 5 pertanyaan paling umum (accordion, konten statis).
6. **Footer** — Logo, link navigasi, kontak (WhatsApp + email), copyright.

**Technical:**
- Next.js SSG + ISR (revalidate 3600 detik) untuk performa SEO.
- `fetchGames()` di server component.
- Meta title: `Top Up Diamond Murah & Instan | AZKA TOP UP`

---

### 4.2 Halaman Game `/games/[slug]`

**Tujuan:** Pilih produk dan mulai checkout dalam 1 halaman.

**Layout:**
```
[Game Header: thumbnail + nama + deskripsi]

[Form Order — kanan/bawah di mobile]
  ├── Input: Game User ID  (label dinamis dari API: "User ID", "Player ID", dll.)
  ├── Input: Zone/Server ID  (tampil HANYA jika game.needs_zone = true)
  ├── Tombol "Cek Nama Akun" (optional — kosongkan jika game tidak support)
  ├── Input: Email (untuk notifikasi)
  └── Panduan cari ID: link buka modal dengan ilustrasi

[Daftar Produk/Denominasi]
  ├── Grid card: nama produk, harga (Rp), badge stok
  └── Click produk → highlight + masuk ke summary
```

**Checkout Flow:**
1. User pilih produk (highlight card terpilih)
2. Isi form (Game ID, Zone jika perlu, Email)
3. Klik "Beli Sekarang" → POST `/api/v1/orders`
4. Response berisi `snap_token` → buka **Midtrans Snap.js popup**
5. User bayar di popup
6. Midtrans close → redirect ke `/order/[order_code]`

**Validasi form:**
- Game User ID: required, validasi format per game (numerik untuk FF, dll.)
- Email: format email valid
- Zone ID: required hanya jika `game.needs_zone = true`

**Technical:**
- SSG page dengan ISR 3600s
- Produk di-fetch server-side dari `GET /api/v1/games/{slug}/products`
- Midtrans Snap.js dimuat via `<Script src="https://app.sandbox.midtrans.com/snap/snap.js" />`
- `snap.pay(token, { onSuccess, onPending, onError, onClose })`

---

### 4.3 Halaman Status Order `/order/[order_code]`

**Tujuan:** Pelanggan bisa memantau progress transaksi secara real-time.

**Layout:**
```
[Header: Kode Order]

[Status Card — visual utama]
  ├── ⏳ Menunggu Pembayaran  (kuning, countdown timer)
  ├── 💳 Pembayaran Diterima  (biru, spinner animasi)
  ├── 🔄 Sedang Diproses      (biru, spinner animasi)
  ├── ✅ Top-up Berhasil      (hijau, confetti animasi)
  └── ❌ Top-up Gagal         (merah, info refund)

[Detail Order]
  Game | Produk | Harga | Metode Bayar | Waktu Order

[Jika Sukses: tampilkan SN/token Digiflazz]

[Tombol "Pesan Lagi" | "Kembali ke Home"]

[Jika Gagal: Kontak support via WhatsApp / Email]
```

**Technical:**
- `useOrderStatus(orderCode)` — SWR polling setiap 5 detik ke `GET /api/v1/orders/{order_code}`
- Stop polling jika `payment_status = expired` ATAU `topup_status = completed|failed`
- Countdown timer untuk pembayaran pending (hitung dari `created_at + 60 menit`)
- Halaman ini publik — tidak butuh auth

---

### 4.4 Auth Customer

| Halaman | Path | Keterangan |
|---|---|---|
| Login | `/login` | Email + password. "Ingat saya" checkbox |
| Register | `/register` | Nama, email, password, konfirmasi |
| Lupa Password | `/forgot-password` | Input email → kirim reset link |
| Reset Password | `/reset-password?token=` | Form password baru |

- Token Sanctum disimpan sebagai **HttpOnly cookie** (via Next.js route handler sebagai proxy, bukan localStorage)
- Redirect ke `/dashboard` setelah login sukses
- Middleware Next.js: redirect ke `/login` jika akses `/dashboard` tanpa session

---

### 4.5 Dashboard User `/dashboard`

**Hanya untuk user terdaftar (protected route).**

| Tab | Konten |
|---|---|
| Riwayat Transaksi | Tabel: Kode Order, Game, Produk, Harga, Status, Tanggal. Pagination 20/halaman |
| Filter | Status (semua/sukses/gagal/pending), range tanggal, game |
| Detail Order | Click baris → modal detail lengkap |
| Pesan Lagi | Tombol di modal detail → pre-fill checkout form |

---

## 5. Halaman & Fitur — Admin Dashboard

### 5.1 Auth Admin

- URL login: `/admin/login` — **tidak ada link dari halaman publik**
- Setelah login, token admin disimpan sebagai HttpOnly cookie terpisah dari customer
- Middleware Next.js: cek cookie admin → redirect ke `/admin/login` jika tidak ada
- **Session expire 8 jam** (sesuai backend) — tampilkan notifikasi "Session expired, silakan login ulang"

---

### 5.2 Dashboard Overview `/admin/dashboard`

**Sidebar navigasi (dark, collapsible di mobile):**
```
🏠 Dashboard
📦 Produk
📋 Transaksi
⚙️  Pengaturan
👤 Profile
[Logout]
```

**Main content:**

#### Summary Cards (row atas):
| Card | Data |
|---|---|
| 💰 Revenue Hari Ini | Total selling_price order sukses hari ini |
| 📦 Transaksi Sukses | Count completed hari ini |
| ⏳ Pending | Count pending/processing hari ini |
| ❌ Gagal | Count failed hari ini |
| 💳 Saldo Digiflazz | Fetch dari `GET /api/v1/admin/balance` |

#### Chart Revenue (30 hari):
- Line chart menggunakan **Recharts**
- X-axis: tanggal, Y-axis: total revenue (Rp)
- Tooltip hover: tanggal + total + jumlah transaksi

#### Alert:
- ⚠️ Warning card jika saldo Digiflazz < threshold (configurable di settings)
- ⚠️ Warning card jika ada order `topup_status = failed` yang belum di-flag

---

### 5.3 Manajemen Produk `/admin/products`

**Toolbar:**
```
[🔄 Sync Pricelist Digiflazz]  [Filter: Game ▼]  [Bulk Action ▼]  [Search...]
```

**Tabel produk:**
| Kolom | Keterangan |
|---|---|
| Produk | Nama + SKU |
| Game | Badge nama game |
| Base Price | Harga H2H dari Digiflazz (Rp) |
| Markup | Input inline (flat Rp / persen %) |
| Selling Price | Preview real-time setelah markup |
| Margin | Rp & % profit |
| Status | Toggle switch aktif/nonaktif |
| Aksi | Edit modal |

**Fitur:**
- **Sync Pricelist:** klik tombol → loading → snackbar hasil (X produk baru, Y harga berubah)
- **Edit markup inline:** klik sel markup → edit langsung → auto-save dengan debounce 500ms
- **Preview selling_price:** update real-time tanpa simpan dulu
- **Bulk action:** select checkbox → "Aktifkan Semua" / "Nonaktifkan Semua" / "Set Markup Massal"
- **Set Markup Massal per Game:** modal → pilih game → set aturan markup → preview → konfirmasi

---

### 5.4 Monitor Transaksi `/admin/orders`

**Filter bar:**
```
[Status Bayar ▼] [Status Top-up ▼] [Game ▼] [Tanggal: dari–sampai] [Search...] [🔄 Refresh] [📥 Export CSV]
```

**Tabel transaksi (auto-refresh 30 detik):**
| Kolom | Keterangan |
|---|---|
| Kode Order | Format AZKA-YYYYMMDD-XXXXX |
| Email | Email customer |
| Game / Produk | Nama game + nama produk |
| Total | Selling price (Rp) |
| Bayar | StatusBadge payment_status |
| Top-up | StatusBadge topup_status |
| Waktu | Relative time (misal: "3 menit lalu") |
| Aksi | Tombol detail |

**Modal Detail Order:**
```
[Tab: Ringkasan] [Tab: Log Timeline] [Tab: Payload Digiflazz]

Ringkasan:
  - Semua field order
  - Tombol [🔁 Retry] (jika topup_status = failed/processing)
  - Tombol [🚩 Flag Refund] (jika belum di-flag)
  - Form catatan refund (muncul setelah flag)

Log Timeline:
  - Daftar order_logs dengan event + timestamp + payload JSON (collapsible)

Payload Digiflazz:
  - Request yang dikirim (JSON, syntax highlighted)
  - Response yang diterima (JSON, syntax highlighted)
```

---

### 5.5 Pengaturan `/admin/settings`

**Tabs:**

| Tab | Konten |
|---|---|
| Umum | Nama toko, deskripsi, URL WhatsApp support, Email support |
| Pembayaran | Toggle metode: QRIS, GoPay, DANA, ShopeePay, BCA VA, BNI VA, Mandiri |
| Notifikasi | Threshold saldo Digiflazz, email alert, Telegram bot token + chat ID |
| Maintenance | Toggle maintenance mode, teks banner pengumuman homepage |

Semua settings di-persist ke `PUT /api/v1/admin/settings` → `site_settings` tabel.

---

## 6. Internasionalisasi (i18n)

- Toggle bahasa **Indonesia / English** di navbar (simpan di `localStorage`)
- Semua string UI menggunakan key dari `lib/i18n.ts`
- Prioritas terjemahan v1: semua halaman customer store
- Admin dashboard: cukup Bahasa Indonesia untuk v1 (admin internal)

**Contoh struktur:**
```typescript
// lib/i18n.ts
export const strings = {
  id: {
    home: { hero_title: "Top Up Game Instan & Murah", ... },
    order: { status_pending: "Menunggu Pembayaran", ... },
  },
  en: {
    home: { hero_title: "Instant & Affordable Game Top-Up", ... },
    order: { status_pending: "Awaiting Payment", ... },
  }
}
```

---

## 7. State Management & Data Fetching

| Kebutuhan | Solusi |
|---|---|
| Auth state (user/admin) | React Context + HttpOnly cookie |
| Order status polling | SWR dengan `refreshInterval: 5000` |
| Admin table data | SWR dengan `revalidateOnFocus: true` |
| Form state | React Hook Form + Zod validation |
| Toast/notifikasi | `react-hot-toast` |
| Tanggal/waktu | `date-fns` |
| Format rupiah | `Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' })` |

---

## 8. SEO

| Halaman | Title Tag | Meta Description |
|---|---|---|
| Homepage | `Top Up Game Instan & Murah \| AZKA TOP UP` | `Beli diamond, UC, dan voucher game favoritmu dengan harga terbaik dan proses otomatis instan 24 jam.` |
| Game `/games/mobile-legends` | `Top Up Diamond Mobile Legends Murah & Instan \| AZKA TOP UP` | `Top up diamond Mobile Legends dengan harga terbaik, proses otomatis 24 jam tanpa ribet.` |
| Order Status | `Status Pesanan {order_code} \| AZKA TOP UP` | (noindex) |
| Admin | (noindex semua halaman admin) | — |

- `robots.txt`: block semua `/admin/*` dan `/dashboard/*`
- `sitemap.xml`: generate otomatis untuk semua halaman game (via `next-sitemap`)
- Open Graph image per halaman game

---

## 9. Performa & Non-Functional

| Metrik | Target |
|---|---|
| LCP (Largest Contentful Paint) | < 2.5 detik |
| FID (First Input Delay) | < 100ms |
| CLS (Cumulative Layout Shift) | < 0.1 |
| Time to Interactive | < 3.5 detik pada 4G |
| Bundle size | < 200KB JS initial load |
| Lighthouse Score | ≥ 90 (mobile) |

**Strategi performa:**
- Semua halaman game: SSG + ISR (tidak ada server render per request)
- Gambar: `next/image` dengan `priority` untuk LCP image di hero
- Font: `next/font` (auto-subset, no layout shift)
- Kode splitting otomatis per route (App Router default)

---

## 10. Keamanan Frontend

| Area | Implementasi |
|---|---|
| Token storage | HttpOnly cookie (tidak bisa diakses JS) |
| API calls | Semua via server-side route handler saat butuh auth header |
| XSS | Tidak gunakan `dangerouslySetInnerHTML`; semua output di-escape React |
| CSRF | SameSite=Lax cookie |
| Admin route | Middleware Next.js cek cookie sebelum render |
| Env variables | Semua secret di `.env.local`, hanya expose `NEXT_PUBLIC_*` ke browser |

---

## 11. Milestones Pengembangan Frontend

### Phase A — Foundation & Store (Estimasi: 8–10 hari)
| # | Task |
|---|---|
| A.1 | Setup proyek: `create-next-app`, konfigurasi Tailwind, ESLint, TypeScript |
| A.2 | Design system: token warna, komponen UI dasar (Button, Badge, Modal, Toast) |
| A.3 | Layout store: Navbar, Footer, LanguageToggle |
| A.4 | Homepage: Hero, Grid Game, Cara Kerja, FAQ |
| A.5 | Halaman game `/games/[slug]`: list produk + form order |
| A.6 | Integrasi Midtrans Snap.js: `snap.pay()` dari token API |
| A.7 | Halaman status order `/order/[order_code]`: polling SWR, status visual |
| A.8 | Auth customer: login, register, forgot password |
| A.9 | Dashboard user: histori transaksi |
| A.10 | i18n: bilingual ID/EN untuk semua halaman store |

### Phase B — Admin Dashboard (Estimasi: 7–9 hari)
| # | Task |
|---|---|
| B.1 | Layout admin: dark sidebar, header, auth middleware |
| B.2 | Admin login + session management |
| B.3 | Dashboard overview: summary cards + Recharts line chart |
| B.4 | Manajemen produk: tabel, sync Digiflazz, edit markup inline |
| B.5 | Monitor transaksi: tabel dengan filter, modal detail, retry, flag refund |
| B.6 | Halaman settings: toggle pembayaran, notifikasi, maintenance |

### Phase C — Polish & Deploy (Estimasi: 3–4 hari)
| # | Task |
|---|---|
| C.1 | SEO: sitemap.xml, robots.txt, OG image |
| C.2 | Performance audit: Lighthouse ≥ 90 mobile |
| C.3 | Testing: E2E flow checkout sandbox |
| C.4 | Deploy ke Vercel + konfigurasi env production |

---

## 12. Dependencies

```json
{
  "dependencies": {
    "next": "^14.x",
    "react": "^18.x",
    "tailwindcss": "^3.x",
    "swr": "^2.x",
    "react-hook-form": "^7.x",
    "zod": "^3.x",
    "recharts": "^2.x",
    "react-hot-toast": "^2.x",
    "date-fns": "^3.x",
    "axios": "^1.x",
    "next-sitemap": "^4.x"
  },
  "devDependencies": {
    "typescript": "^5.x",
    "@types/react": "^18.x",
    "eslint": "^8.x",
    "prettier": "^3.x"
  }
}
```

---

*AZKA TOP UP Frontend PRD v1.0 | Juni 2026*  
*Referensi backend: `c:\laragon\www\be topup\Docs\Task\be\`*
