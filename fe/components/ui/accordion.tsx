'use client';

import React, { useState } from 'react';
import { ChevronDown } from 'lucide-react';

interface AccordionItemProps {
  title: string;
  content: string;
}

export const AccordionItem: React.FC<AccordionItemProps> = ({ title, content }) => {
  const [isOpen, setIsOpen] = useState(false);

  return (
    <div className="border border-white/5 rounded-xl bg-bg-surface/50 overflow-hidden transition-all duration-300">
      <button
        onClick={() => setIsOpen(!isOpen)}
        className="w-full flex items-center justify-between p-5 text-left font-semibold text-gray-200 hover:text-white hover:bg-white/5 transition-all duration-200 cursor-pointer"
      >
        <span className="text-sm md:text-base">{title}</span>
        <ChevronDown
          className={`h-5 w-5 text-gray-400 transition-transform duration-300 ${
            isOpen ? 'transform rotate-180 text-brand-primary' : ''
          }`}
        />
      </button>
      
      <div
        className={`transition-all duration-300 ease-in-out ${
          isOpen ? 'max-h-96 opacity-100 border-t border-white/5' : 'max-h-0 opacity-0 pointer-events-none'
        }`}
      >
        <p className="p-5 text-sm md:text-base text-gray-400 leading-relaxed bg-bg-dark/20">
          {content}
        </p>
      </div>
    </div>
  );
};

interface AccordionProps {
  items: AccordionItemProps[];
  className?: string;
}

export const Accordion: React.FC<AccordionProps> = ({ items, className = '' }) => {
  return (
    <div className={`space-y-3 ${className}`}>
      {items.map((item, index) => (
        <AccordionItem key={index} title={item.title} content={item.content} />
      ))}
    </div>
  );
};
