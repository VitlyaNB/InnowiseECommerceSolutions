import React, { useEffect, useState } from 'react';
import api from '../api';
import { Link } from 'react-router-dom';
import { ShoppingCart } from 'lucide-react';
import Hero from '../components/Hero'; // <--- Импортируем наш новый баннер

export default function Catalog() {
    const [products, setProducts] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        api.get('/products')
            .then(res => setProducts(res.data.data || res.data))
            .catch(err => console.error(err))
            .finally(() => setLoading(false));
    }, []);

    const addToCart = async (e, productId) => {
        e.preventDefault();
        try {
            await api.post('/cart', { product_id: productId, quantity: 1 });
            alert('Добавлено в корзину');
        } catch (error) {
            alert('Нужно авторизоваться');
        }
    };

    if (loading) return <div className="p-20 text-center text-gray-500">Загрузка витрины...</div>;

    return (
        <div className="max-w-7xl mx-auto px-4 py-6">

            {/* ВСТАВЛЯЕМ КРАСИВУЮ ШАПКУ ЗДЕСЬ */}
            <Hero />

            {/* Якорь для скролла */}
            <div id="products-grid" className="mb-8 flex items-end justify-between">
                <div>
                    <h2 className="text-3xl font-black text-gray-900 dark:text-white">Популярные товары</h2>
                    <p className="text-gray-500 dark:text-gray-400 mt-1">Выбор наших покупателей</p>
                </div>
            </div>

            {products.length === 0 ? (
                <p className="text-center text-gray-500 py-20">Товаров пока нет.</p>
            ) : (
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    {products.map(product => (
                        <Link
                            key={product.id}
                            to={`/product/${product.id}`}
                            className="group bg-white dark:bg-gray-800 rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden border border-gray-100 dark:border-gray-700 flex flex-col"
                        >
                            <div className="relative aspect-square bg-gray-100 dark:bg-gray-900 overflow-hidden">
                                {product.images && product.images.length > 0 ? (
                                    <img src={product.images[0].url} alt={product.name} className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
                                ) : (
                                    <div className="w-full h-full flex items-center justify-center text-gray-400 font-bold">NO PHOTO</div>
                                )}
                                <div className="absolute top-3 right-3 bg-white/90 dark:bg-gray-900/90 backdrop-blur px-2 py-1 rounded-lg text-xs font-bold shadow-sm">
                                    {product.quantity > 0 ? 'В наличии' : 'Нет на складе'}
                                </div>
                            </div>

                            <div className="p-5 flex flex-col flex-1">
                                <h3 className="font-bold text-gray-900 dark:text-white text-lg leading-tight mb-2 group-hover:text-indigo-600 transition-colors">
                                    {product.name}
                                </h3>
                                <p className="text-gray-500 dark:text-gray-400 text-sm mb-4 line-clamp-2 flex-1">
                                    {product.description}
                                </p>
                                <div className="flex items-center justify-between mt-auto">
                                    <span className="text-xl font-black text-indigo-600">
                                        {parseFloat(product.price).toFixed(2)} <span className="text-xs">BYN</span>
                                    </span>
                                    <button
                                        onClick={(e) => addToCart(e, product.id)}
                                        className="bg-gray-900 dark:bg-white text-white dark:text-black p-2.5 rounded-xl hover:bg-indigo-600 dark:hover:bg-indigo-400 hover:text-white transition-colors"
                                    >
                                        <ShoppingCart className="w-5 h-5" />
                                    </button>
                                </div>
                            </div>
                        </Link>
                    ))}
                </div>
            )}
        </div>
    );
}
