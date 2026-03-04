import React from 'react';
import { ArrowRight, Sparkles } from 'lucide-react';

export default function Hero() {
    return (
        <div className="relative w-full h-[500px] mb-12 rounded-3xl overflow-hidden shadow-2xl group">
            {/* 1. ФОНОВАЯ КАРТИНКА (Абстрактный стиль или мода) */}
            <img
                src="https://images.unsplash.com/photo-1483985988355-763728e1935b?q=80&w=2070&auto=format&fit=crop"
                alt="Hero Banner"
                className="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
            />

            {/* 2. ГРАДИЕНТ (Затемнение, чтобы текст читался) */}
            <div className="absolute inset-0 bg-gradient-to-r from-black/90 via-black/50 to-transparent"></div>

            {/* 3. КОНТЕНТ ПОВЕРХ КАРТИНКИ */}
            <div className="absolute inset-0 flex flex-col justify-center px-8 sm:px-16 max-w-3xl">

                {/* Бейдж */}
                <div className="flex items-center gap-2 mb-6 animate-fade-in-up">
                    <span className="px-4 py-1.5 bg-indigo-600 text-white text-xs font-black tracking-widest uppercase rounded-full shadow-lg shadow-indigo-500/30 flex items-center gap-2">
                        <Sparkles className="w-3 h-3" /> New Collection 2026
                    </span>
                </div>

                {/* Заголовок */}
                <h1 className="text-5xl sm:text-7xl font-black text-white leading-tight mb-6 drop-shadow-lg">
                    Твой стиль.<br />
                    <span className="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-cyan-300">
                        Твои правила.
                    </span>
                </h1>

                {/* Описание */}
                <p className="text-lg sm:text-xl text-gray-300 font-medium mb-10 leading-relaxed max-w-xl">
                    Откройте для себя премиальные товары по честным ценам.
                    Быстрая доставка, кэшбек на баланс и гарантия качества.
                </p>

            </div>
        </div>
    );
}
