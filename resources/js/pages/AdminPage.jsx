import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { Link } from 'react-router-dom';
import { ArrowLeft, PlusCircle, Users, LayoutDashboard } from 'lucide-react';

export default function AdminPage() {
    const [activeTab, setActiveTab] = useState('addProduct');
    const [users, setUsers] = useState([]);

    // Стейт для формы товара
    const [formData, setFormData] = useState({
        name: '', description: '', price: '', quantity: '1', category_id: '1'
    });
    const [msg, setMsg] = useState('');

    // Загрузка пользователей, если открыта вкладка "Пользователи"
    useEffect(() => {
        if (activeTab === 'users') {
            const token = localStorage.getItem('auth_token');
            axios.get('/api/users', {
                headers: { Authorization: `Bearer ${token}` }
            })
                .then(res => setUsers(res.data))
                .catch(err => console.error("Ошибка загрузки пользователей:", err));
        }
    }, [activeTab]);

    const handleSubmit = async (e) => {
        e.preventDefault();
        try {
            const token = localStorage.getItem('auth_token');
            await axios.post('/api/products', formData, {
                headers: { Authorization: `Bearer ${token}` }
            });
            setMsg('Товар успешно добавлен!');
            setFormData({ name: '', description: '', price: '', quantity: '1', category_id: '1' });
            setTimeout(() => setMsg(''), 4000); // Убираем сообщение через 4 сек
        } catch (error) {
            setMsg('Ошибка при добавлении.');
            console.error(error);
        }
    };

    return (
        <div className="min-h-screen bg-gray-50 flex">

            {/* Боковое меню (Sidebar) */}
            <div className="w-72 bg-white shadow-sm border-r border-gray-200 flex flex-col">
                <div className="p-6 border-b border-gray-100 flex items-center gap-3">
                    <LayoutDashboard className="w-8 h-8 text-indigo-600" />
                    <h1 className="text-xl font-black text-gray-900 tracking-tight">ADMIN PANEL</h1>
                </div>
                <div className="p-4 flex flex-col gap-2 flex-1">
                    <button
                        onClick={() => setActiveTab('addProduct')}
                        className={`flex items-center gap-3 p-4 rounded-xl font-bold transition-all ${activeTab === 'addProduct' ? 'bg-indigo-50 text-indigo-600' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900'}`}
                    >
                        <PlusCircle className="w-5 h-5" /> Добавить товар
                    </button>
                    <button
                        onClick={() => setActiveTab('users')}
                        className={`flex items-center gap-3 p-4 rounded-xl font-bold transition-all ${activeTab === 'users' ? 'bg-indigo-50 text-indigo-600' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900'}`}
                    >
                        <Users className="w-5 h-5" /> Пользователи БД
                    </button>
                </div>
                <div className="p-4 border-t border-gray-100">
                    <Link to="/" className="flex items-center gap-3 p-4 text-gray-500 hover:text-gray-900 transition-colors font-bold rounded-xl hover:bg-gray-50">
                        <ArrowLeft className="w-5 h-5" /> В магазин
                    </Link>
                </div>
            </div>

            {/* Главная контентная часть */}
            <div className="flex-1 p-10 overflow-y-auto">

                {/* Вкладка: Добавление товара */}
                {activeTab === 'addProduct' && (
                    <div className="max-w-3xl bg-white rounded-3xl shadow-sm border border-gray-100 p-10">
                        <h2 className="text-3xl font-black text-gray-900 mb-8">Новый товар</h2>
                        {msg && <div className="mb-6 p-4 rounded-xl bg-green-50 text-green-700 font-bold border border-green-100">{msg}</div>}

                        <form onSubmit={handleSubmit} className="space-y-6">
                            <div>
                                <label className="block text-sm font-bold text-gray-700 mb-2">Название товара</label>
                                <input type="text" required value={formData.name} onChange={e => setFormData({...formData, name: e.target.value})} className="w-full p-4 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:ring-2 focus:ring-indigo-500 outline-none transition-all" />
                            </div>

                            <div className="grid grid-cols-2 gap-6">
                                <div>
                                    <label className="block text-sm font-bold text-gray-700 mb-2">Цена (₽)</label>
                                    <input type="number" step="0.01" required value={formData.price} onChange={e => setFormData({...formData, price: e.target.value})} className="w-full p-4 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:ring-2 focus:ring-indigo-500 outline-none transition-all" />
                                </div>
                                <div>
                                    <label className="block text-sm font-bold text-gray-700 mb-2">Кол-во на складе</label>
                                    <input type="number" required value={formData.quantity} onChange={e => setFormData({...formData, quantity: e.target.value})} className="w-full p-4 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:ring-2 focus:ring-indigo-500 outline-none transition-all" />
                                </div>
                            </div>

                            <div>
                                <label className="block text-sm font-bold text-gray-700 mb-2">ID Категории</label>
                                <input type="number" required value={formData.category_id} onChange={e => setFormData({...formData, category_id: e.target.value})} className="w-full p-4 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:ring-2 focus:ring-indigo-500 outline-none transition-all" />
                            </div>

                            <div>
                                <label className="block text-sm font-bold text-gray-700 mb-2">Описание</label>
                                <textarea required value={formData.description} onChange={e => setFormData({...formData, description: e.target.value})} className="w-full p-4 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:ring-2 focus:ring-indigo-500 outline-none h-32 transition-all"></textarea>
                            </div>

                            <button type="submit" className="flex items-center justify-center gap-2 w-full bg-indigo-600 text-white font-bold py-4 rounded-xl hover:bg-indigo-700 transition-colors shadow-md">
                                <PlusCircle className="w-5 h-5" /> Опубликовать товар
                            </button>
                        </form>
                    </div>
                )}

                {/* Вкладка: Список пользователей */}
                {activeTab === 'users' && (
                    <div className="max-w-5xl bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                        <div className="p-8 border-b border-gray-100 flex justify-between items-center bg-white">
                            <h2 className="text-3xl font-black text-gray-900">Пользователи</h2>
                            <span className="bg-indigo-50 text-indigo-600 px-4 py-2 rounded-full font-bold text-sm">
                                Всего: {users.length}
                            </span>
                        </div>
                        <div className="overflow-x-auto p-8 pt-0 mt-6">
                            <table className="w-full text-left">
                                <thead>
                                <tr className="text-gray-400 border-b border-gray-100">
                                    <th className="pb-4 font-semibold pl-4">ID</th>
                                    <th className="pb-4 font-semibold">Имя</th>
                                    <th className="pb-4 font-semibold">Email</th>
                                    <th className="pb-4 font-semibold">Роль</th>
                                    <th className="pb-4 font-semibold">Регистрация</th>
                                </tr>
                                </thead>
                                <tbody>
                                {users.map(user => (
                                    <tr key={user.id} className="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                                        <td className="py-5 pl-4 text-gray-500 font-medium">#{user.id}</td>
                                        <td className="py-5 font-bold text-gray-900">{user.name}</td>
                                        <td className="py-5 text-gray-600">{user.email}</td>
                                        <td className="py-5">
                                            {user.role === 'admin'
                                                ? <span className="bg-red-100 text-red-600 px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider">Admin</span>
                                                : <span className="bg-green-100 text-green-600 px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider">User</span>
                                            }
                                        </td>
                                        <td className="py-5 text-sm text-gray-400 font-medium">
                                            {new Date(user.created_at).toLocaleDateString('ru-RU')}
                                        </td>
                                    </tr>
                                ))}
                                </tbody>
                            </table>
                        </div>
                    </div>
                )}
            </div>
        </div>
    );
}
