import React, { useEffect, useState } from 'react';
import { useParams, Link, useNavigate } from 'react-router-dom';
import api from '../api';
import ImageWithFallback from '../components/ImageWithFallback';
import {
    ShoppingCart,
    ArrowLeft,
    ShieldCheck,
    Truck,
    Zap,
    CheckCircle2,
    Info
} from 'lucide-react';

export default function SingleProductPage() {
    const { productId: id } = useParams();
    const navigate = useNavigate();
    const [product, setProduct] = useState(null);
    const [loading, setLoading] = useState(true);
    const [activeImage, setActiveImage] = useState(0);
    const [addingToCart, setAddingToCart] = useState(false);

    useEffect(() => {
        setLoading(true);
        api.get(`/products/${id}`)
            .then(res => {
                const data = res.data.data || res.data;
                setProduct(data);
            })
            .catch(err => console.error("Ошибка при загрузке товара:", err))
            .finally(() => setLoading(false));
    }, [id]);

    const addToCart = async () => {
        if (!product || product.quantity === 0) return;
        setAddingToCart(true);
        try {
            await api.post('/cart', { product_id: product.id, quantity: 1 });
            navigate('/cart');
        } catch (err) {
            console.error('Ошибка добавления в корзину:', err);
        } finally {
            setAddingToCart(false);
        }
    };

    if (loading) {
        return (
            <div className="min-h-screen flex items-center justify-center bg-white dark:bg-gray-900">
                <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-indigo-600"></div>
            </div>
        );
    }

    if (!product) {
        return (
            <div className="min-h-screen flex flex-col items-center justify-center bg-white dark:bg-gray-900">
                <h2 className="text-2xl font-bold text-gray-800 dark:text-gray-200 mb-4">Товар не найден</h2>
                <Link to="/" className="text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-2">
                    <ArrowLeft className="w-4 h-4" /> Вернуться в каталог
                </Link>
            </div>
        );
    }

    const hasImages = product.images && product.images.length > 0;
    const mainImageUrl = hasImages ? product.images[activeImage].url : null;

    return (
        <div className="min-h-screen bg-white dark:bg-gray-900 pb-20 transition-colors duration-300">
            {/* Навигация */}
            <nav className="max-w-7xl mx-auto px-4 py-6">
                <Link to="/" className="inline-flex items-center text-sm font-medium text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors gap-2">
                    <ArrowLeft className="w-4 h-4" /> Назад к покупкам
                </Link>
            </nav>

            <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16">

                    {/* Левая колонка: Галерея */}
                    <div className="space-y-4">
                        <div className="aspect-[4/5] bg-gray-50 dark:bg-gray-800 rounded-3xl overflow-hidden border border-gray-100 dark:border-gray-700 shadow-sm relative">
                            <ImageWithFallback
                                src={mainImageUrl}
                                alt={product.name}
                                className="w-full h-full object-cover transition-opacity duration-300"
                            />
                            {product.quantity === 0 && (
                                <div className="absolute top-4 left-4 bg-white/90 dark:bg-gray-800/90 backdrop-blur px-4 py-2 rounded-full text-xs font-bold text-red-600 dark:text-red-400 shadow-sm">
                                    Нет в наличии
                                </div>
                            )}
                        </div>

                        {hasImages && product.images.length > 1 && (
                            <div className="grid grid-cols-5 gap-4">
                                {product.images.map((img, index) => (
                                    <button
                                        key={img.id}
                                        onClick={() => setActiveImage(index)}
                                        className={`aspect-square rounded-xl overflow-hidden border-2 transition-all ${
                                            activeImage === index
                                                ? 'border-indigo-600 scale-95'
                                                : 'border-transparent opacity-70 hover:opacity-100 dark:opacity-50 dark:hover:opacity-100'
                                        }`}
                                    >
                                        <ImageWithFallback src={img.url} alt="" className="w-full h-full object-cover" />
                                    </button>
                                ))}
                            </div>
                        )}
                    </div>

                    <div className="flex flex-col justify-start">
                        <div className="mb-6">
                            <span className="text-indigo-600 dark:text-indigo-400 text-sm font-bold tracking-widest uppercase">
                                {product.category?.name || 'Категория'}
                            </span>
                            <h1 className="text-4xl font-black text-gray-900 dark:text-white mt-2 mb-4 leading-tight">
                                {product.name}
                            </h1>
                            <div className="flex items-center gap-4">
                                <span className="text-3xl font-black text-gray-900 dark:text-white">
                                    {product.price.toLocaleString()} BYN
                                </span>
                                {product.old_price && (
                                    <span className="text-xl text-gray-400 dark:text-gray-500 line-through">
                                        {product.old_price.toLocaleString()} ₽
                                    </span>
                                )}
                            </div>
                        </div>

                        <div className="prose prose-sm text-gray-600 dark:prose-invert mb-8">
                            <p className="leading-relaxed dark:text-gray-300">
                                {product.description || 'Описание товара скоро появится. Мы работаем над тем, чтобы предоставить вам максимально подробную информацию.'}
                            </p>
                        </div>

                        {/* Характеристики (Оригинал, Быстрая доставка) */}
                        <div className="grid grid-cols-2 gap-4 mb-8">
                            <div className="p-4 bg-gray-50 dark:bg-gray-800 rounded-2xl flex items-center gap-3 border border-transparent dark:border-gray-700">
                                <CheckCircle2 className="w-5 h-5 text-green-500" />
                                <span className="text-sm font-medium text-gray-700 dark:text-gray-200">Оригинал</span>
                            </div>
                            <div className="p-4 bg-gray-50 dark:bg-gray-800 rounded-2xl flex items-center gap-3 border border-transparent dark:border-gray-700">
                                <Truck className="w-5 h-5 text-indigo-500 dark:text-indigo-400" />
                                <span className="text-sm font-medium text-gray-700 dark:text-gray-200">Быстрая доставка</span>
                            </div>
                        </div>

                        {/* Кнопка покупки */}
                        <div className="space-y-4">
                            <button
                                onClick={addToCart}
                                disabled={product.quantity === 0 || addingToCart}
                                className={`w-full py-5 rounded-2xl font-bold flex items-center justify-center gap-3 transition-all shadow-lg ${
                                    product.quantity > 0
                                        ? 'bg-gray-900 text-white dark:bg-indigo-600 dark:hover:bg-indigo-700 hover:bg-indigo-600 active:scale-95'
                                        : 'bg-gray-200 text-gray-400 dark:bg-gray-800 dark:text-gray-600 cursor-not-allowed'
                                }`}
                            >
                                <ShoppingCart className="w-6 h-6" />
                                {addingToCart ? 'Добавление...' : (product.quantity > 0 ? 'Добавить в корзину' : 'Нет в наличии')}
                            </button>

                            <p className="text-center text-xs text-gray-400 dark:text-gray-500 flex items-center justify-center gap-2">
                                <ShieldCheck className="w-4 h-4" /> Безопасная оплата и гарантия возврата 14 дней
                            </p>
                        </div>

                        {/* Дополнительные преимущества */}
                        <div className="mt-12 pt-8 border-t border-gray-100 dark:border-gray-800 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div className="flex gap-4">
                                <div className="bg-indigo-50 dark:bg-indigo-900/30 p-3 rounded-xl h-fit">
                                    <Zap className="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
                                </div>
                                <div>
                                    <h4 className="text-sm font-bold text-gray-900 dark:text-white">Мгновенная обработка</h4>
                                    <p className="text-xs text-gray-500 dark:text-gray-400 mt-1">Заказ поступит в работу в течение 15 минут.</p>
                                </div>
                            </div>
                            <div className="flex gap-4">
                                <div className="bg-orange-50 dark:bg-orange-900/30 p-3 rounded-xl h-fit">
                                    <Info className="w-5 h-5 text-orange-600 dark:text-orange-400" />
                                </div>
                                <div>
                                    <h4 className="text-sm font-bold text-gray-900 dark:text-white">Нужна помощь?</h4>
                                    <p className="text-xs text-gray-500 dark:text-gray-400 mt-1">Наша поддержка работает 24/7 для вас.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </main>
        </div>
    );
}
