import React, { useEffect, useMemo, useState } from 'react';
import { useSearchParams, Link } from 'react-router-dom';
import api from '../api';

export default function SearchPage() {
    const [searchParams] = useSearchParams();
    const query = searchParams.get('q');
    const [products, setProducts] = useState([]);
    const [facets, setFacets] = useState({ categories: [], price: { min: 0, max: 0 } });
    const [categories, setCategories] = useState([]);
    const [filters, setFilters] = useState({ categoryId: '', priceMin: '', priceMax: '', inStock: false });
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);

    useEffect(() => {
        api.get('/categories')
            .then(res => setCategories(res.data.data || res.data))
            .catch(() => setCategories([]));
    }, []);

    useEffect(() => {
        if (query) {
            setLoading(true);
            setError(null);

            const params = new URLSearchParams();
            params.set('query', query);
            if (filters.categoryId) params.set('category_id', filters.categoryId);
            if (filters.priceMin) params.set('price_min', filters.priceMin);
            if (filters.priceMax) params.set('price_max', filters.priceMax);
            if (filters.inStock) params.set('in_stock', '1');

            api.get(`/products/search?${params.toString()}`)
                .then(res => {
                    // Laravel Resource + Pagination возвращает данные в res.data.data
                    // Если пагинации нет, то просто res.data.data
                    setProducts(res.data.data || []);
                    setFacets(res.data.meta?.facets || { categories: [], price: { min: 0, max: 0 } });
                })
                .catch(err => {
                    console.error("Ошибка при поиске:", err);
                    setError("Не удалось загрузить результаты поиска.");
                    setProducts([]);
                })
                .finally(() => setLoading(false));
        } else {
            setProducts([]);
        }
    }, [query, filters]);

    const categoryOptions = useMemo(() => {
        const map = new Map(categories.map(c => [c.id, c.name]));
        return (facets.categories || []).map(item => ({
            id: item.id,
            name: map.get(item.id) || `Категория #${item.id}`,
            count: item.count,
        }));
    }, [categories, facets]);

    const resetFilters = () => {
        setFilters({ categoryId: '', priceMin: '', priceMax: '', inStock: false });
    };

    return (
        <div className="max-w-7xl mx-auto px-4 py-8">
            <h1 className="text-2xl font-bold mb-6 dark:text-white">
                {query ? `Результаты поиска: "${query}"` : 'Введите запрос для поиска'}
            </h1>

            {query ? (
                <div className="grid grid-cols-1 lg:grid-cols-[280px_1fr] gap-8 mb-8">
                    <div className="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-5 h-fit">
                        <div className="flex items-center justify-between mb-4">
                            <h2 className="text-sm font-black uppercase tracking-widest text-gray-500 dark:text-gray-400">Фильтры</h2>
                            <button onClick={resetFilters} className="text-xs font-bold text-indigo-600 hover:text-indigo-700">Сбросить</button>
                        </div>

                        <div className="space-y-6">
                            <div>
                                <div className="text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Категории</div>
                                <div className="space-y-2 max-h-48 overflow-y-auto pr-1">
                                    {categoryOptions.length === 0 && (
                                        <div className="text-xs text-gray-400">Нет данных</div>
                                    )}
                                    {categoryOptions.map(cat => (
                                        <label key={cat.id} className="flex items-center justify-between text-sm text-gray-700 dark:text-gray-300">
                                            <span className="flex items-center gap-2">
                                                <input
                                                    type="radio"
                                                    name="category"
                                                    className="accent-indigo-600"
                                                    checked={filters.categoryId === String(cat.id)}
                                                    onChange={() => setFilters(prev => ({ ...prev, categoryId: String(cat.id) }))}
                                                />
                                                {cat.name}
                                            </span>
                                            <span className="text-xs text-gray-400">{cat.count}</span>
                                        </label>
                                    ))}
                                    <label className="flex items-center gap-2 text-sm text-gray-500 mt-2">
                                        <input
                                            type="radio"
                                            name="category"
                                            className="accent-indigo-600"
                                            checked={!filters.categoryId}
                                            onChange={() => setFilters(prev => ({ ...prev, categoryId: '' }))}
                                        />
                                        Все категории
                                    </label>
                                </div>
                            </div>

                            <div>
                                <div className="text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Цена</div>
                                <div className="flex gap-2">
                                    <input
                                        type="number"
                                        min="0"
                                        placeholder={facets.price?.min ? `от ${Math.floor(facets.price.min)}` : 'от'}
                                        value={filters.priceMin}
                                        onChange={e => setFilters(prev => ({ ...prev, priceMin: e.target.value }))}
                                        className="w-full rounded-xl border border-gray-200 dark:border-gray-700 px-3 py-2 text-sm bg-white dark:bg-gray-900 dark:text-white"
                                    />
                                    <input
                                        type="number"
                                        min="0"
                                        placeholder={facets.price?.max ? `до ${Math.ceil(facets.price.max)}` : 'до'}
                                        value={filters.priceMax}
                                        onChange={e => setFilters(prev => ({ ...prev, priceMax: e.target.value }))}
                                        className="w-full rounded-xl border border-gray-200 dark:border-gray-700 px-3 py-2 text-sm bg-white dark:bg-gray-900 dark:text-white"
                                    />
                                </div>
                            </div>

                            <label className="flex items-center gap-3 text-sm text-gray-700 dark:text-gray-300">
                                <input
                                    type="checkbox"
                                    className="accent-indigo-600"
                                    checked={filters.inStock}
                                    onChange={e => setFilters(prev => ({ ...prev, inStock: e.target.checked }))}
                                />
                                Только в наличии
                            </label>
                        </div>
                    </div>

                    <div>
                        {loading ? (
                            <div className="flex justify-center py-20">
                                <div className="text-gray-500 dark:text-gray-400 text-lg">Загрузка...</div>
                            </div>
                        ) : error ? (
                            <div className="text-center py-10 text-red-500">
                                {error}
                            </div>
                        ) : products.length > 0 ? (
                            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                                {products.map((product) => (
                                    <Link
                                        key={product.id}
                                        to={`/product/${product.id}`}
                                        className="group bg-white dark:bg-gray-800 rounded-3xl overflow-hidden border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-xl transition-all flex flex-col"
                                    >
                                        <div className="aspect-[4/5] bg-gray-200 dark:bg-gray-700 relative overflow-hidden">
                                            <img
                                                src={product.images?.[0]?.url || 'https://placehold.co/400x500?text=No+Image'}
                                                alt={product.name}
                                                className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                            />
                                        </div>
                                        <div className="p-6 flex flex-col flex-1">
                                            <h3 className="text-lg font-bold text-gray-900 dark:text-white mb-2">{product.name}</h3>
                                            <div className="flex items-center justify-between mt-auto pt-4 border-t border-gray-100 dark:border-gray-700">
                                                <span className="text-xl font-black text-gray-900 dark:text-white">{product.price} BYN</span>
                                            </div>
                                        </div>
                                    </Link>
                                ))}
                            </div>
                        ) : (
                            <div className="text-center py-10 text-gray-500 dark:text-gray-400">
                                По вашему запросу ничего не найдено.
                            </div>
                        )}
                    </div>
                </div>
            ) : (
                <div className="text-center py-10 text-gray-500 dark:text-gray-400">
                    Введите запрос для поиска.
                </div>
            )}
        </div>
    );
}
