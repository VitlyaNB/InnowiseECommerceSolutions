import React, { useState } from 'react';
import api from '../api';

const LoginPage = () => {

    const [isLogin, setIsLogin] = useState(true);

    const [formData, setFormData] = useState({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
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
        setTimeout(() => {
            if (response.data.user.role === 'admin') {
                window.location.href = '/admin';
            } else {
                window.location.href = '/';
            }
        }, 1000);
        e.preventDefault();
        setLoading(true);
        setErrors({});
        setSuccessMessage('');

        const endpoint = isLogin ? '/login' : '/register';

        try {
            const response = await api.post(endpoint, formData);

            localStorage.setItem('auth_token', response.data.access_token);
            localStorage.setItem('user', JSON.stringify(response.data.user));

            setSuccessMessage('Успешно! Перенаправление...');

            setTimeout(() => {
                window.location.href = '/';
            }, 1000);

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
        <div className="min-h-screen flex items-center justify-center bg-gray-100">
            <div className="bg-white p-8 rounded-lg shadow-md w-96">
                <h2 className="text-2xl font-bold mb-6 text-center">
                    {isLogin ? 'Вход в систему' : 'Регистрация'}
                </h2>

                {successMessage && (
                    <div className="mb-4 text-green-600 bg-green-100 p-3 rounded">
                        {successMessage}
                    </div>
                )}
                {errors.general && (
                    <div className="mb-4 text-red-600 bg-red-100 p-3 rounded">
                        {errors.general}
                    </div>
                )}

                <form onSubmit={handleSubmit} className="space-y-4">
                    {!isLogin && (
                        <div>
                            <label className="block text-gray-700 text-sm font-bold mb-2">Имя</label>
                            <input
                                type="text"
                                name="name"
                                value={formData.name}
                                onChange={handleChange}
                                className="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:border-blue-300"
                                required={!isLogin}
                            />
                            {errors.name && <p className="text-red-500 text-xs italic mt-1">{errors.name[0]}</p>}
                        </div>
                    )}

                    <div>
                        <label className="block text-gray-700 text-sm font-bold mb-2">Email</label>
                        <input
                            type="email"
                            name="email"
                            value={formData.email}
                            onChange={handleChange}
                            className="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:border-blue-300"
                            required
                        />
                        {errors.email && <p className="text-red-500 text-xs italic mt-1">{errors.email[0]}</p>}
                    </div>

                    <div>
                        <label className="block text-gray-700 text-sm font-bold mb-2">Пароль</label>
                        <input
                            type="password"
                            name="password"
                            value={formData.password}
                            onChange={handleChange}
                            className="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:border-blue-300"
                            required
                        />
                        {errors.password && <p className="text-red-500 text-xs italic mt-1">{errors.password[0]}</p>}
                    </div>

                    {!isLogin && (
                        <div>
                            <label className="block text-gray-700 text-sm font-bold mb-2">Подтвердите пароль</label>
                            <input
                                type="password"
                                name="password_confirmation"
                                value={formData.password_confirmation}
                                onChange={handleChange}
                                className="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:border-blue-300"
                                required={!isLogin}
                            />
                        </div>
                    )}

                    <button
                        type="submit"
                        disabled={loading}
                        className="w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline disabled:opacity-50"
                    >
                        {loading ? 'Загрузка...' : (isLogin ? 'Войти' : 'Зарегистрироваться')}
                    </button>
                </form>

                <div className="mt-4 text-center">
                    <button
                        onClick={() => {
                            setIsLogin(!isLogin);
                            setErrors({});
                        }}
                        className="text-blue-500 hover:text-blue-700 text-sm font-bold"
                    >
                        {isLogin ? 'Нет аккаунта? Зарегистрируйтесь' : 'Уже есть аккаунт? Войдите'}

                    </button>
                    admin@example.com
                    password
                </div>
            </div>
        </div>
    );
};
export default LoginPage;
