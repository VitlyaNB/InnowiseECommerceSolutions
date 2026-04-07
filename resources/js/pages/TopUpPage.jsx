import React, { useState } from 'react';
import api from '../api';
import { useAuth } from '../contexts/AuthContext'; // Чтобы обновить баланс в контексте
import { useNavigate } from 'react-router-dom';
import { CreditCard, Wallet, ArrowRight } from 'lucide-react';

export default function TopUpPage() {
    const { login, user } = useAuth(); // login используем для обновления данных юзера без перезагрузки
    const navigate = useNavigate();
    const [amount, setAmount] = useState('');
    const [cardNumber, setCardNumber] = useState('');
    const [expiry, setExpiry] = useState('');
    const [cvv, setCvv] = useState('');
    const [cardholder, setCardholder] = useState('');
    const [errors, setErrors] = useState({});
    const [loading, setLoading] = useState(false);

    const formatCardNumber = (value) => {
        const digits = value.replace(/\D/g, '').slice(0, 16);
        return digits.replace(/(\d{4})(?=\d)/g, '$1 ').trim();
    };

    const formatExpiry = (value) => {
        const digits = value.replace(/\D/g, '').slice(0, 4);
        if (digits.length <= 2) return digits;
        return `${digits.slice(0, 2)}/${digits.slice(2, 4)}`;
    };

    const validatePaymentFields = () => {
        const nextErrors = {};
        const cardDigits = cardNumber.replace(/\D/g, '');
        const expiryDigits = expiry.replace(/\D/g, '');
        const cvvDigits = cvv.replace(/\D/g, '');
        const topUpAmount = Number(amount);

        if (cardDigits.length !== 16) {
            nextErrors.cardNumber = 'Card number must contain exactly 16 digits.';
        }

        if (expiryDigits.length !== 4) {
            nextErrors.expiry = 'Expiry must contain exactly 4 digits in MM/YY format.';
        } else {
            const month = Number(expiryDigits.slice(0, 2));
            if (month < 1 || month > 12) {
                nextErrors.expiry = 'Expiry month must be between 01 and 12.';
            }
        }

        if (cvvDigits.length !== 3) {
            nextErrors.cvv = 'CVV must contain exactly 3 digits.';
        }

        if (cardholder.trim().length < 2) {
            nextErrors.cardholder = 'Cardholder name is required.';
        }

        if (!Number.isFinite(topUpAmount) || topUpAmount < 1) {
            nextErrors.amount = 'Amount must be at least 1 BYN.';
        }

        setErrors(nextErrors);

        return Object.keys(nextErrors).length === 0;
    };

    const handleTopUp = async (e) => {
        e.preventDefault();

        if (!validatePaymentFields()) {
            return;
        }

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
                    <p className="opacity-80 mt-2">Текущий баланс: {(parseFloat(user?.balance) || 0).toFixed(2)} BYN</p>
                </div>

                <form onSubmit={handleTopUp} className="p-8 space-y-6">
                    {/* "Фейковые" поля карты */}
                    <div className="space-y-4">
                        <div>
                            <label className="block text-xs font-bold text-gray-500 uppercase mb-1">Номер карты</label>
                            <div className="relative">
                                <CreditCard className="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 w-5 h-5" />
                                <input
                                    type="text"
                                    placeholder="0000 0000 0000 0000"
                                    value={cardNumber}
                                    onChange={e => setCardNumber(formatCardNumber(e.target.value))}
                                    className="w-full pl-10 p-3 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl outline-none focus:ring-2 focus:ring-indigo-500 font-mono text-gray-700 dark:text-white"
                                />
                            </div>
                            {errors.cardNumber && <p className="text-xs text-red-500 mt-1">{errors.cardNumber}</p>}
                        </div>
                        <div>
                            <label className="block text-xs font-bold text-gray-500 uppercase mb-1">Имя держателя</label>
                            <input
                                type="text"
                                placeholder="IVAN IVANOV"
                                value={cardholder}
                                onChange={e => setCardholder(e.target.value)}
                                className="w-full p-3 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl outline-none focus:ring-2 focus:ring-indigo-500 text-gray-700 dark:text-white"
                            />
                            {errors.cardholder && <p className="text-xs text-red-500 mt-1">{errors.cardholder}</p>}
                        </div>
                        <div className="flex gap-4">
                            <div className="w-1/2">
                                <label className="block text-xs font-bold text-gray-500 uppercase mb-1">Срок</label>
                                <input
                                    type="text"
                                    placeholder="MM/YY"
                                    value={expiry}
                                    onChange={e => setExpiry(formatExpiry(e.target.value))}
                                    className="w-full p-3 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl outline-none focus:ring-2 focus:ring-indigo-500 text-center font-mono text-gray-700 dark:text-white"
                                />
                                {errors.expiry && <p className="text-xs text-red-500 mt-1">{errors.expiry}</p>}
                            </div>
                            <div className="w-1/2">
                                <label className="block text-xs font-bold text-gray-500 uppercase mb-1">CVV</label>
                                <input
                                    type="password"
                                    placeholder="123"
                                    maxLength="3"
                                    value={cvv}
                                    onChange={e => setCvv(e.target.value.replace(/\D/g, '').slice(0, 3))}
                                    className="w-full p-3 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl outline-none focus:ring-2 focus:ring-indigo-500 text-center font-mono text-gray-700 dark:text-white"
                                />
                                {errors.cvv && <p className="text-xs text-red-500 mt-1">{errors.cvv}</p>}
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
                        {errors.amount && <p className="text-xs text-red-500 mt-2">{errors.amount}</p>}
                    </div>

                    <button disabled={loading} type="submit" className="w-full py-4 bg-black dark:bg-white text-white dark:text-black font-black text-lg rounded-2xl hover:opacity-90 transition-opacity flex items-center justify-center gap-2">
                        {loading ? 'Обработка...' : <>Пополнить <ArrowRight className="w-5 h-5" /></>}
                    </button>
                </form>
            </div>
        </div>
    );
}
