'use client';

import React from 'react';
import Link from 'next/link';
import { Gamepad2, Mail, MessageSquare, ShieldCheck } from 'lucide-react';
import { useLanguage } from '@/lib/i18n';

export const Footer: React.FC = () => {
  const { t } = useLanguage();

  const paymentMethods = [
    'QRIS', 'GoPay', 'DANA', 'OVO', 'ShopeePay', 
    'BCA VA', 'BNI VA', 'Mandiri VA', 'BRI VA'
  ];

  return (
    <footer className="bg-[#0b0b14] border-t border-white/5 pt-16 pb-8">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="grid grid-cols-1 md:grid-cols-4 gap-12 mb-16">
          {/* Column 1: Brand Info */}
          <div className="space-y-4">
            <Link href="/" className="flex items-center space-x-2">
              <div className="p-2 rounded-xl bg-brand-primary/10 border border-brand-primary/20">
                <Gamepad2 className="h-5 w-5 text-brand-primary" />
              </div>
              <span className="text-lg font-extrabold tracking-wider bg-linear-to-r from-white to-brand-primary bg-clip-text text-transparent">
                AZKA TOP UP
              </span>
            </Link>
            <p className="text-sm text-gray-400 leading-relaxed">
              {t.footer.about}
            </p>
            <div className="flex items-center space-x-2 text-brand-success text-xs font-semibold">
              <ShieldCheck className="h-4 w-4" />
              <span>100% SECURE & OFFICIAL PARTNER</span>
            </div>
          </div>

          {/* Column 2: Quick Links */}
          <div>
            <h3 className="text-sm font-bold uppercase tracking-wider text-white mb-4">
              {t.footer.quickLinks}
            </h3>
            <ul className="space-y-2.5">
              <li>
                <Link href="/" className="text-sm text-gray-400 hover:text-white transition-colors duration-200">
                  {t.nav.home}
                </Link>
              </li>
              <li>
                <Link href="/#games" className="text-sm text-gray-400 hover:text-white transition-colors duration-200">
                  {t.nav.games}
                </Link>
              </li>
              <li>
                <Link href="/order" className="text-sm text-gray-400 hover:text-white transition-colors duration-200">
                  {t.nav.trackOrder}
                </Link>
              </li>
            </ul>
          </div>

          {/* Column 3: Contact & Support */}
          <div>
            <h3 className="text-sm font-bold uppercase tracking-wider text-white mb-4">
              {t.footer.support}
            </h3>
            <ul className="space-y-3">
              <li className="flex items-center space-x-2 text-sm text-gray-400">
                <MessageSquare className="h-4 w-4 text-brand-primary shrink-0" />
                <a 
                  href="https://wa.me/6281234567890" 
                  target="_blank" 
                  rel="noopener noreferrer"
                  className="hover:text-white transition-colors"
                >
                  WhatsApp: +62 812-3456-7890
                </a>
              </li>
              <li className="flex items-center space-x-2 text-sm text-gray-400">
                <Mail className="h-4 w-4 text-brand-primary shrink-0" />
                <a href="mailto:support@azkatopup.com" className="hover:text-white transition-colors">
                  support@azkatopup.com
                </a>
              </li>
            </ul>
          </div>

          {/* Column 4: Supported Payments */}
          <div>
            <h3 className="text-sm font-bold uppercase tracking-wider text-white mb-4">
              {t.footer.paymentPartner}
            </h3>
            <div className="grid grid-cols-3 gap-2">
              {paymentMethods.map((method) => (
                <div 
                  key={method} 
                  className="flex items-center justify-center p-2 rounded-lg bg-white/5 border border-white/5 hover:border-white/10 text-[10px] font-bold text-gray-400 select-none hover:text-gray-300 transition-colors"
                >
                  {method}
                </div>
              ))}
            </div>
          </div>
        </div>

        {/* Divider */}
        <div className="border-t border-white/5 pt-8 flex flex-col sm:flex-row items-center justify-between text-xs text-gray-500">
          <p>© {new Date().getFullYear()} AZKA TOP UP. {t.footer.rights}</p>
          <div className="flex space-x-4 mt-4 sm:mt-0">
            <Link href="/terms" className="hover:text-gray-400 transition-colors">Terms of Service</Link>
            <Link href="/privacy" className="hover:text-gray-400 transition-colors">Privacy Policy</Link>
          </div>
        </div>
      </div>
    </footer>
  );
};
