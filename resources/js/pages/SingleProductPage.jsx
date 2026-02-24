import React, { useEffect, useState } from 'react';
import { useParams, Link } from 'react-router-dom';
import axios from 'axios';
import { ArrowLeft, ShoppingCart, Star, Truck, ShieldCheck } from 'lucide-react';

export default function SingleProductPage() {
    const { productId } = useParams();
    const [product, setProduct] = useState(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        axios.get(`/api/products/${productId}`)
            .then(res => setProduct(res.data.data || res.data))
            .catch(err => console.error(err))
            .finally(() => setLoading(false));
    }, [productId]);

    if (loading) return <div className="min-h-[calc(100vh-80px)] flex justify-center items-center"><div className="animate-spin rounded-full h-16 w-16 border-t-2 border-indigo-600"></div></div>;
    if (!product) return <div className="min-h-[calc(100vh-80px)] flex justify-center items-center"><h1 className="text-2xl font-bold">Товар не найден</h1></div>;

    return (
        <div className="min-h-[calc(100vh-80px)] bg-white">
            <div className="max-w-7xl mx-auto px-4 py-8 sm:px-6 lg:px-8">
                <Link to="/catalog" className="inline-flex items-center gap-2 text-gray-400 hover:text-indigo-600 mb-8 transition-colors font-bold">
                    <ArrowLeft className="w-5 h-5" /> Вернуться в каталог
                </Link>

                <div className="grid grid-cols-1 md:grid-cols-2 gap-12 lg:gap-20">
                    <div className="aspect-[4/5] bg-gray-100 rounded-3xl overflow-hidden shadow-sm">
                        <img src={`https://picsum.photos/seed/${product.id}/800/1000`} alt={product.name} className="w-full h-full object-cover" />
                    </div>

                    <div className="flex flex-col py-6">
                        <h1 className="text-4xl md:text-5xl font-black text-gray-900 mb-4 tracking-tight">{product.name}</h1>
                        <div className="flex items-center gap-4 mb-8">
                            <div className="flex text-yellow-400"><Star fill="currentColor" className="w-5 h-5"/><Star fill="currentColor" className="w-5 h-5"/><Star fill="currentColor" className="w-5 h-5"/><Star fill="currentColor" className="w-5 h-5"/><Star fill="currentColor" className="w-5 h-5"/></div>
                            <span className="text-gray-500 font-medium">В наличии: {product.quantity} шт.</span>
                        </div>

                        <p className="text-4xl font-black text-indigo-600 mb-8">{product.price} ₽</p>

                        <div className="prose text-gray-600 mb-10 leading-relaxed text-lg">
                            <p>{product.description || 'Премиальное качество. Идеально подойдет для вашего гардероба.'}</p>
                        </div>

                        <button className="flex items-center justify-center gap-3 w-full bg-black text-white font-bold py-5 rounded-2xl hover:bg-indigo-600 transition-all shadow-lg hover:-translate-y-1 mt-auto">
                            <ShoppingCart className="w-6 h-6" /> Добавить в корзину
                        </button>

                        <div className="grid grid-cols-2 gap-4 mt-10 border-t border-gray-100 pt-8">
                            <div className="flex items-center gap-3"><Truck className="w-6 h-6 text-indigo-600"/><span className="font-bold text-sm text-gray-700">Доставка 1-2 дня</span></div>
                            <div className="flex items-center gap-3"><ShieldCheck className="w-6 h-6 text-indigo-600"/><span className="font-bold text-sm text-gray-700">Оригинал 100%</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
