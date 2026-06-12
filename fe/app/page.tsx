'use client';

import React, { useState } from 'react';
import Link from 'next/link';
import useSWR from 'swr';
import { api } from '@/lib/api';
import { useLanguage } from '@/lib/i18n';
import { Accordion } from '@/components/ui/accordion';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Gamepad2, Search, ArrowRight, ShieldCheck, Zap, HelpCircle } from 'lucide-react';

interface Game {
  id: number;
  name: string;
  slug: string;
  thumbnail_url: string;
  description: string;
  id_field_label: string;
  id_field_placeholder: string;
  needs_zone: boolean;
  is_active: boolean;
  sort_order: number;
}

const fetcher = (url: string) => api.get(url).then((res) => res.data.data);

// Fallback games in case backend is loading or unavailable
const fallbackGames: Game[] = [
  {
    id: 1,
    name: 'Mobile Legends',
    slug: 'mobile-legends',
    thumbnail_url: '/games/mobile-legends.png',
    description: 'Top Up Diamonds Mobile Legends: Bang Bang Instan 24 Jam.',
    id_field_label: 'User ID',
    id_field_placeholder: 'Contoh: 12345678',
    needs_zone: true,
    is_active: true,
    sort_order: 1,
  },
  {
    id: 2,
    name: 'Free Fire',
    slug: 'free-fire',
    thumbnail_url: '/games/free-fire.png',
    description: 'Top Up Diamonds Free Fire Instan 24 Jam.',
    id_field_label: 'Player ID',
    id_field_placeholder: 'Contoh: 87654321',
    needs_zone: false,
    is_active: true,
    sort_order: 2,
  },
  {
    id: 3,
    name: 'PUBG Mobile',
    slug: 'pubg-mobile',
    thumbnail_url: '/games/pubg-mobile.png',
    description: 'Top Up UC PUBG Mobile Instan 24 Jam.',
    id_field_label: 'Character ID',
    id_field_placeholder: 'Contoh: 5123456789',
    needs_zone: false,
    is_active: true,
    sort_order: 3,
  },
];

export default function Home() {
  const { t } = useLanguage();
  const [search, setSearch] = useState('');
  
  // Fetch active games from backend
  const { data: games, isLoading } = useSWR<Game[]>('/games', fetcher, {
    revalidateOnFocus: false,
    shouldRetryOnError: false,
  });

  // Decide which games to display (fetched data, or fallbacks if loading/error)
  const displayGames = games && games.length > 0 ? games : fallbackGames;

  // Filter games based on search input
  const filteredGames = displayGames.filter((game) =>
    game.name.toLowerCase().includes(search.toLowerCase())
  );

  // FAQ Items from translations
  const faqItems = [
    { title: t.faq.q1, content: t.faq.a1 },
    { title: t.faq.q2, content: t.faq.a2 },
    { title: t.faq.q3, content: t.faq.a3 },
    { title: t.faq.q4, content: t.faq.a4 },
    { title: t.faq.q5, content: t.faq.a5 },
  ];

  return (
    <div className="relative">

      {/* Hero Section */}
      <section className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-12 pb-20 md:pt-20 md:pb-32">
        <div className="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
          <div className="lg:col-span-7 space-y-6 text-center lg:text-left">
            <Badge variant="info">
              🚀 {t.howItWorks.step3Title.split('.')[1] || 'PROSES OTOMATIS'}
            </Badge>
            <h1 className="text-4xl md:text-6xl font-extrabold tracking-tight text-white leading-tight">
              {t.hero.title.split(' ').map((word, i) => 
                word.toLowerCase() === 'instan' || word.toLowerCase() === 'instant' || word.toLowerCase() === 'aman' || word.toLowerCase() === 'secure' ? (
                  <span key={i} className="bg-linear-to-r from-brand-primary to-brand-secondary bg-clip-text text-transparent">
                    {word}{' '}
                  </span>
                ) : `${word} `
              )}
            </h1>
            <p className="text-base md:text-lg text-gray-400 max-w-xl mx-auto lg:mx-0 leading-relaxed">
              {t.hero.subtitle}
            </p>
            <div className="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4">
              <a href="#games">
                <Button variant="primary" size="lg" className="group w-full sm:w-auto">
                  <span>{t.hero.cta}</span>
                  <ArrowRight className="ml-2 h-5 w-5 group-hover:translate-x-1 transition-transform" />
                </Button>
              </a>
              <div className="flex items-center space-x-2 text-xs text-gray-400">
                <ShieldCheck className="h-5 w-5 text-brand-success shrink-0" />
                <span>Secure SSL Checkout</span>
              </div>
            </div>
          </div>

          <div className="lg:col-span-5 hidden lg:block relative">
            {/* Visual game card mockups */}
            <div className="relative w-full h-[400px] rounded-3xl overflow-hidden glass p-6 border border-white/10">
              <div className="relative h-full flex flex-col justify-between">
                <div className="flex items-center justify-between">
                  <div className="flex items-center space-x-2">
                    <Gamepad2 className="h-8 w-8 text-brand-primary" />
                    <span className="font-extrabold text-white tracking-widest text-sm">AZKA TOP UP</span>
                  </div>
                  <Badge variant="success">Online 24h</Badge>
                </div>
                <div className="space-y-4">
                  <div className="h-4 bg-white/5 rounded w-2/3" />
                  <div className="h-10 bg-white/10 rounded-xl w-full flex items-center px-4 justify-between border border-white/5">
                    <span className="text-xs text-gray-400">Pilih Game...</span>
                    <Search className="h-4 w-4 text-gray-500" />
                  </div>
                  <div className="grid grid-cols-3 gap-2">
                    <div className="h-20 bg-white/5 rounded-xl border border-white/5 flex items-center justify-center text-xs font-bold text-gray-500">MLBB</div>
                    <div className="h-20 bg-white/5 rounded-xl border border-white/5 flex items-center justify-center text-xs font-bold text-gray-500">FF</div>
                    <div className="h-20 bg-white/5 rounded-xl border border-white/5 flex items-center justify-center text-xs font-bold text-gray-500">PUBG</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* Game Grid Section */}
      <section id="games" className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 border-t border-white/5">
        <div className="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-12">
          <div>
            <h2 className="text-2xl md:text-3xl font-extrabold text-white mb-2">
              {t.games.title}
            </h2>
            <p className="text-sm md:text-base text-gray-400">
              {t.games.subtitle}
            </p>
          </div>

          {/* Search Input */}
          <div className="relative w-full md:w-80">
            <input
              type="text"
              placeholder={t.games.searchPlaceholder}
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              className="w-full pl-11 pr-4 py-3 rounded-xl bg-bg-surface/50 border border-white/5 focus:border-brand-primary/50 text-white placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-brand-primary/50 transition-all duration-300"
            />
            <Search className="absolute left-4 top-3.5 h-5 w-5 text-gray-500" />
          </div>
        </div>

        {/* Dynamic skeleton loader or game cards */}
        {isLoading && !games ? (
          <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-6">
            {[1, 2, 3, 4].map((n) => (
              <div key={n} className="rounded-2xl border border-white/5 bg-bg-surface/30 p-4 space-y-4 animate-pulse">
                <div className="aspect-square bg-white/5 rounded-xl w-full" />
                <div className="h-4 bg-white/10 rounded w-2/3" />
                <div className="h-3 bg-white/5 rounded w-1/2" />
              </div>
            ))}
          </div>
        ) : filteredGames.length === 0 ? (
          <div className="text-center py-16 text-gray-500 space-y-2">
            <Gamepad2 className="h-12 w-12 mx-auto opacity-20" />
            <p className="text-base">{t.games.noGames}</p>
          </div>
        ) : (
          <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-6">
            {filteredGames.map((game) => (
              <Link
                key={game.id}
                href={`/games/${game.slug}`}
                className="group relative rounded-2xl border border-white/5 hover:border-white/10 bg-bg-surface/30 hover:bg-bg-surface/60 p-4 transition-all duration-300 hover:-translate-y-0.5 cursor-pointer block"
              >
                <div className="relative aspect-square rounded-xl overflow-hidden mb-4 bg-bg-dark border border-white/5">
                  {/* Local image fallback or fetched URL */}
                  {/* eslint-disable-next-line @next/next/no-img-element */}
                  <img
                    src={game.thumbnail_url.includes('placeholder.com') ? `/games/${game.slug}.png` : game.thumbnail_url}
                    alt={game.name}
                    className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                    loading="lazy"
                    onError={(e) => {
                      (e.target as HTMLImageElement).src = `/games/${game.slug}.png`;
                    }}
                  />
                  <div className="absolute top-2 right-2">
                    <Badge variant="success" className="text-[10px] scale-90">
                      {t.games.instantBadge}
                    </Badge>
                  </div>
                </div>
                <h3 className="font-bold text-white group-hover:text-brand-primary transition-colors text-sm md:text-base">
                  {game.name}
                </h3>
                <p className="text-xs text-gray-500 mt-1 line-clamp-1">
                  {game.description}
                </p>
              </Link>
            ))}
          </div>
        )}
      </section>

      {/* How it Works Section */}
      <section className="bg-[#0b0b14] py-20 border-t border-b border-white/5">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center max-w-2xl mx-auto mb-16">
            <h2 className="text-2xl md:text-3xl font-extrabold text-white mb-3">
              {t.howItWorks.title}
            </h2>
            <p className="text-sm md:text-base text-gray-400">
              {t.howItWorks.subtitle}
            </p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div className="p-6 rounded-2xl bg-bg-surface/20 border border-white/5 flex flex-col items-center text-center space-y-4">
              <div className="p-4 rounded-full bg-brand-primary/10 border border-brand-primary/20 text-brand-primary">
                <Gamepad2 className="h-6 w-6" />
              </div>
              <h3 className="font-bold text-white text-base md:text-lg">{t.howItWorks.step1Title}</h3>
              <p className="text-sm text-gray-400 leading-relaxed">
                {t.howItWorks.step1Desc}
              </p>
            </div>

            <div className="p-6 rounded-2xl bg-bg-surface/20 border border-white/5 flex flex-col items-center text-center space-y-4">
              <div className="p-4 rounded-full bg-brand-secondary/10 border border-brand-secondary/20 text-brand-secondary">
                <Zap className="h-6 w-6" />
              </div>
              <h3 className="font-bold text-white text-base md:text-lg">{t.howItWorks.step2Title}</h3>
              <p className="text-sm text-gray-400 leading-relaxed">
                {t.howItWorks.step2Desc}
              </p>
            </div>

            <div className="p-6 rounded-2xl bg-bg-surface/20 border border-white/5 flex flex-col items-center text-center space-y-4">
              <div className="p-4 rounded-full bg-brand-success/10 border border-brand-success/20 text-brand-success">
                <ShieldCheck className="h-6 w-6" />
              </div>
              <h3 className="font-bold text-white text-base md:text-lg">{t.howItWorks.step3Title}</h3>
              <p className="text-sm text-gray-400 leading-relaxed">
                {t.howItWorks.step3Desc}
              </p>
            </div>
          </div>
        </div>
      </section>

      {/* FAQ Section */}
      <section className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div className="text-center mb-12">
          <div className="inline-flex p-3 rounded-full bg-brand-primary/10 border border-brand-primary/20 text-brand-primary mb-3">
            <HelpCircle className="h-6 w-6" />
          </div>
          <h2 className="text-2xl md:text-3xl font-extrabold text-white mb-2">
            {t.faq.title}
          </h2>
          <p className="text-sm md:text-base text-gray-400">
            {t.faq.subtitle}
          </p>
        </div>

        <Accordion items={faqItems} />
      </section>
    </div>
  );
}
