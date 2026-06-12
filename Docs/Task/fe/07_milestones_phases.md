## 7. Frontend Milestones & Development Phases

> **Asumsi:** Solo developer, ~8 jam/hari, 5 hari/minggu.
> Provider: Digiflazz (sandbox aktif) + Midtrans (sandbox aktif).

---

### Phase 0 — Setup & Foundation `Hari 1–5`

**Goal:** Semua infrastruktur dan scaffolding siap, tidak ada hambatan di fase berikutnya.

| Status | # | Task | Hari |
| :---: |---|---|---|
| [ ] | 0.1 | Git setup: struktur repo (monorepo atau 2 repo), `.gitignore`, branch strategy (`main`, `develop`, `feature/*`) | 0.5 |
| [ ] | 0.3 | Next.js 14 scaffold: `create-next-app`, App Router, Tailwind CSS 3, konfigurasi `tailwind.config.js` dengan custom tokens | 1.0 |
| [ ] | 0.7 | GitHub Actions CI dasar: Jest/ESLint pada setiap push ke `develop` | 0.5 |

**Exit criteria:** Next.js homepage render.

---

### Phase 3 — Frontend Customer-Facing `Hari 31–44`

**Goal:** Semua halaman customer berjalan di staging, mobile-first, checkout flow functional.

| Status | # | Task | Hari |
| :---: |---|---|---|
| [ ] | 3.1 | Design system setup: extend `tailwind.config.js` dengan custom color tokens, shadow tokens, base components: `Button`, `Card`, `Badge`, `Input`, `Modal` | 1.0 |
| [ ] | 3.2 | Layout komponen: `Navbar`, `Footer`, `MobileNav` | 0.5 |
| [ ] | 3.3 | Homepage (SSG): hero section (Glassmorphism dark), game category grid (SSG dari API), "Produk Populer" section | 1.5 |
| [ ] | 3.4 | Halaman game SSG `/topup/[slug]`: product listing, denomination cards (Neobrutalism) | 1.5 |
| [ ] | 3.5 | Komponen form order: input Game User ID (+ Zone ID conditional), email input, validasi client-side, panduan User ID modal | 1.0 |
| [ ] | 3.6 | Halaman checkout: order summary card (Glassmorphism), payment method selector, tombol "Bayar Sekarang" | 1.0 |
| [ ] | 3.7 | Integrasi Midtrans Snap.js: load script `midtrans-js`, buka popup on button click, handle callback `onSuccess`, `onPending`, `onError`, `onClose` | 1.0 |
| [ ] | 3.8 | Halaman Menunggu Pembayaran: countdown timer, instruksi pembayaran per metode, tombol "Batalkan" | 1.0 |
| [ ] | 3.9 | Halaman status order `/order/[order_code]`: polling SWR setiap 5 detik, status badges dengan animasi, hentikan polling saat final state | 1.5 |
| [ ] | 3.10 | Halaman sukses & gagal: tampilan detail lengkap, SN display, tombol "Pesan Lagi" | 0.5 |
| [ ] | 3.11 | Auth pages: login, register, forgot password, reset password | 1.0 |
| [ ] | 3.12 | Dashboard user: tabel histori (filter + pagination), detail modal, tombol "Pesan Lagi", export CSV | 1.5 |
| [ ] | 3.13 | SEO: `generateMetadata()` per halaman game, sitemap.xml via `next-sitemap`, JSON-LD breadcrumb | 0.5 |
| [ ] | 3.14 | Mobile responsiveness audit menyeluruh di 375px, 768px, 1280px. Fix semua overflow/layout issue | 1.0 |

**Exit criteria:** Semua halaman render benar di mobile & desktop. Checkout flow functional end-to-end di staging. Lighthouse mobile score ≥ 85.

---

### Phase 4 — Admin Dashboard `Hari 45–52`

**Goal:** Admin dapat mengelola produk, memantau transaksi, dan melihat analytics secara mandiri.

| Status | # | Task | Hari |
| :---: |---|---|---|
| [ ] | 4.1 | Admin auth: login page `/admin/login`, protected route wrapper, role-based component rendering | 0.5 |
| [ ] | 4.2 | Layout admin: sidebar navigasi, topbar dengan nama admin + logout | 0.5 |
| [ ] | 4.3 | Dashboard overview: revenue cards (hari ini, 7 hari, 30 hari), jumlah transaksi per status, success rate | 1.0 |
| [ ] | 4.4 | Chart pendapatan 30 hari: Recharts `LineChart`, data dari `/admin/analytics/summary` | 0.5 |
| [ ] | 4.5 | Halaman manajemen produk: tabel dengan base_price (tersembunyi dari publik), toggle is_active, form set markup inline, tombol Sync, bulk action | 2.0 |
| [ ] | 4.6 | Halaman monitor transaksi: tabel dengan filter kombinasi, pagination, search, detail modal lengkap (payload + logs), tombol Manual Retry & Flag Refund | 1.5 |
| [ ] | 4.7 | Widget saldo Digiflazz di sidebar/dashboard, badge warning jika < threshold | 0.5 |
| [ ] | 4.8 | Halaman settings: maintenance toggle, banner editor, toggle metode bayar | 0.5 |

**Exit criteria:** Admin dapat sync produk dari Digiflazz, set markup, filter dan lihat detail transaksi, trigger manual retry — semua via UI tanpa akses langsung ke database.

---

### Phase 5 — Hardening, QA & Deployment `Hari 53–60`

**Goal:** Platform live di production, aman, monitored, dan siap menerima transaksi nyata.

| Status | # | Task | Hari |
| :---: |---|---|---|
| [ ] | 5.2 | Error monitoring: setup Sentry (Next.js) | 0.5 |
| [ ] | 5.3 | Performance audit: Lighthouse mobile | 0.5 |
| [ ] | 5.6 | Vercel deployment: push frontend ke Vercel, set semua env vars production, validasi build berhasil | 0.5 |
| [ ] | 5.8 | Smoke testing: 20 transaksi end-to-end via sandbox (verifikasi UI flow) | 0.5 |

**Exit criteria:** Frontend live di Vercel production. Semua smoke test sukses.

---

*AZKA TOP UP — PRD v1.0 | Juni 2026*
*Tech: Next.js 14 + Laravel 13 + MySQL + Redis | Provider: Digiflazz + Midtrans*
