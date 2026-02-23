import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import axios from 'axios';

export default function LoginPage() {
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const navigate = useNavigate();

    const handleLogin = async (e) => {
        e.preventDefault();
        try {
            const res = await axios.post('/api/login', { email, password });
            localStorage.setItem('token', res.data.token);
            localStorage.setItem('user_role', res.data.user.role);
            navigate(res.data.user.role === 'admin' ? '/admin' : '/');
        } catch (err) { alert("Ошибка входа"); }
    };

    return (
        <div className="min-h-screen flex items-center justify-center bg-gray-100">
            <div className="bg-white p-10 rounded-3xl shadow-xl w-96 text-center">
                <h1 className="text-2xl font-black mb-6">Вход в INNOSHOP</h1>
                <form onSubmit={handleLogin} className="space-y-4">
                    <input type="email" placeholder="Email" className="w-full p-3 border rounded-xl" onChange={e => setEmail(e.target.value)} />
                    <input type="password" placeholder="Пароль" className="w-full p-3 border rounded-xl" onChange={e => setPassword(e.target.value)} />
                    <button type="submit" className="w-full bg-indigo-600 text-white py-3 rounded-xl font-bold">Войти</button>
                </form>
                <div className="mt-4 border-t pt-4">
                    <button onClick={() => {
                        localStorage.removeItem('token');
                        localStorage.setItem('user_role', 'guest');
                        navigate('/');
                    }} className="text-gray-500 hover:text-indigo-600 transition">Войти как гость</button>
                </div>
            </div>
        </div>
    );
}
