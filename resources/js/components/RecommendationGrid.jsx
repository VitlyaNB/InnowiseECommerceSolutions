import React from 'react';
import { Link } from 'react-router-dom';

export default function RecommendationGrid({ title, subtitle, items }) {
    if (!items || items.length === 0) return null;

    return (
        <section className="mt-12">
            <div className="mb-6">
                <h2 className="text-3xl font-black text-slate-900 dark:text-white tracking-tight">{title}</h2>
                {subtitle && <p className="text-slate-500 dark:text-slate-400 mt-2 font-medium">{subtitle}</p>}
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                {items.map(product => (
                    <Link
                        key={product.id}
                        to={`/product/${product.id}`}
                        className="group bg-white dark:bg-slate-800 rounded-[2rem] hover:shadow-2xl hover:shadow-indigo-500/10 transition-all duration-500 overflow-hidden border border-slate-100 dark:border-slate-700/50 flex flex-col relative"
                    >
                        <div className="relative aspect-[4/5] bg-slate-100 dark:bg-slate-900 overflow-hidden">
                            {product.images && product.images.length > 0 ? (
                                <img src={product.images[0].url} alt={product.name} className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700" />
                            ) : (
                                <div className="w-full h-full flex items-center justify-center text-slate-300 font-black tracking-widest">NO IMG</div>
                            )}
                        </div>

                        <div className="p-6 flex flex-col flex-1">
                            <h3 className="font-bold text-slate-900 dark:text-white text-lg leading-tight mb-2 line-clamp-2">
                                {product.name}
                            </h3>
                            <div className="mt-auto pt-4 flex items-center justify-between">
                                <span className="text-2xl font-black text-slate-900 dark:text-white">
                                    {parseFloat(product.price).toFixed(2)} <span className="text-sm text-slate-400 font-medium">BYN</span>
                                </span>
                            </div>
                        </div>
                    </Link>
                ))}
            </div>
        </section>
    );
}
