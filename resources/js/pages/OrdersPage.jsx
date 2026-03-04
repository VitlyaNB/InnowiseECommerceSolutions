import React, { useEffect, useState } from 'react';
import api from '../api';
import { Link } from 'react-router-dom';
import { Package, Calendar, MapPin, ChevronRight, Clock } from 'lucide-react';

export default function OrdersPage() {
    const [orders, setOrders] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        api.get('/orders')
            .then(res => setOrders(res.data.data))
            .catch(console.error)
            .finally(() => setLoading(false));
    }, []);

    if (loading) return <div className="p-20 text-center text-gray-500">Загрузка истории...</div>;

    if (orders.length === 0) {
        return (
            <div className="flex flex-col items-center justify-center min-h-[60vh] text-center px-4">
                <Package className="w-16 h-16 text-gray-300 mb-4" />
                <h2 className="text-2xl font-black text-gray-900 dark:text-white mb-2">Заказов пока нет</h2>
                <Link to="/" className="text-indigo-600 font-bold hover:underline">Начать покупки</Link>
            </div>
        );
    }

    return (
        <div className="max-w-4xl mx-auto px-4 py-10">
            <h1 className="text-3xl font-black text-gray-900 dark:text-white mb-8">История заказов</h1>

            <div className="space-y-6">
                {orders.map(order => (
                    <div key={order.id} className="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                        {/* Хедер заказа */}
                        <div className="bg-gray-50 dark:bg-gray-700/50 p-6 flex flex-wrap gap-6 justify-between items-center border-b border-gray-100 dark:border-gray-700">
                            <div className="flex gap-6">
                                <div>
                                    <p className="text-xs text-gray-500 uppercase font-bold mb-1">Дата заказа</p>
                                    <p className="font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                        <Calendar className="w-4 h-4 text-gray-400" />
                                        {new Date(order.created_at).toLocaleDateString()}
                                    </p>
                                </div>
                                <div>
                                    <p className="text-xs text-gray-500 uppercase font-bold mb-1">Сумма</p>
                                    <p className="font-black text-indigo-600">{parseFloat(order.total_amount).toFixed(2)} BYN</p>
                                </div>
                                <div>
                                    <p className="text-xs text-gray-500 uppercase font-bold mb-1">Статус</p>
                                    <span className="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-black uppercase tracking-wider">
                                        {order.status}
                                    </span>
                                </div>
                            </div>
                            <div className="text-right">
                                <p className="text-xs text-gray-500 uppercase font-bold mb-1">Номер заказа</p>
                                <p className="font-mono text-gray-900 dark:text-white">#{order.id}</p>
                            </div>
                        </div>

                        {/* Список товаров */}
                        <div className="p-6">
                            {order.items.map(item => (
                                <div key={item.id} className="flex items-center gap-4 py-4 border-b border-gray-50 dark:border-gray-700 last:border-0 last:pb-0 first:pt-0">
                                    <div className="w-20 h-20 bg-gray-100 dark:bg-gray-900 rounded-xl overflow-hidden shrink-0">
                                        {item.product.images?.[0] ? (
                                            <img src={item.product.images[0].url} className="w-full h-full object-cover" />
                                        ) : <div className="w-full h-full flex items-center justify-center text-xs">NO IMG</div>}
                                    </div>
                                    <div className="flex-1">
                                        <Link to={`/product/${item.product_id}`} className="font-bold text-lg text-gray-900 dark:text-white hover:text-indigo-600 transition-colors">
                                            {item.product.name}
                                        </Link>
                                        <p className="text-sm text-gray-500 mt-1">
                                            {item.quantity} шт. × {item.price} BYN
                                        </p>
                                    </div>
                                    <Link to={`/product/${item.product_id}`} className="px-4 py-2 bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 rounded-xl font-bold text-sm hover:bg-indigo-100 dark:hover:bg-indigo-900/40 transition-colors">
                                        Оставить отзыв
                                    </Link>
                                </div>
                            ))}
                        </div>
                    </div>
                ))}
            </div>
        </div>
    );
}
