import type { Metadata } from 'next';
import { Inter } from 'next/font/google';
import { LanguageProvider } from '@/lib/i18n';
import { Navbar } from '@/components/layout/navbar';
import { Footer } from '@/components/layout/footer';
import './globals.css';

const inter = Inter({
  subsets: ['latin'],
  variable: '--font-inter',
  display: 'swap',
});

export const metadata: Metadata = {
  title: 'Top Up Game Instan & Aman | AZKA TOP UP',
  description: 'Beli diamond, UC, dan voucher game favoritmu dengan harga terbaik dan proses otomatis instan 24 jam terpercaya di Indonesia.',
  keywords: 'top up game, diamond ml, voucher game, top up murah, azka top up, mobile legends, free fire',
  robots: {
    index: true,
    follow: true,
  },
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="id" className={`${inter.variable} h-full antialiased`}>
      <body className="font-sans min-h-full flex flex-col bg-bg-dark text-foreground">
        <LanguageProvider>
          <Navbar />
          <main className="grow">
            {children}
          </main>
          <Footer />
        </LanguageProvider>
      </body>
    </html>
  );
}
