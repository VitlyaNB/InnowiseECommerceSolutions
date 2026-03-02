import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import api from '../api';
import { useAuth } from '../contexts/AuthContext';

const LoginPage = () => {
    const [isLogin, setIsLogin] = useState(true);
    const { login } = useAuth(); // Достаем функцию login из контекста
    const navigate = useNavigate(); // Хук для плавного редиректа

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

            // Используем контекст для входа (он сам положит в LocalStorage и настроит axios)
            login(response.data.user, response.data.access_token);
            setSuccessMessage('Успешно! Перенаправление...');

            // Плавный переход без перезагрузки всей страницы
            setTimeout(() => {
                if (response.data.user.role === 'admin') {
                    navigate('/admin');
                } else {
                    navigate('/');
                }
            }, 800);

        } catch (error) {
            if (error.response && error.response.status === 422) {
                setErrors(error.response.data.errors || error.response.data.message);
            } else {
                setErrors({ general: 'Что-то пошло не так. Попробуйте позже.' });
            }
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="min-h-[calc(100vh-80px)] flex items-center justify-center bg-gray-50">
            <div className="bg-white p-10 rounded-3xl shadow-sm border border-gray-100 w-[400px]">
                <h2 className="text-3xl font-black mb-8 text-center text-gray-900">
                    {isLogin ? 'Вход' : 'Регистрация'}
                </h2>

                {successMessage && <div className="mb-6 text-indigo-600 bg-indigo-50 p-4 rounded-xl font-bold text-center border border-indigo-100">{successMessage}</div>}
                {errors.general && <div className="mb-6 text-red-600 bg-red-50 p-4 rounded-xl font-bold text-center border border-red-100">{errors.general}</div>}

                <form onSubmit={handleSubmit} className="space-y-5">
                    {!isLogin && (
                        <div>
                            <label className="block text-gray-700 text-sm font-bold mb-2">Имя</label>
                            <input type="text" name="name" value={formData.name} onChange={handleChange} className="w-full px-4 py-3 border border-gray-200 rounded-xl focus:bg-white bg-gray-50 outline-none focus:ring-2 focus:ring-indigo-500 transition-all" required={!isLogin} />
                            {errors.name && <p className="text-red-500 text-xs mt-2 font-bold">{errors.name[0]}</p>}
                        </div>
                    )}

                    <div>
                        <label className="block text-gray-700 text-sm font-bold mb-2">Email</label>
                        <input type="email" name="email" value={formData.email} onChange={handleChange} className="w-full px-4 py-3 border border-gray-200 rounded-xl focus:bg-white bg-gray-50 outline-none focus:ring-2 focus:ring-indigo-500 transition-all" required />
                        {errors.email && <p className="text-red-500 text-xs mt-2 font-bold">{errors.email[0]}</p>}
                    </div>

                    <div>
                        <label className="block text-gray-700 text-sm font-bold mb-2">Пароль</label>
                        <input type="password" name="password" value={formData.password} onChange={handleChange} className="w-full px-4 py-3 border border-gray-200 rounded-xl focus:bg-white bg-gray-50 outline-none focus:ring-2 focus:ring-indigo-500 transition-all" required />
                        {errors.password && <p className="text-red-500 text-xs mt-2 font-bold">{errors.password[0]}</p>}
                    </div>

                    {!isLogin && (
                        <div>
                            <label className="block text-gray-700 text-sm font-bold mb-2">Подтвердите пароль</label>
                            <input type="password" name="password_confirmation" value={formData.password_confirmation} onChange={handleChange} className="w-full px-4 py-3 border border-gray-200 rounded-xl focus:bg-white bg-gray-50 outline-none focus:ring-2 focus:ring-indigo-500 transition-all" required={!isLogin} />
                        </div>
                    )}

                    <button type="submit" disabled={loading} className="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-black py-4 px-4 rounded-xl transition-colors disabled:opacity-50 mt-4 shadow-md">
                        {loading ? 'Секунду...' : (isLogin ? 'Войти в аккаунт' : 'Создать аккаунт')}
                    </button>
                </form>

                <div className="mt-8 text-center border-t border-gray-100 pt-6">
                    <button onClick={() => { setIsLogin(!isLogin); setErrors({}); }} className="text-gray-500 hover:text-indigo-600 text-sm font-bold transition-colors">
                        {isLogin ? 'Нет аккаунта? Создать' : 'Уже есть аккаунт? Войти'}
                    </button>
                </div>
            </div>
        </div>
    );
};

export default LoginPage;
