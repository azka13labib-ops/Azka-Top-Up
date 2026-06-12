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
