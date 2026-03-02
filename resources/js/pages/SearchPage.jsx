import React, { useEffect, useState } from 'react';
import { useSearchParams, Link } from 'react-router-dom';
import axios from 'axios';

export default function SearchPage() {
    const [searchParams] = useSearchParams();
    const query = searchParams.get('q');
    const [products, setProducts] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        if (query) {
            setLoading(true);
            // Отправляем запрос на ваш API. Обратите внимание на ключ 'query'
            axios.get(`/api/products/search?query=${encodeURIComponent(query)}`)
                .then(res => {
                    // Laravel Resource возвращает данные в ключе 'data'
                    // При пагинации это res.data.data
                    setProducts(res.data.data || []);
                })
                .catch(err => {
                    console.error("Ошибка поиска:", err);
                    setProducts([]);
                })
                .finally(() => setLoading(false));
        }
    }, [query]);

    return (
        <div className="max-w-7xl mx-auto px-4 py-8">
            <h1 className="text-2xl font-bold mb-6 dark:text-white">
                {query ? `Результаты поиска: "${query}"` : 'Введите запрос для поиска'}
            </h1>

            {loading ? (
                <div className="text-center py-10 dark:text-gray-400">Загрузка результатов...</div>
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
    );
}
