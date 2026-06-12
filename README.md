# AZKA TOP UP — Decoupled Repository

Monorepo workspace untuk pengembangan platform automated top-up AZKA TOP UP.

## Struktur Direktori

```
/be topup (root)
├── Docs/                  # Dokumentasi Proyek & Spesifikasi (PRD)
│   ├── README.md          # Indeks utama dokumentasi
│   ├── prd.md             # Master PRD
│   ├── Task/              # Daftar tugas terpisah (BE & FE)
│   │   ├── be/            # Tugas & milestones Backend (Laravel)
│   │   └── fe/            # Tugas & milestones Frontend (Next.js)
│   └── Implementation/    # Dokumen implementasi teknis
├── be/                    # Backend Codebase (Laravel 13 + PHP 8.4+)
└── fe/                    # Frontend Codebase (Next.js 14) [Akan dibuat nanti]
```

## Cara Memulai

1. **Dokumentasi:** Silakan baca terlebih dahulu indeks dokumentasi di [Docs/README.md](Docs/README.md) untuk memahami spesifikasi, alur, dan ERD database.
2. **Backend:** Seluruh kode backend Laravel berada di direktori `be/`. Panduan tugas pengerjaan backend berada di [Docs/Task/be/07_milestones_phases.md](Docs/Task/be/07_milestones_phases.md).
3. **Frontend:** Seluruh kode frontend Next.js akan berada di direktori `fe/`.
