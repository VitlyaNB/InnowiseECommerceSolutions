import React, { useEffect, useState } from 'react';
import api from '../api';
import { useAuth } from '../contexts/AuthContext';
import { Trash2, Plus, Minus, ShoppingBag, CreditCard, Wallet, AlertCircle } from 'lucide-react';
import { Link, useNavigate } from 'react-router-dom';

export default function CartPage() {
    const { user, login } = useAuth(); // login нужен для обновления баланса юзера
    const [cartItems, setCartItems] = useState([]);
    const [loading, setLoading] = useState(true);
    const [processing, setProcessing] = useState(false);
    const [error, setError] = useState('');
    const navigate = useNavigate();

    const fetchCart = () => {
        api.get('/cart')
            .then(res => setCartItems(res.data.data || []))
            .catch(err => console.error(err))
            .finally(() => setLoading(false));
    };

    useEffect(() => {
        fetchCart();
    }, []);

    const updateQuantity = async (id, quantity) => {
        if (quantity < 1) return;
        try {
            await api.put(`/cart/${id}`, { quantity });
            fetchCart();
        } catch (err) {
            console.error(err);
        }
    };

    const removeItem = async (id) => {
        try {
            await api.delete(`/cart/${id}`);
            fetchCart();
        } catch (err) {
            console.error(err);
        }
    };

    // Логика покупки
    const handleCheckout = async () => {
        setProcessing(true);
        setError('');

        try {
            const res = await api.post('/orders');

            // Если сервер вернул новый баланс, обновляем юзера в контексте
            if (res.data.new_balance !== undefined && user) {
                const token = localStorage.getItem('auth_token');
                // Обновляем объект user, сохраняя остальные поля
                login({ ...user, balance: res.data.new_balance }, token);
            }

            alert('Заказ успешно оплачен и оформлен!');
            setCartItems([]); // Очищаем корзину визуально
            navigate('/'); // Или на страницу заказов
        } catch (err) {
            // Показываем ошибку от сервера (например, "Недостаточно средств")
            setError(err.response?.data?.message || 'Ошибка при оформлении заказа');
        } finally {
            setProcessing(false);
        }
    };

    const total = cartItems.reduce((sum, item) => sum + (parseFloat(item.product.price) * item.quantity), 0);
    const canAfford = user ? parseFloat(user.balance) >= total : false;

    if (loading) return <div className="p-10 text-center">Загрузка корзины...</div>;

    if (cartItems.length === 0) {
        return (
            <div className="flex flex-col items-center justify-center min-h-[60vh] text-center p-4">
                <div className="w-24 h-24 bg-indigo-50 rounded-full flex items-center justify-center mb-6">
                    <ShoppingBag className="w-12 h-12 text-indigo-300" />
                </div>
                <h2 className="text-3xl font-black text-gray-900 dark:text-white mb-2">Корзина пуста</h2>
                <p className="text-gray-500 mb-8 max-w-md">Похоже, вы еще ничего не добавили. Перейдите в каталог, чтобы найти что-то интересное.</p>
                <Link to="/catalog" className="px-8 py-4 bg-indigo-600 text-white rounded-2xl font-bold hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-200">
                    Перейти к покупкам
                </Link>
            </div>
        );
    }

    return (
        <div className="max-w-7xl mx-auto p-6 md:p-10">
            <h1 className="text-4xl font-black text-gray-900 dark:text-white mb-8">Моя корзина</h1>

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
                {/* Список товаров */}
                <div className="lg:col-span-2 space-y-4">
                    {cartItems.map(item => (
                        <div key={item.id} className="flex gap-4 p-4 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
                            {/* Картинка */}
                            <div className="w-24 h-24 bg-gray-100 rounded-xl overflow-hidden flex-shrink-0">
                                {item.product.images && item.product.images.length > 0 ? (
                                    <img src={item.product.images[0].url} alt={item.product.name} className="w-full h-full object-cover" />
                                ) : (
                                    <div className="w-full h-full flex items-center justify-center text-gray-400">Нет фото</div>
                                )}
                            </div>

                            {/* Инфо */}
                            <div className="flex-1 flex flex-col justify-between">
                                <div>
                                    <h3 className="font-bold text-lg text-gray-900 dark:text-white line-clamp-1">{item.product.name}</h3>
                                    <p className="text-gray-500 text-sm">Цена: {item.product.price} BYN</p>
                                </div>
                                <div className="flex items-center justify-between mt-4">
                                    <div className="flex items-center gap-3 bg-gray-50 dark:bg-gray-700 rounded-lg p-1">
                                        <button onClick={() => updateQuantity(item.id, item.quantity - 1)} className="p-1 hover:bg-white dark:hover:bg-gray-600 rounded-md transition-colors"><Minus className="w-4 h-4" /></button>
                                        <span className="font-bold w-4 text-center text-gray-900 dark:text-white">{item.quantity}</span>
                                        <button onClick={() => updateQuantity(item.id, item.quantity + 1)} className="p-1 hover:bg-white dark:hover:bg-gray-600 rounded-md transition-colors"><Plus className="w-4 h-4" /></button>
                                    </div>
                                    <button onClick={() => removeItem(item.id)} className="text-red-500 hover:text-red-600 p-2"><Trash2 className="w-5 h-5" /></button>
                                </div>
                            </div>
                        </div>
                    ))}
                </div>

                {/* Итого и Оплата */}
                <div className="lg:col-span-1">
                    <div className="bg-white dark:bg-gray-800 rounded-3xl p-8 shadow-sm border border-gray-100 dark:border-gray-700 sticky top-24">
                        <h3 className="text-xl font-black text-gray-900 dark:text-white mb-6">Сумма заказа</h3>

                        <div className="flex justify-between mb-4 text-gray-500">
                            <span>Товары ({cartItems.length})</span>
                            <span>{total.toFixed(2)} BYN</span>
                        </div>
                        <div className="flex justify-between mb-8 text-2xl font-black text-indigo-600">
                            <span>Итого</span>
                            <span>{total.toFixed(2)} BYN</span>
                        </div>

                        {/* Информация о балансе */}
                        {user ? (
                            <div className={`p-4 rounded-xl mb-6 flex items-center gap-3 border ${canAfford ? 'bg-green-50 border-green-100 text-green-800' : 'bg-red-50 border-red-100 text-red-800'}`}>
                                <Wallet className="w-5 h-5" />
                                <div>
                                    <p className="text-xs font-bold uppercase opacity-70">Ваш баланс</p>
                                    <p className="font-black text-lg">{parseFloat(user.balance).toFixed(2)} BYN</p>
                                </div>
                                {!canAfford && (
                                    <Link to="/top-up" className="ml-auto bg-white px-3 py-1 rounded-lg text-xs font-bold shadow-sm hover:bg-gray-50">
                                        Пополнить
                                    </Link>
                                )}
                            </div>
                        ) : (
                            <div className="mb-6 p-4 bg-yellow-50 text-yellow-800 rounded-xl text-sm font-bold">
                                <Link to="/login" className="underline">Войдите</Link>, чтобы использовать кошелек.
                            </div>
                        )}

                        {error && (
                            <div className="mb-6 p-4 bg-red-50 text-red-600 rounded-xl flex items-center gap-2 text-sm font-bold animate-pulse">
                                <AlertCircle className="w-5 h-5" /> {error}
                            </div>
                        )}

                        <button
                            onClick={handleCheckout}
                            disabled={processing || !canAfford || !user}
                            className={`w-full py-4 rounded-xl font-black text-lg flex items-center justify-center gap-2 transition-all shadow-lg
                                ${canAfford
                                ? 'bg-black dark:bg-white text-white dark:text-black hover:opacity-90 active:scale-95'
                                : 'bg-gray-200 text-gray-400 cursor-not-allowed'}`}
                        >
                            {processing ? 'Обработка...' : <>Оплатить заказ <CreditCard className="w-5 h-5" /></>}
                        </button>

                        {!canAfford && user && (
                            <p className="text-center text-xs text-red-500 font-bold mt-4">
                                Недостаточно средств для оплаты
                            </p>
                        )}
                    </div>
                </div>
            </div>
        </div>
    );
}
