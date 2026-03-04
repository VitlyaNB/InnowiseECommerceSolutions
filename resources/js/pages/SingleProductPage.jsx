import React, { useEffect, useState } from 'react';
import { useParams, Link } from 'react-router-dom';
import api from '../api';
import { ShoppingCart, ArrowLeft, Check } from 'lucide-react';

export default function SingleProductPage() {
    const { id } = useParams(); // URL: /product/:id
    const [product, setProduct] = useState(null);
    const [loading, setLoading] = useState(true);
    const [mainImage, setMainImage] = useState(null);

    useEffect(() => {
        setLoading(true);
        // Backend API: /products/{id}
        api.get(`/products/${id}`)
            .then(res => {
                const data = res.data.data || res.data;
                setProduct(data);
                if (data.images && data.images.length > 0) {
                    setMainImage(data.images[0].url);
                }
            })
            .catch(err => console.error(err))
            .finally(() => setLoading(false));
    }, [id]);

    const addToCart = async () => {
        try {
            await api.post('/cart', { product_id: product.id, quantity: 1 });
            alert('Товар успешно добавлен');
        } catch (e) {
            alert('Ошибка (возможно, нужно войти)');
        }
    };

    if (loading) return <div className="p-20 text-center">Загрузка...</div>;
    if (!product) return <div className="p-20 text-center">Товар не найден</div>;

    return (
        <div className="max-w-7xl mx-auto px-4 py-10">
            <Link to="/" className="inline-flex items-center gap-2 text-gray-500 hover:text-indigo-600 mb-8 font-bold transition-colors">
                <ArrowLeft className="w-4 h-4" /> На главную
            </Link>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-12">
                {/* Левая часть: Фото */}
                <div className="space-y-4">
                    <div className="aspect-square bg-gray-100 dark:bg-gray-800 rounded-3xl overflow-hidden border border-gray-200 dark:border-gray-700">
                        {mainImage ? (
                            <img src={mainImage} className="w-full h-full object-cover" alt={product.name} />
                        ) : (
                            <div className="w-full h-full flex items-center justify-center text-gray-400 font-bold">NO IMAGE</div>
                        )}
                    </div>
                    {/* Миниатюры */}
                    {product.images && product.images.length > 1 && (
                        <div className="flex gap-4 overflow-x-auto pb-2">
                            {product.images.map((img, idx) => (
                                <button
                                    key={idx}
                                    onClick={() => setMainImage(img.url)}
                                    className={`w-20 h-20 rounded-xl overflow-hidden border-2 transition-all ${mainImage === img.url ? 'border-indigo-600' : 'border-transparent opacity-60 hover:opacity-100'}`}
                                >
                                    <img src={img.url} className="w-full h-full object-cover" />
                                </button>
                            ))}
                        </div>
                    )}
                </div>

                {/* Правая часть: Инфо */}
                <div>
                    <h1 className="text-4xl font-black text-gray-900 dark:text-white mb-4 leading-tight">{product.name}</h1>
                    <div className="text-3xl font-black text-indigo-600 mb-6">
                        {parseFloat(product.price).toFixed(2)} BYN
                    </div>

                    <div className="bg-gray-50 dark:bg-gray-800 p-6 rounded-2xl mb-8 border border-gray-100 dark:border-gray-700">
                        <p className="text-gray-600 dark:text-gray-300 leading-relaxed">{product.description}</p>
                    </div>

                    <button
                        onClick={addToCart}
                        className="w-full sm:w-auto px-8 py-4 bg-black dark:bg-white text-white dark:text-black rounded-2xl font-bold text-lg hover:opacity-90 flex items-center justify-center gap-3 transition-all shadow-lg active:scale-95"
                    >
                        <ShoppingCart className="w-5 h-5" /> Добавить в корзину
                    </button>

                    <div className="mt-8 flex items-center gap-3 text-sm font-bold text-gray-500">
                        <Check className="w-5 h-5 text-green-500" />
                        В наличии: {product.quantity} шт.
                    </div>
                </div>
            </div>
        </div>
    );
}
