import React, { useEffect, useState } from 'react';
import api from '../api';
import { Link } from 'react-router-dom';
import { ShoppingCart } from 'lucide-react';
import Hero from '../components/Hero';
import RecommendationGrid from '../components/RecommendationGrid';

export default function Catalog() {
    const [products, setProducts] = useState([]);
    const [homeRecs, setHomeRecs] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        Promise.all([
            api.get('/products'),
            api.get('/recommendations/home'),
        ])
            .then(([productsRes, recsRes]) => {
                setProducts(productsRes.data.data || productsRes.data);
                setHomeRecs(recsRes.data.items?.data || recsRes.data.items || []);
            })
            .catch(err => console.error(err))
            .finally(() => setLoading(false));
    }, []);

    const addToCart = async (e, productId) => {
        e.preventDefault();
        try {
            await api.post('/cart', { product_id: productId, quantity: 1 });
            alert('Добавлено в корзину');
        } catch (error) {
            alert(error.response?.data?.message || 'Ошибка');
        }
    };

    if (loading) return <div className="flex justify-center p-20"><div className="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div></div>;

    return (
        <div className="max-w-7xl mx-auto px-4 py-8">
            <Hero />

            <div className="mb-10">
                <h2 className="text-4xl font-black text-slate-900 dark:text-white tracking-tight">Популярное</h2>
                <p className="text-slate-500 dark:text-slate-400 mt-2 font-medium">Эксклюзивный выбор для вас</p>
            </div>

            {products.length === 0 ? (
                <div className="text-center py-20 text-slate-400 font-bold">Товаров пока нет.</div>
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

            <RecommendationGrid
                title="Рекомендации для вас"
                subtitle="Собрано на основе ваших просмотров"
                items={homeRecs}
            />
        </div>
    );
}
