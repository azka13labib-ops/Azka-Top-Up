import React from 'react';

type BadgeVariant = 'success' | 'danger' | 'warning' | 'info' | 'neutral';

interface BadgeProps {
  children: React.ReactNode;
  variant?: BadgeVariant;
  className?: string;
}

export const Badge: React.FC<BadgeProps> = ({
  children,
  variant = 'neutral',
  className = '',
}) => {
  const baseStyle = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold select-none border tracking-wider uppercase';

  const variants = {
    neutral: 'bg-white/5 text-gray-300 border-white/10',
    success: 'bg-brand-success/10 text-brand-success border-brand-success/20',
    danger: 'bg-brand-danger/10 text-brand-danger border-brand-danger/20',
    warning: 'bg-brand-warning/10 text-brand-warning border-brand-warning/20',
    info: 'bg-brand-info/10 text-brand-info border-brand-info/20',
  };

  return (
    <span className={`${baseStyle} ${variants[variant]} ${className}`}>
      {children}
    </span>
  );
};
