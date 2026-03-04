import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import api from '../api';
import { useAuth } from '../contexts/AuthContext';

const LoginPage = () => {
    const [isLogin, setIsLogin] = useState(true);
    const { login } = useAuth();
    const navigate = useNavigate();

    const [formData, setFormData] = useState({
        name: '', email: '', password: '', password_confirmation: '',
    });

    const [loading, setLoading] = useState(false);
    const [errors, setErrors] = useState({});
    const [successMessage, setSuccessMessage] = useState('');

    const handleChange = (e) => {
        setFormData({ ...formData, [e.target.name]: e.target.value });
        if (errors[e.target.name]) {
            setErrors({ ...errors, [e.target.name]: null });
        }
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setLoading(true);
        setErrors({});
        setSuccessMessage('');

        const endpoint = isLogin ? '/login' : '/register';

        try {
            const response = await api.post(endpoint, formData);

            // ИСПРАВЛЕНИЕ: Бэкенд возвращает 'token', а не 'access_token'
            const userData = response.data.user;
            const token = response.data.token;

            if (!token) {
                throw new Error("Токен не получен от сервера");
            }

            login(userData, token);
            setSuccessMessage('Успешно! Входим...');

            setTimeout(() => {
                if (userData.role === 'admin') {
                    navigate('/admin');
                } else {
                    navigate('/');
                }
            }, 800);

        } catch (error) {
            console.error("Login Error:", error);
            if (error.response && error.response.status === 422) {
                setErrors(error.response.data.errors || { general: error.response.data.message });
            } else {
                setErrors({ general: error.message || 'Ошибка входа. Проверьте данные.' });
            }
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="min-h-[calc(100vh-80px)] flex items-center justify-center bg-gray-50 dark:bg-gray-900">
            <div className="bg-white dark:bg-gray-800 p-10 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 w-[400px]">
                <h2 className="text-3xl font-black mb-8 text-center text-gray-900 dark:text-white uppercase tracking-tight">
                    {isLogin ? 'Вход' : 'Регистрация'}
                </h2>

                {successMessage && <div className="mb-6 text-green-600 bg-green-50 p-4 rounded-xl font-bold text-center border border-green-100">{successMessage}</div>}
                {Object.keys(errors).length > 0 && (
                    <div className="mb-6 text-red-600 bg-red-50 p-4 rounded-xl font-bold text-center border border-red-100">
                        {errors.general || "Ошибка валидации"}
                    </div>
                )}

                <form onSubmit={handleSubmit} className="space-y-5">
                    {!isLogin && (
                        <div>
                            <label className="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2 tracking-wide uppercase">Имя</label>
                            <input type="text" name="name" value={formData.name} onChange={handleChange} className="w-full px-4 py-3 border border-gray-200 dark:border-gray-600 rounded-xl focus:bg-white bg-gray-50 dark:bg-gray-700 dark:text-white outline-none focus:ring-2 focus:ring-indigo-500 transition-all" required={!isLogin} />
                        </div>
                    )}

                    <div>
                        <label className="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2 tracking-wide uppercase">Email</label>
                        <input type="email" name="email" value={formData.email} onChange={handleChange} className="w-full px-4 py-3 border border-gray-200 dark:border-gray-600 rounded-xl focus:bg-white bg-gray-50 dark:bg-gray-700 dark:text-white outline-none focus:ring-2 focus:ring-indigo-500 transition-all" required />
                    </div>

                    <div>
                        <label className="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2 tracking-wide uppercase">Пароль</label>
                        <input type="password" name="password" value={formData.password} onChange={handleChange} className="w-full px-4 py-3 border border-gray-200 dark:border-gray-600 rounded-xl focus:bg-white bg-gray-50 dark:bg-gray-700 dark:text-white outline-none focus:ring-2 focus:ring-indigo-500 transition-all" required />
                    </div>

                    {!isLogin && (
                        <div>
                            <label className="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2 tracking-wide uppercase">Подтверждение</label>
                            <input type="password" name="password_confirmation" value={formData.password_confirmation} onChange={handleChange} className="w-full px-4 py-3 border border-gray-200 dark:border-gray-600 rounded-xl focus:bg-white bg-gray-50 dark:bg-gray-700 dark:text-white outline-none focus:ring-2 focus:ring-indigo-500 transition-all" required={!isLogin} />
                        </div>
                    )}

                    <button type="submit" disabled={loading} className="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-black py-4 px-4 rounded-xl transition-all disabled:opacity-50 mt-4 shadow-lg shadow-indigo-500/20 active:scale-95 uppercase tracking-widest">
                        {loading ? 'Секунду...' : (isLogin ? 'Войти' : 'Создать')}
                    </button>
                </form>

                <div className="mt-8 text-center border-t border-gray-100 dark:border-gray-700 pt-6">
                    <button onClick={() => { setIsLogin(!isLogin); setErrors({}); }} className="text-gray-500 hover:text-indigo-600 text-xs font-black transition-colors uppercase tracking-widest">
                        {isLogin ? 'Нет аккаунта? Регистрация' : 'Есть аккаунт? Войти'}
                    </button>
                </div>
            </div>
        </div>
    );
};

export default LoginPage;
