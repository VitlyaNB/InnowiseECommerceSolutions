import React, { useEffect, useMemo, useState } from 'react';
import api from '../api';
import { Link } from 'react-router-dom';
import { ShoppingCart } from 'lucide-react';
import Hero from '../components/Hero';
import RecommendationGrid from '../components/RecommendationGrid';
import { useAuth } from '../contexts/AuthContext';

export default function Catalog() {
    const [products, setProducts] = useState([]);
    const [homeRecs, setHomeRecs] = useState([]);
    const [loading, setLoading] = useState(true);
    const [productsLoading, setProductsLoading] = useState(false);
    const [productsError, setProductsError] = useState(null);
    const [categories, setCategories] = useState([]);
    const [filters, setFilters] = useState({ categoryId: '', priceMin: '', priceMax: '', inStock: false });
    const [pendingFilters, setPendingFilters] = useState({ categoryId: '', priceMin: '', priceMax: '', inStock: false });
    const [isFilterOpen, setIsFilterOpen] = useState(false);
    const { loadCartCount } = useAuth();

    useEffect(() => {
        api.get('/recommendations/home')
            .then(recsRes => {
                setHomeRecs(recsRes.data.items?.data || recsRes.data.items || []);
            })
            .catch(err => console.error(err))
            .finally(() => setLoading(false));
    }, []);

    useEffect(() => {
        api.get('/categories')
            .then(res => setCategories(res.data.data || res.data))
            .catch(() => setCategories([]));
    }, []);

    useEffect(() => {
        const handleOpenFilters = () => {
            setPendingFilters(filters);
            setIsFilterOpen(true);
        };
        window.addEventListener('open-filters', handleOpenFilters);
        return () => window.removeEventListener('open-filters', handleOpenFilters);
    }, [filters]);

    useEffect(() => {
        setProductsLoading(true);
        setProductsError(null);

        const params = new URLSearchParams();
        if (filters.categoryId) params.set('category_id', filters.categoryId);
        if (filters.priceMin) params.set('price_min', filters.priceMin);
        if (filters.priceMax) params.set('price_max', filters.priceMax);
        if (filters.inStock) params.set('in_stock', '1');

        api.get(`/products?${params.toString()}`)
            .then(res => {
                setProducts(res.data.data || res.data || []);
            })
            .catch(err => {
                console.error("Ошибка при загрузке товаров:", err);
                setProductsError("Не удалось загрузить товары.");
                setProducts([]);
            })
            .finally(() => setProductsLoading(false));
    }, [filters]);

    const categoryOptions = useMemo(() => {
        if (categories.length === 0) return [];
        return categories.map(item => ({
            id: item.id,
            name: item.name,
        }));
    }, [categories]);

    const addToCart = async (e, productId) => {
        e.preventDefault();
        try {
            await api.post('/cart', { product_id: productId, quantity: 1 });
            await loadCartCount();
            alert('Добавлено в корзину');
        } catch (error) {
            alert(error.response?.data?.message || 'Ошибка');
        }
    };

    if (loading) return <div className="flex justify-center p-20"><div className="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div></div>;

    return (
        <div className="max-w-7xl mx-auto px-4 py-8">
            <Hero />

            <div className="mb-10 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h2 className="text-4xl font-black text-slate-900 dark:text-white tracking-tight">Каталог</h2>
                    <p className="text-slate-500 dark:text-slate-400 mt-2 font-medium">Все товары в одном месте</p>
                </div>
            </div>

            {productsLoading ? (
                <div className="flex justify-center py-20">
                    <div className="text-slate-500 dark:text-slate-400 text-lg">Загрузка...</div>
                </div>
            ) : productsError ? (
                <div className="text-center py-10 text-red-500">
                    {productsError}
                </div>
            ) : products.length === 0 ? (
                <div className="text-center py-20 text-slate-400 font-bold">
                    Товаров пока нет.
                </div>
            ) : (
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                    {products.map(product => (
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
                                {product.quantity > 0 ? (
                                    <div className="absolute top-4 right-4 bg-white/90 dark:bg-black/80 backdrop-blur px-3 py-1 rounded-full text-xs font-bold shadow-sm">
                                        В наличии
                                    </div>
                                ) : (
                                    <div className="absolute inset-0 bg-black/50 flex items-center justify-center">
                                        <span className="bg-white text-black px-4 py-2 rounded-full font-bold uppercase tracking-widest text-xs">Нет на складе</span>
                                    </div>
                                )}
                            </div>

                            <div className="p-6 flex flex-col flex-1">
                                <h3 className="font-bold text-slate-900 dark:text-white text-lg leading-tight mb-2 group-hover:text-indigo-600 transition-colors line-clamp-2">
                                    {product.name}
                                </h3>
                                <div className="mt-auto pt-4 flex items-center justify-between">
                                    <span className="text-2xl font-black text-slate-900 dark:text-white">
                                        {parseFloat(product.price).toFixed(2)} <span className="text-sm text-slate-400 font-medium">BYN</span>
                                    </span>
                                    <button
                                        onClick={(e) => addToCart(e, product.id)}
                                        className="w-12 h-12 bg-slate-900 dark:bg-white text-white dark:text-slate-900 rounded-full flex items-center justify-center hover:scale-110 transition-transform shadow-lg"
                                    >
                                        <ShoppingCart className="w-5 h-5" />
                                    </button>
                                </div>
                            </div>
                        </Link>
                    ))}
                </div>
            )}

            {!(filters.categoryId || filters.priceMin || filters.priceMax || filters.inStock) && (
                <RecommendationGrid
                    title="Рекомендации для вас"
                    subtitle="Собрано на основе ваших просмотров"
                    items={homeRecs}
                />
            )}

            {isFilterOpen && (
                <div
                    className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4"
                    onClick={() => setIsFilterOpen(false)}
                >
                    <div
                        className="w-full max-w-md bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-700 p-6 shadow-2xl"
                        onClick={(e) => e.stopPropagation()}
                    >
                        <div className="flex items-center justify-between mb-6">
                            <h3 className="text-sm font-black uppercase tracking-widest text-slate-500 dark:text-slate-400">Фильтры</h3>
                            <button
                                onClick={() => setPendingFilters({ categoryId: '', priceMin: '', priceMax: '', inStock: false })}
                                className="text-xs font-bold text-indigo-600 hover:text-indigo-700"
                            >
                                Сбросить
                            </button>
                        </div>

                        <div className="space-y-6">
                            <div>
                                <div className="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Категории</div>
                                <div className="space-y-2 max-h-52 overflow-y-auto pr-1">
                                    {categoryOptions.length === 0 && (
                                        <div className="text-xs text-slate-400">Нет данных</div>
                                    )}
                                    {categoryOptions.map(cat => (
                                        <label key={cat.id} className="flex items-center justify-between text-sm text-slate-700 dark:text-slate-300">
                                            <span className="flex items-center gap-2">
                                                <input
                                                    type="radio"
                                                    name="category"
                                                    className="accent-indigo-600"
                                                    checked={pendingFilters.categoryId === String(cat.id)}
                                                    onChange={() => setPendingFilters(prev => ({ ...prev, categoryId: String(cat.id) }))}
                                                />
                                                {cat.name}
                                            </span>
                                        </label>
                                    ))}
                                    <label className="flex items-center gap-2 text-sm text-slate-500 mt-2">
                                        <input
                                            type="radio"
                                            name="category"
                                            className="accent-indigo-600"
                                            checked={!pendingFilters.categoryId}
                                            onChange={() => setPendingFilters(prev => ({ ...prev, categoryId: '' }))}
                                        />
                                        Все категории
                                    </label>
                                </div>
                            </div>

                            <div>
                                <div className="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Цена</div>
                                <div className="flex gap-2">
                                    <input
                                        type="number"
                                        min="0"
                                        placeholder="от"
                                        value={pendingFilters.priceMin}
                                        onChange={e => setPendingFilters(prev => ({ ...prev, priceMin: e.target.value }))}
                                        className="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2 text-sm bg-white dark:bg-slate-950 dark:text-white"
                                    />
                                    <input
                                        type="number"
                                        min="0"
                                        placeholder="до"
                                        value={pendingFilters.priceMax}
                                        onChange={e => setPendingFilters(prev => ({ ...prev, priceMax: e.target.value }))}
                                        className="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2 text-sm bg-white dark:bg-slate-950 dark:text-white"
                                    />
                                </div>
                            </div>

                            <label className="flex items-center gap-3 text-sm text-slate-700 dark:text-slate-300">
                                <input
                                    type="checkbox"
                                    className="accent-indigo-600"
                                    checked={pendingFilters.inStock}
                                    onChange={e => setPendingFilters(prev => ({ ...prev, inStock: e.target.checked }))}
                                />
                                Только в наличии
                            </label>

                            <div className="flex items-center justify-end gap-3 pt-2">
                                <button
                                    onClick={() => setIsFilterOpen(false)}
                                    className="px-4 py-2 rounded-full text-sm font-bold text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white"
                                >
                                    Закрыть
                                </button>
                                <button
                                    onClick={() => {
                                        setFilters(pendingFilters);
                                        setIsFilterOpen(false);
                                    }}
                                    className="px-5 py-2.5 rounded-full bg-indigo-600 text-white text-sm font-bold hover:bg-indigo-700 transition-colors"
                                >
                                    Применить
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}
