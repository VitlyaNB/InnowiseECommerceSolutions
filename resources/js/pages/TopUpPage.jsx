import React, { useState } from 'react';
import api from '../api';
import { useAuth } from '../contexts/AuthContext'; // Чтобы обновить баланс в контексте
import { useNavigate } from 'react-router-dom';
import { CreditCard, Wallet, ArrowRight } from 'lucide-react';

export default function TopUpPage() {
    const { login, user } = useAuth(); // login используем для обновления данных юзера без перезагрузки
    const navigate = useNavigate();
    const [amount, setAmount] = useState('');
    const [loading, setLoading] = useState(false);

    const handleTopUp = async (e) => {
        e.preventDefault();
        setLoading(true);

        try {
            // Отправляем запрос на пополнение
            const response = await api.post('/wallet/top-up', { amount });

            // Магия: обновляем данные пользователя в контексте приложения
            // Мы берем старый токен, но новые данные user из ответа сервера
            const token = localStorage.getItem('auth_token');
            login(response.data.user, token);

            alert('Оплата прошла успешно! Ваш баланс пополнен.');
            navigate('/'); // Возвращаем на главную
        } catch (error) {
            console.error(error);
            alert('Ошибка при пополнении.');
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="min-h-screen bg-gray-50 dark:bg-gray-900 flex items-center justify-center p-4">
            <div className="max-w-md w-full bg-white dark:bg-gray-800 rounded-3xl shadow-xl overflow-hidden border border-gray-100 dark:border-gray-700">
                <div className="p-8 bg-indigo-600 text-white text-center">
                    <Wallet className="w-12 h-12 mx-auto mb-4 opacity-80" />
                    <h1 className="text-2xl font-black uppercase tracking-wide">Пополнение кошелька</h1>
                    <p className="opacity-80 mt-2">Текущий баланс: {user?.balance || 0} BYN</p>
                </div>

                <form onSubmit={handleTopUp} className="p-8 space-y-6">
                    {/* "Фейковые" поля карты */}
                    <div className="space-y-4">
                        <div>
                            <label className="block text-xs font-bold text-gray-500 uppercase mb-1">Номер карты</label>
                            <div className="relative">
                                <CreditCard className="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 w-5 h-5" />
                                <input type="text" placeholder="0000 0000 0000 0000" className="w-full pl-10 p-3 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl outline-none focus:ring-2 focus:ring-indigo-500 font-mono text-gray-700 dark:text-white" />
                            </div>
                        </div>
                        <div className="flex gap-4">
                            <div className="w-1/2">
                                <label className="block text-xs font-bold text-gray-500 uppercase mb-1">Срок</label>
                                <input type="text" placeholder="MM/YY" className="w-full p-3 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl outline-none focus:ring-2 focus:ring-indigo-500 text-center font-mono text-gray-700 dark:text-white" />
                            </div>
                            <div className="w-1/2">
                                <label className="block text-xs font-bold text-gray-500 uppercase mb-1">CVV</label>
                                <input type="password" placeholder="123" maxLength="3" className="w-full p-3 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl outline-none focus:ring-2 focus:ring-indigo-500 text-center font-mono text-gray-700 dark:text-white" />
                            </div>
                        </div>
                    </div>

                    <div className="pt-4 border-t border-gray-100 dark:border-gray-700">
                        <label className="block text-sm font-bold text-gray-900 dark:text-white mb-2">Сумма пополнения (BYN)</label>
                        <input
                            type="number"
                            step="0.01"
                            required
                            min="1"
                            value={amount}
                            onChange={e => setAmount(e.target.value)}
                            className="w-full p-4 text-2xl font-bold text-center bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-2xl outline-none focus:ring-2 focus:ring-indigo-500 text-indigo-600 dark:text-indigo-400"
                            placeholder="0.00"
                        />
                    </div>

                    <button disabled={loading} type="submit" className="w-full py-4 bg-black dark:bg-white text-white dark:text-black font-black text-lg rounded-2xl hover:opacity-90 transition-opacity flex items-center justify-center gap-2">
                        {loading ? 'Обработка...' : <>Пополнить <ArrowRight className="w-5 h-5" /></>}
                    </button>
                </form>
            </div>
        </div>
    );
}
