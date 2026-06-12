'use client';

import React, { useState } from 'react';
import Link from 'next/link';
import { usePathname } from 'next/navigation';
import { Gamepad2, Globe, Menu, X, LogIn } from 'lucide-react';
import { useLanguage } from '@/lib/i18n';
import { Button } from '../ui/button';

export const Navbar: React.FC = () => {
  const { language, setLanguage, t } = useLanguage();
  const pathname = usePathname();
  const [isOpen, setIsOpen] = useState(false);

  const navLinks = [
    { href: '/', label: t.nav.home },
    { href: '/#games', label: t.nav.games },
    { href: '/order', label: t.nav.trackOrder },
  ];

  const toggleLanguage = () => {
    setLanguage(language === 'id' ? 'en' : 'id');
  };

  const isActive = (href: string) => {
    if (href.startsWith('/#')) return false;
    return pathname === href;
  };

  return (
    <nav className="sticky top-0 z-50 glass-nav w-full">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex items-center justify-between h-20">
          {/* Logo / Branding */}
          <Link href="/" className="flex items-center space-x-2 group">
            <div className="p-2 rounded-xl bg-brand-primary/10 border border-brand-primary/20 group-hover:bg-brand-primary/25 transition-all duration-300">
              <Gamepad2 className="h-6 w-6 text-brand-primary group-hover:scale-110 transition-transform duration-300" />
            </div>
            <span className="text-lg md:text-xl font-extrabold tracking-wider bg-linear-to-r from-white via-gray-100 to-brand-primary bg-clip-text text-transparent">
              AZKA TOP UP
            </span>
          </Link>

          {/* Desktop Navigation Links */}
          <div className="hidden md:flex items-center space-x-8">
            {navLinks.map((link) => (
              <Link
                key={link.href}
                href={link.href}
                className={`text-sm font-semibold tracking-wide transition-colors duration-200 hover:text-white ${
                  isActive(link.href) ? 'text-brand-primary' : 'text-gray-300'
                }`}
              >
                {link.label}
              </Link>
            ))}
          </div>

          {/* Right Actions (Language & Auth) */}
          <div className="hidden md:flex items-center space-x-4">
            {/* Language Toggle Button */}
            <button
              onClick={toggleLanguage}
              className="flex items-center space-x-2 px-3 py-1.5 rounded-lg bg-white/5 border border-white/5 hover:bg-white/10 hover:border-white/10 text-gray-300 hover:text-white text-xs font-bold transition-all duration-200 cursor-pointer"
            >
              <Globe className="h-4 w-4 text-brand-primary" />
              <span>{language === 'id' ? 'ID' : 'EN'}</span>
            </button>

            {/* Login / Register CTA */}
            <Link href="/login">
              <Button variant="ghost" size="sm" className="flex items-center space-x-1">
                <LogIn className="h-4 w-4" />
                <span>{t.nav.login}</span>
              </Button>
            </Link>

            <Link href="/admin/login" className="text-gray-400 hover:text-white text-xs transition-colors duration-200">
              {t.nav.adminDashboard}
            </Link>
          </div>

          {/* Mobile menu button */}
          <div className="md:hidden flex items-center space-x-3">
            {/* Language Toggle for Mobile */}
            <button
              onClick={toggleLanguage}
              className="flex items-center space-x-1.5 px-2.5 py-1.5 rounded-lg bg-white/5 border border-white/5 text-gray-300 text-xs font-bold cursor-pointer"
            >
              <Globe className="h-3.5 w-3.5 text-brand-primary" />
              <span>{language === 'id' ? 'ID' : 'EN'}</span>
            </button>

            <button
              onClick={() => setIsOpen(!isOpen)}
              className="p-2 rounded-xl text-gray-400 hover:text-white hover:bg-white/5 focus:outline-none cursor-pointer"
            >
              {isOpen ? <X className="h-6 w-6" /> : <Menu className="h-6 w-6" />}
            </button>
          </div>
        </div>
      </div>

      {/* Mobile Menu (Drawer) */}
      <div
        className={`md:hidden transition-all duration-300 ease-in-out border-b border-white/5 bg-bg-dark/95 ${
          isOpen ? 'max-h-screen opacity-100 py-4' : 'max-h-0 opacity-0 overflow-hidden pointer-events-none'
        }`}
      >
        <div className="px-4 space-y-3">
          {navLinks.map((link) => (
            <Link
              key={link.href}
              href={link.href}
              onClick={() => setIsOpen(false)}
              className={`block px-4 py-2.5 rounded-xl text-base font-semibold transition-colors ${
                isActive(link.href)
                  ? 'bg-brand-primary/10 text-brand-primary'
                  : 'text-gray-300 hover:bg-white/5 hover:text-white'
              }`}
            >
              {link.label}
            </Link>
          ))}
          <div className="h-px bg-white/5 my-2" />
          <Link href="/login" onClick={() => setIsOpen(false)} className="block">
            <Button variant="primary" size="md" className="w-full justify-center">
              {t.nav.login}
            </Button>
          </Link>
          <Link
            href="/admin/login"
            onClick={() => setIsOpen(false)}
            className="block text-center text-gray-400 hover:text-white text-sm py-2 transition-colors duration-200"
          >
            {t.nav.adminDashboard}
          </Link>
        </div>
      </div>
    </nav>
  );
};
