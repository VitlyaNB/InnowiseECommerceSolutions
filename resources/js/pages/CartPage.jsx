import React, { useState, useEffect } from 'react';
import api from '../api';
import { Trash2, Plus, Minus, CreditCard, ShoppingBag } from 'lucide-react';
import { Link } from 'react-router-dom';
import ImageWithFallback from '../components/ImageWithFallback';

export default function CartPage() {
    const [cart, setCart] = useState({ items: [], totals: { subtotal: 0, tax: 0, total: 0 } });
    const [loading, setLoading] = useState(true);

    const fetchCart = () => {
        api.get('/cart')
            .then(res => setCart(res.data))
            .catch(() => setCart({ items: [], totals: { subtotal: 0, tax: 0, total: 0 } }))
            .finally(() => setLoading(false));
    };

    useEffect(() => { fetchCart(); }, []);

    const updateQuantity = async (id, quantity) => {
        if (quantity < 1) return;
        await api.put(`/cart/${id}`, { quantity });
        fetchCart();
    };

    const removeItem = async (id) => {
        await api.delete(`/cart/${id}`);
        fetchCart();
    };

    const clearCart = async () => {
        if (!window.confirm('Очистить корзину?')) return;
        await api.delete('/cart');
        fetchCart();
    };

    if (loading) {
        return (
            <div className="min-h-[50vh] flex items-center justify-center">
                <div className="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-indigo-600"></div>
            </div>
        );
    }

    return (
        <div className="max-w-7xl mx-auto px-4 py-12 grid grid-cols-1 lg:grid-cols-3 gap-12">
            <div className="lg:col-span-2 space-y-6">
                <div className="flex items-center justify-between">
                    <h1 className="text-3xl font-black text-gray-900 dark:text-white">Корзина</h1>
                    {cart.items?.length > 0 && (
                        <button
                            onClick={clearCart}
                            className="text-sm text-red-500 hover:text-red-600 font-bold"
                        >
                            Очистить корзину
                        </button>
                    )}
                </div>
                {!cart.items?.length ? (
                    <div className="flex flex-col items-center justify-center py-20 bg-gray-50 dark:bg-gray-800/50 rounded-3xl border border-dashed border-gray-200 dark:border-gray-700">
                        <ShoppingBag className="w-16 h-16 text-gray-400 mb-4" />
                        <p className="text-gray-600 dark:text-gray-400 mb-4">Корзина пуста</p>
                        <Link
                            to="/"
                            className="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl transition-colors"
                        >
                            В каталог
                        </Link>
                    </div>
                ) : (
                    cart.items.map(item => {
                        const product = item.product || {};
                        const imgUrl = product.images?.[0]?.url;
                        return (
                            <div
                                key={item.id}
                                className="flex items-center gap-6 p-6 bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm"
                            >
                                <ImageWithFallback
                                    src={imgUrl}
                                    alt={product.name}
                                    className="w-24 h-24 object-cover rounded-xl flex-shrink-0"
                                />
                                <div className="flex-1 min-w-0">
                                    <Link
                                        to={`/product/${product.id}`}
                                        className="font-bold text-lg dark:text-white hover:text-indigo-600 block truncate"
                                    >
                                        {product.name}
                                    </Link>
                                    <p className="text-indigo-600 font-black mt-1">{product.price} BYN</p>
                                </div>
                                <div className="flex items-center gap-4 bg-gray-50 dark:bg-gray-700 p-2 rounded-xl">
                                    <button
                                        onClick={() => updateQuantity(item.id, item.quantity - 1)}
                                        className="p-1 hover:text-indigo-600 dark:hover:text-indigo-400"
                                    >
                                        <Minus className="w-4 h-4" />
                                    </button>
                                    <span className="font-bold w-6 text-center dark:text-white">{item.quantity}</span>
                                    <button
                                        onClick={() => updateQuantity(item.id, item.quantity + 1)}
                                        className="p-1 hover:text-indigo-600 dark:hover:text-indigo-400"
                                    >
                                        <Plus className="w-4 h-4" />
                                    </button>
                                </div>
                                <button
                                    onClick={() => removeItem(item.id)}
                                    className="p-3 text-red-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-xl transition"
                                >
                                    <Trash2 className="w-5 h-5" />
                                </button>
                            </div>
                        );
                    })
                )}
            </div>

            <div className="lg:col-span-1">
                <div className="sticky top-24 bg-gray-50 dark:bg-gray-800 p-8 rounded-3xl h-fit border border-gray-100 dark:border-gray-700">
                    <h3 className="text-xl font-black mb-6 dark:text-white">Детали заказа</h3>
                    <div className="space-y-3 text-sm text-gray-600 dark:text-gray-300 mb-6">
                        <div className="flex justify-between">
                            <span>Товары</span>
                            <span>{cart.totals?.subtotal ?? 0} BYN</span>
                        </div>
                        <div className="flex justify-between">
                            <span>НДС (20%)</span>
                            <span>{cart.totals?.tax ?? 0} BYN</span>
                        </div>
                    </div>
                    <div className="flex justify-between text-2xl font-black text-gray-900 dark:text-white mb-8 border-t pt-6 border-gray-200 dark:border-gray-600">
                        <span>Итого</span>
                        <span>{cart.totals?.total ?? 0} BYN</span>
                    </div>
                    <button
                        disabled={!cart.items?.length}
                        className="w-full bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-black py-4 rounded-2xl flex justify-center items-center gap-2 transition-colors"
                    >
                        <CreditCard className="w-5 h-5" /> Оформить заказ
                    </button>
                </div>
            </div>
        </div>
    );
}
