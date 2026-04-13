import React, { useEffect, useState } from 'react';
import { useParams, Link } from 'react-router-dom';
import api from '../api';
import { ShoppingCart, ArrowLeft, Check } from 'lucide-react';
import Reviews from '../components/Reviews';
import RecommendationGrid from '../components/RecommendationGrid';
import { useAuth } from '../contexts/AuthContext';

export default function SingleProductPage() {
    const { id } = useParams();
    const [product, setProduct] = useState(null);
    const [loading, setLoading] = useState(true);
    const [mainImage, setMainImage] = useState(null);
    const [recs, setRecs] = useState({ also_bought: [], similar: [], recently_viewed: [] });
    const { loadCartCount } = useAuth();

    useEffect(() => {
        setLoading(true);
        api.get(`/products/${id}`)
            .then(res => {
                const data = res.data.data || res.data;
                setProduct(data);
                if (data.images && data.images.length > 0) {
                    setMainImage(data.images[0].url);
                }
            })
            .catch(err => console.error("Ошибка загрузки товара:", err))
            .finally(() => setLoading(false));

        api.post(`/products/${id}/view`).catch(() => {});
        api.get(`/products/${id}/recommendations`)
            .then(res => {
                setRecs({
                    also_bought: res.data.also_bought?.data || res.data.also_bought || [],
                    similar: res.data.similar?.data || res.data.similar || [],
                    recently_viewed: res.data.recently_viewed?.data || res.data.recently_viewed || [],
                });
            })
            .catch(() => {});
    }, [id]);

    const addToCart = async () => {
        try {
            await api.post('/cart', { product_id: product.id, quantity: 1 });
            await loadCartCount();
            alert('Товар успешно добавлен в корзину');
        } catch (err) {
            alert(err.response?.data?.message || 'Ошибка при добавлении товара');
        }
    };

    if (loading) return (
        <div className="flex justify-center items-center min-h-[60vh]">
            <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
        </div>
    );

    if (!product) return (
        <div className="max-w-7xl mx-auto px-4 py-20 text-center">
            <h1 className="text-2xl font-bold text-gray-800 dark:text-white mb-4">Товар не найден</h1>
            <Link to="/" className="text-indigo-600 font-bold hover:underline">Вернуться в каталог</Link>
        </div>
    );

    return (
        <div className="max-w-7xl mx-auto px-4 py-10">
            <Link to="/" className="inline-flex items-center gap-2 text-gray-500 hover:text-indigo-600 mb-8 font-bold transition-colors">
                <ArrowLeft className="w-4 h-4" /> Назад в магазин
            </Link>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-12">
                {/* Секция изображений */}
                <div className="space-y-4">
                    <div className="aspect-square bg-gray-100 dark:bg-gray-800 rounded-3xl overflow-hidden border border-gray-200 dark:border-gray-700 shadow-inner">
                        {mainImage ? (
                            <img src={mainImage} className="w-full h-full object-cover transition-transform hover:scale-105 duration-500" alt={product.name} />
                        ) : (
                            <div className="w-full h-full flex items-center justify-center text-gray-400 font-bold uppercase tracking-widest">Нет изображения</div>
                        )}
                    </div>
                    {product.images && product.images.length > 1 && (
                        <div className="flex gap-4 overflow-x-auto pb-2 scrollbar-hide">
                            {product.images.map((img, idx) => (
                                <button
                                    key={idx}
                                    onClick={() => setMainImage(img.url)}
                                    className={`w-20 h-20 rounded-xl overflow-hidden border-2 transition-all shrink-0 ${mainImage === img.url ? 'border-indigo-600 scale-95 shadow-sm' : 'border-transparent opacity-60 hover:opacity-100'}`}
                                >
                                    <img src={img.url} className="w-full h-full object-cover" alt={`${product.name} view ${idx + 1}`} />
                                </button>
                            ))}
                        </div>
                    )}
                </div>

                {/* Информационная секция */}
                <div>
                    <h1 className="text-4xl font-black text-gray-900 dark:text-white mb-4 leading-tight">{product.name}</h1>
                    <div className="text-3xl font-black text-indigo-600 mb-6">
                        {parseFloat(product.price).toFixed(2)} BYN
                    </div>

                    <div className="bg-gray-50 dark:bg-gray-800/50 p-6 rounded-2xl mb-8 border border-gray-100 dark:border-gray-700">
                        <p className="text-gray-600 dark:text-gray-300 leading-relaxed italic">
                            {product.description || "Описание товара временно отсутствует."}
                        </p>
                    </div>

                    <div className="flex flex-col sm:flex-row gap-4 mb-8">
                        <button
                            onClick={addToCart}
                            className="flex-1 px-8 py-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl font-black text-lg transition-all shadow-lg active:scale-95 flex items-center justify-center gap-3"
                        >
                            <ShoppingCart className="w-6 h-6" /> Добавить в корзину
                        </button>
                    </div>

                    <div className="border-t border-gray-100 dark:border-gray-700 pt-6 space-y-4">
                        <div className="flex items-center gap-3 text-sm font-bold text-gray-600 dark:text-gray-400">
                            <Check className="w-5 h-5 text-green-500 bg-green-50 dark:bg-green-900/30 rounded-full p-0.5" />
                            В наличии на складе: {product.quantity} шт.
                        </div>
                        <div className="flex items-center gap-3 text-sm font-bold text-gray-600 dark:text-gray-400">
                            <Check className="w-5 h-5 text-green-500 bg-green-50 dark:bg-green-900/30 rounded-full p-0.5" />
                            Официальная гарантия и поддержка
                        </div>
                    </div>
                </div>
            </div>

            {/* Блок отзывов */}
            <Reviews productId={id} />

            <RecommendationGrid
                title="Похожие товары"
                subtitle="На основе категории и описания"
                items={recs.similar}
            />
            <RecommendationGrid
                title="С этим также покупают"
                subtitle="Частые сочетания в заказах"
                items={recs.also_bought}
            />
            <RecommendationGrid
                title="Вы недавно смотрели"
                subtitle="История просмотров"
                items={recs.recently_viewed}
            />
        </div>
    );
}
