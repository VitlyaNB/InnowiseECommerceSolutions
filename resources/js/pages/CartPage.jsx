import React, { useEffect, useState } from 'react';
import api from '../api';
import { useAuth } from '../contexts/AuthContext';
import { Trash2, Plus, Minus, ShoppingBag, CreditCard, Wallet, AlertCircle, CheckSquare, Square } from 'lucide-react';
import { Link, useNavigate } from 'react-router-dom';

export default function CartPage() {
    const { user, login } = useAuth();
    const [cartData, setCartData] = useState({ items: [], totals: { total: 0 } });
    const [selectedItems, setSelectedItems] = useState(new Set()); // Храним ID выбранных
    const [shippingAddress, setShippingAddress] = useState(''); // Стейт для адреса доставки
    const [loading, setLoading] = useState(true);
    const [processing, setProcessing] = useState(false);
    const [error, setError] = useState('');
    const navigate = useNavigate();

    const fetchCart = () => {
        api.get('/cart')
            .then(res => {
                const items = res.data.items || [];
                setCartData({
                    items: items,
                    totals: res.data.totals || { total: 0 }
                });
                // По умолчанию выбираем всё, если список пуст был (удобство)
                if (selectedItems.size === 0 && items.length > 0) {
                    setSelectedItems(new Set(items.map(i => i.id)));
                }
            })
            .catch(err => console.error(err))
            .finally(() => setLoading(false));
    };

    useEffect(() => {
        fetchCart();
    }, []);

    // Логика галочек
    const toggleItem = (id) => {
        const newSelected = new Set(selectedItems);
        if (newSelected.has(id)) {
            newSelected.delete(id);
        } else {
            newSelected.add(id);
        }
        setSelectedItems(newSelected);
    };

    const toggleAll = () => {
        if (selectedItems.size === cartData.items.length) {
            setSelectedItems(new Set()); // Снять все
        } else {
            setSelectedItems(new Set(cartData.items.map(i => i.id))); // Выбрать все
        }
    };

    // Подсчет суммы ТОЛЬКО выбранных
    const selectedTotal = cartData.items
        .filter(item => selectedItems.has(item.id))
        .reduce((sum, item) => sum + (parseFloat(item.product.price) * item.quantity), 0);

    const updateQuantity = async (id, quantity) => {
        if (quantity < 1) return;
        try {
            await api.put(`/cart/${id}`, { quantity });
            fetchCart();
        } catch (err) { console.error(err); }
    };

    const removeItem = async (id) => {
        try {
            await api.delete(`/cart/${id}`);
            fetchCart();
            // Удаляем из выделения тоже
            const newSelected = new Set(selectedItems);
            newSelected.delete(id);
            setSelectedItems(newSelected);
        } catch (err) { console.error(err); }
    };

    const handleCheckout = async () => {
        if (!user) { navigate('/login'); return; }
        if (selectedItems.size === 0) { setError('Выберите товары для покупки'); return; }
        if (!shippingAddress.trim()) { setError('Пожалуйста, введите адрес доставки'); return; }

        setProcessing(true);
        setError('');

        try {
            // Отправляем правильные ключи: selected_item_ids и shipping_address
            const res = await api.post('/orders', {
                selected_item_ids: Array.from(selectedItems),
                shipping_address: shippingAddress
            });

            if (res.data.new_balance !== undefined) {
                const token = localStorage.getItem('auth_token');
                login({ ...user, balance: res.data.new_balance }, token);
            }
            alert('Заказ успешно оформлен!');

            // Очищаем данные после успешного заказа
            setSelectedItems(new Set());
            setShippingAddress('');
            fetchCart();
        } catch (err) {
            setError(err.response?.data?.message || 'Ошибка при оформлении заказа');
        } finally {
            setProcessing(false);
        }
    };

    const canAfford = user ? parseFloat(user.balance) >= selectedTotal : false;

    if (loading) return <div className="p-20 text-center font-bold text-gray-500">Загрузка...</div>;

    if (cartData.items.length === 0) {
        return (
            <div className="flex flex-col items-center justify-center min-h-[60vh] text-center p-4">
                <ShoppingBag className="w-16 h-16 text-gray-300 mb-4" />
                <h2 className="text-2xl font-bold mb-2">Корзина пуста</h2>
                <Link to="/" className="text-indigo-600 font-bold hover:underline">В каталог</Link>
            </div>
        );
    }

    return (
        <div className="max-w-7xl mx-auto p-4 md:p-8">
            <h1 className="text-3xl font-black text-gray-900 dark:text-white mb-8">Корзина</h1>

            <div className="flex items-center gap-3 mb-6 p-4 bg-gray-50 dark:bg-gray-800 rounded-xl">
                <button onClick={toggleAll} className="flex items-center gap-3 font-bold text-gray-700 dark:text-gray-300">
                    {selectedItems.size === cartData.items.length ? (
                        <CheckSquare className="w-6 h-6 text-indigo-600" />
                    ) : (
                        <Square className="w-6 h-6 text-gray-400" />
                    )}
                    Выбрать все ({cartData.items.length})
                </button>
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
                {/* Список */}
                <div className="lg:col-span-2 space-y-4">
                    {cartData.items.map(item => (
                        <div key={item.id} className={`flex gap-4 p-4 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border transition-all ${selectedItems.has(item.id) ? 'border-indigo-500 ring-1 ring-indigo-500' : 'border-gray-100 dark:border-gray-700'}`}>

                            {/* Чекбокс */}
                            <button onClick={() => toggleItem(item.id)} className="self-center p-2">
                                {selectedItems.has(item.id) ? (
                                    <CheckSquare className="w-6 h-6 text-indigo-600" />
                                ) : (
                                    <Square className="w-6 h-6 text-gray-300 hover:text-gray-400" />
                                )}
                            </button>

                            <Link to={`/product/${item.product.id}`} className="w-24 h-24 bg-gray-100 rounded-xl overflow-hidden flex-shrink-0">
                                {item.product.images?.[0] ? (
                                    <img src={item.product.images[0].url} className="w-full h-full object-cover" />
                                ) : <div className="w-full h-full flex items-center justify-center text-xs">NO IMG</div>}
                            </Link>

                            <div className="flex-1 flex flex-col justify-between">
                                <div className="flex justify-between">
                                    <Link to={`/product/${item.product.id}`} className="font-bold text-lg line-clamp-1 hover:text-indigo-600 transition-colors dark:text-white">
                                        {item.product.name}
                                    </Link>
                                    <p className="font-bold whitespace-nowrap dark:text-white">{item.product.price} BYN</p>
                                </div>

                                <div className="flex justify-between items-center mt-2">
                                    <div className="flex items-center gap-3 bg-gray-50 dark:bg-gray-700 rounded-lg p-1">
                                        <button onClick={() => updateQuantity(item.id, item.quantity - 1)} className="p-1 hover:bg-white rounded"><Minus className="w-4 h-4" /></button>
                                        <span className="w-6 text-center font-bold text-sm dark:text-white">{item.quantity}</span>
                                        <button onClick={() => updateQuantity(item.id, item.quantity + 1)} className="p-1 hover:bg-white rounded"><Plus className="w-4 h-4" /></button>
                                    </div>
                                    <button onClick={() => removeItem(item.id)} className="text-red-400 p-2"><Trash2 className="w-5 h-5" /></button>
                                </div>
                            </div>
                        </div>
                    ))}
                </div>

                {/* Панель оплаты */}
                <div className="lg:col-span-1">
                    <div className="bg-white dark:bg-gray-800 rounded-3xl p-8 shadow-lg border border-gray-100 dark:border-gray-700 sticky top-24">
                        <h3 className="text-xl font-black mb-6 dark:text-white">К оплате</h3>

                        <div className="flex justify-between mb-2 text-gray-500">
                            <span>Выбрано товаров:</span>
                            <span>{selectedItems.size}</span>
                        </div>
                        <div className="flex justify-between mb-8 text-3xl font-black text-indigo-600">
                            <span>Итого:</span>
                            <span>{selectedTotal.toFixed(2)} BYN</span>
                        </div>

                        {user ? (
                            <div className={`p-4 rounded-xl mb-6 flex items-center gap-3 border ${canAfford ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-800'}`}>
                                <Wallet className="w-5 h-5" />
                                <div>
                                    <p className="text-xs font-bold uppercase opacity-70">Ваш баланс</p>
                                    <p className="font-bold">{parseFloat(user.balance).toFixed(2)} BYN</p>
                                </div>
                            </div>
                        ) : (
                            <div className="mb-6 p-4 bg-yellow-50 text-yellow-800 rounded-xl text-sm font-bold">
                                <Link to="/login" className="underline">Войдите</Link> для оплаты.
                            </div>
                        )}

                        {/* НОВОЕ: Поле для ввода адреса */}
                        {user && (
                            <div className="mb-6">
                                <label className="block text-sm font-bold mb-2 text-gray-700 dark:text-gray-300">
                                    Адрес доставки
                                </label>
                                <input
                                    type="text"
                                    value={shippingAddress}
                                    onChange={(e) => setShippingAddress(e.target.value)}
                                    placeholder="г. Минск, ул. Пушкина 10"
                                    className="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 focus:ring-2 focus:ring-indigo-500 outline-none dark:text-white transition-all"
                                />
                            </div>
                        )}

                        {error && <div className="mb-4 text-red-500 text-sm font-bold flex items-center gap-2"><AlertCircle className="w-4 h-4"/> {error}</div>}

                        <button
                            onClick={handleCheckout}
                            disabled={processing || selectedItems.size === 0 || (user && !canAfford) || !user}
                            className={`w-full py-4 rounded-xl font-black text-lg flex items-center justify-center gap-2 transition-all shadow-xl active:scale-95
                                ${user && canAfford && selectedItems.size > 0
                                ? 'bg-black dark:bg-white text-white dark:text-black hover:opacity-90'
                                : 'bg-gray-200 text-gray-400 cursor-not-allowed shadow-none'}`}
                        >
                            {processing ? 'Обработка...' : <>Купить ({selectedItems.size}) <CreditCard className="w-5 h-5" /></>}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    );
}
