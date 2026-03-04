import React, { useEffect, useState } from 'react';
import api from '../api';
import { Link } from 'react-router-dom';
import { ShoppingCart, Info } from 'lucide-react';

export default function Catalog() {
    const [products, setProducts] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        api.get('/products')
            .then(res => {
                setProducts(res.data.data || res.data);
            })
            .catch(err => console.error(err))
            .finally(() => setLoading(false));
    }, []);

    const addToCart = async (productId) => {
        try {
            await api.post('/cart', { product_id: productId, quantity: 1 });
            alert('Товар добавлен в корзину');
        } catch (error) {
            if (error.response && error.response.status === 401) {
                alert('Пожалуйста, войдите в систему');
            } else {
                alert('Ошибка при добавлении');
            }
        }
    };

    if (loading) return <div className="flex justify-center p-10"><div className="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div></div>;

    return (
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div className="mb-8">
                <h1 className="text-3xl font-black text-gray-900 dark:text-white mb-2">Новинки</h1>
                <p className="text-gray-500 dark:text-gray-400">Самые свежие товары нашего магазина</p>
            </div>

            {products.length === 0 ? (
                <div className="text-center py-20 bg-gray-50 dark:bg-gray-800 rounded-3xl">
                    <p className="text-gray-500 font-bold">Товары пока не добавлены.</p>
                </div>
            ) : (
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    {products.map(product => (
                        <div key={product.id} className="group bg-white dark:bg-gray-800 rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 dark:border-gray-700 overflow-hidden flex flex-col">
                            <Link to={`/products/${product.id}`} className="relative aspect-square overflow-hidden bg-gray-100 dark:bg-gray-900">
                                {product.images && product.images.length > 0 ? (
                                    <img src={product.images[0].url} alt={product.name} className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
                                ) : (
                                    <div className="w-full h-full flex items-center justify-center text-gray-400 font-bold">NO IMAGE</div>
                                )}
                                <div className="absolute bottom-3 left-3 bg-white/90 dark:bg-gray-800/90 backdrop-blur px-3 py-1 rounded-lg font-black text-gray-900 dark:text-white text-sm shadow-sm">
                                    {parseFloat(product.price).toFixed(2)} BYN
                                </div>
                            </Link>

                            <div className="p-5 flex flex-col flex-1">
                                <Link to={`/products/${product.id}`} className="block mb-2">
                                    <h3 className="font-bold text-lg text-gray-900 dark:text-white leading-tight group-hover:text-indigo-600 transition-colors line-clamp-2">
                                        {product.name}
                                    </h3>
                                </Link>
                                <p className="text-gray-500 dark:text-gray-400 text-sm mb-4 line-clamp-2 flex-1">
                                    {product.description}
                                </p>

                                <div className="flex gap-2 mt-auto">
                                    <button onClick={() => addToCart(product.id)} className="flex-1 bg-gray-900 dark:bg-white text-white dark:text-gray-900 py-3 rounded-xl font-bold text-sm hover:bg-indigo-600 dark:hover:bg-indigo-400 hover:text-white transition-all flex items-center justify-center gap-2 active:scale-95">
                                        <ShoppingCart className="w-4 h-4" /> В корзину
                                    </button>
                                </div>
                            </div>
                        </div>
                    ))}
                </div>
            )}
        </div>
    );
}
