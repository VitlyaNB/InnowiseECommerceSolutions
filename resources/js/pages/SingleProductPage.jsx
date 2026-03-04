import React, { useEffect, useState } from 'react';
import { useParams } from 'react-router-dom';
import api from '../api';

export default function SingleProductPage() {
    const { productId } = useParams(); // Берем ID из URL
    const [product, setProduct] = useState(null);

    useEffect(() => {
        // Запрашиваем товар с сервера
        api.get(`/products/${productId}`)
            .then(res => setProduct(res.data.data || res.data))
            .catch(err => console.error(err));
    }, [productId]);

    const addToCart = async () => {
        try {
            await api.post('/cart', { product_id: product.id, quantity: 1 });
            alert('Товар в корзине');
        } catch (error) {
            alert('Ошибка добавления');
        }
    };

    if (!product) return <div className="p-10 text-center">Загрузка...</div>;

    return (
        <div className="max-w-7xl mx-auto px-4 py-10">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
                {/* Фото */}
                <div className="bg-gray-100 rounded-xl overflow-hidden aspect-square">
                    {product.images && product.images.length > 0 ? (
                        <img src={product.images[0].url} alt={product.name} className="w-full h-full object-cover" />
                    ) : (
                        <div className="w-full h-full flex items-center justify-center text-gray-400">Нет фото</div>
                    )}
                </div>

                {/* Инфо */}
                <div>
                    <h1 className="text-3xl font-black mb-4 dark:text-white">{product.name}</h1>
                    <p className="text-2xl font-bold text-indigo-600 mb-6">{product.price} BYN</p>
                    <p className="text-gray-600 dark:text-gray-300 mb-8">{product.description}</p>

                    <button
                        onClick={addToCart}
                        className="bg-black text-white px-8 py-4 rounded-xl font-bold hover:bg-gray-800 w-full md:w-auto"
                    >
                        Добавить в корзину
                    </button>

                    <p className="mt-4 text-sm text-gray-500">
                        Остаток: {product.quantity} шт.
                    </p>
                </div>
            </div>
        </div>
    );
}
