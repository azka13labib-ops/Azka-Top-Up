'use client';

import React, { createContext, useContext, useState, useEffect } from 'react';

export type Language = 'id' | 'en';

export const translations = {
  id: {
    nav: {
      home: 'Beranda',
      games: 'Game',
      trackOrder: 'Lacak Pesanan',
      login: 'Masuk',
      register: 'Daftar',
      dashboard: 'Dashboard',
      logout: 'Keluar',
      adminDashboard: 'Dashboard Admin',
    },
    hero: {
      title: 'Top Up Game Instan & Aman',
      subtitle: 'Dapatkan diamond, voucher, dan item game favoritmu dalam hitungan detik. Proses otomatis 24 jam dengan metode pembayaran terlengkap dan terpercaya.',
      cta: 'Pilih Game Sekarang',
    },
    games: {
      title: 'Daftar Game Terpopuler',
      subtitle: 'Pilih game favoritmu dan nikmati kemudahan top-up instan',
      searchPlaceholder: 'Cari game...',
      instantBadge: 'Instan',
      noGames: 'Tidak ada game yang ditemukan',
    },
    howItWorks: {
      title: 'Cara Mudah Top Up',
      subtitle: 'Selesaikan pengisian saldo game favoritmu dalam 3 langkah mudah',
      step1Title: '1. Pilih Game & Nominal',
      step1Desc: 'Pilih game favoritmu dan tentukan jumlah nominal top-up yang kamu inginkan.',
      step2Title: '2. Bayar Otomatis',
      step2Desc: 'Pilih dari berbagai metode pembayaran aman (QRIS, e-Wallet, VA) dengan konfirmasi otomatis.',
      step3Title: '3. Masuk Akun Instan',
      step3Desc: 'Sistem kami langsung memproses voucher/top-up ke ID game Anda dalam hitungan detik.',
    },
    faq: {
      title: 'Pertanyaan Umum (FAQ)',
      subtitle: 'Semua hal yang perlu Anda ketahui tentang layanan AZKA TOP UP',
      q1: 'Apakah top-up di AZKA TOP UP legal dan aman?',
      a1: 'Ya, 100% legal dan aman. Kami terhubung langsung secara resmi dengan distributor utama sehingga semua transaksi aman dan terhindar dari banned.',
      q2: 'Berapa lama waktu yang dibutuhkan sampai top-up masuk?',
      a2: 'Sebagian besar transaksi diproses secara instan (3-15 detik) setelah pembayaran dikonfirmasi oleh sistem gateway kami.',
      q3: 'Metode pembayaran apa saja yang didukung?',
      a3: 'Kami mendukung pembayaran QRIS (GoPay, DANA, OVO, ShopeePay, LinkAja) serta Virtual Account dari berbagai bank terkemuka di Indonesia.',
      q4: 'Bagaimana jika saya salah memasukkan ID Game?',
      a4: 'Harap periksa kembali ID Game Anda sebelum membayar. Transaksi yang berhasil dikirim ke ID yang salah tidak dapat ditarik kembali/refund.',
      q5: 'Bagaimana cara menghubungi customer support?',
      a5: 'Jika Anda mengalami masalah transaksi, Anda dapat menghubungi kami langsung via WhatsApp atau Email yang tertera di bagian bawah halaman ini.',
    },
    footer: {
      about: 'AZKA TOP UP adalah platform top-up game tercepat, teraman, dan terpercaya di Indonesia. Melayani jutaan gamer dengan sistem otomatis 24 jam.',
      quickLinks: 'Tautan Cepat',
      support: 'Bantuan & Dukungan',
      paymentPartner: 'Mitra Pembayaran',
      rights: 'Hak Cipta Dilindungi Undang-Undang.',
    },
  },
  en: {
    nav: {
      home: 'Home',
      games: 'Games',
      trackOrder: 'Track Order',
      login: 'Login',
      register: 'Register',
      dashboard: 'Dashboard',
      logout: 'Logout',
      adminDashboard: 'Admin Dashboard',
    },
    hero: {
      title: 'Instant & Secure Game Top-Up',
      subtitle: 'Get diamonds, vouchers, and items for your favorite games in seconds. Automated 24/7 processing with the most complete and trusted payment options.',
      cta: 'Top Up Now',
    },
    games: {
      title: 'Most Popular Games',
      subtitle: 'Choose your favorite game and enjoy the convenience of instant top-ups',
      searchPlaceholder: 'Search games...',
      instantBadge: 'Instant',
      noGames: 'No games found',
    },
    howItWorks: {
      title: 'How It Works',
      subtitle: 'Complete your game top-up in 3 simple steps',
      step1Title: '1. Select Game & Amount',
      step1Desc: 'Choose your favorite game and select the top-up denomination you want.',
      step2Title: '2. Pay Automatically',
      step2Desc: 'Choose from various secure payment methods (QRIS, e-Wallet, VA) with instant confirmation.',
      step3Title: '3. Get Instantly',
      step3Desc: 'Our system immediately processes and sends the top-up/voucher to your game ID in seconds.',
    },
    faq: {
      title: 'Frequently Asked Questions (FAQ)',
      subtitle: 'Everything you need to know about AZKA TOP UP services',
      q1: 'Is top-up at AZKA TOP UP legal and safe?',
      a1: 'Yes, 100% legal and safe. We are officially connected with main distributors, ensuring all transactions are safe and risk-free from bans.',
      q2: 'How long does it take for the top-up to arrive?',
      a2: 'Most transactions are processed instantly (3-15 seconds) once payment is confirmed by our gateway system.',
      q3: 'What payment methods are supported?',
      a3: 'We support QRIS payments (GoPay, DANA, OVO, ShopeePay, LinkAja) and Virtual Accounts from various leading banks in Indonesia.',
      q4: 'What if I entered the wrong Game ID?',
      a4: 'Please double-check your Game ID before paying. Successful transactions sent to the wrong ID cannot be reversed/refunded.',
      q5: 'How do I contact customer support?',
      a5: 'If you experience any transaction issues, you can contact us directly via WhatsApp or Email listed at the bottom of this page.',
    },
    footer: {
      about: 'AZKA TOP UP is the fastest, safest, and most trusted game top-up platform in Indonesia. Serving millions of gamers with 24/7 automated systems.',
      quickLinks: 'Quick Links',
      support: 'Support & Assistance',
      paymentPartner: 'Payment Partners',
      rights: 'All Rights Reserved.',
    },
  },
};

type LanguageContextType = {
  language: Language;
  setLanguage: (lang: Language) => void;
  t: typeof translations.id;
};

const LanguageContext = createContext<LanguageContextType | undefined>(undefined);

export const LanguageProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const [language, setLanguageState] = useState<Language>('id');

  useEffect(() => {
    // Read preference from localStorage on mount
    const savedLang = localStorage.getItem('azka_topup_lang') as Language;
    if (savedLang === 'id' || savedLang === 'en') {
      setTimeout(() => {
        setLanguageState(savedLang);
      }, 0);
    }
  }, []);

  const setLanguage = (lang: Language) => {
    setLanguageState(lang);
    localStorage.setItem('azka_topup_lang', lang);
  };

  const t = translations[language];

  return (
    <LanguageContext.Provider value={{ language, setLanguage, t }}>
      {children}
    </LanguageContext.Provider>
  );
};

export const useLanguage = () => {
  const context = useContext(LanguageContext);
  if (!context) {
    throw new Error('useLanguage must be used within a LanguageProvider');
  }
  return context;
};
