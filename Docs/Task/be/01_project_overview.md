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
