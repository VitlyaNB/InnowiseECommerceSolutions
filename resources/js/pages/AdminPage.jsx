import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { Link } from 'react-router-dom';
import { ArrowLeft, PlusCircle, Users, LayoutDashboard, Tag, Image as ImageIcon, Trash2, Edit } from 'lucide-react';

export default function AdminPage() {
    const [activeTab, setActiveTab] = useState('addProduct');
    const [users, setUsers] = useState([]);
    const [categories, setCategories] = useState([]);

    const [formData, setFormData] = useState({
        name: '', description: '', price: '', quantity: '1', category_id: ''
    });
    const [images, setImages] = useState([]);
    const [msg, setMsg] = useState('');

    // Стейты для редактирования юзеров
    const [editingUserId, setEditingUserId] = useState(null);
    const [editUserData, setEditUserData] = useState({});

    useEffect(() => {
        axios.get('/api/categories').then(res => {
            const fetchedCats = res.data.data || res.data;
            setCategories(fetchedCats);
            if (fetchedCats.length > 0) {
                setFormData(prev => ({ ...prev, category_id: fetchedCats[0].id }));
            }
        });
    }, []);

    const fetchUsers = () => {
        axios.get('/api/users').then(res => setUsers(res.data.data || res.data));
    }

    useEffect(() => {
        if (activeTab === 'users') fetchUsers();
    }, [activeTab]);

    const handleProductSubmit = async (e) => {
        e.preventDefault();
        try {
            // Формируем FormData для отправки файлов
            const form = new FormData();
            form.append('name', formData.name);
            form.append('description', formData.description);
            form.append('price', formData.price);
            form.append('quantity', formData.quantity);
            form.append('category_id', formData.category_id);

            // Прикрепляем все выбранные файлы
            Array.from(images).forEach((file, index) => {
                form.append(`images[${index}]`, file);
            });

            await axios.post('/api/products', form, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });

            setMsg('Товар успешно добавлен!');
            setFormData({ name: '', description: '', price: '', quantity: '1', category_id: categories[0]?.id || '' });
            setImages([]);
            setTimeout(() => setMsg(''), 4000);
        } catch (error) {
            setMsg('Ошибка при добавлении. Проверь консоль.');
            console.error(error);
        }
    };

    const handleEditUser = (user) => {
        setEditingUserId(user.id);
        setEditUserData({ name: user.name, email: user.email, role: user.role });
    };

    const handleSaveUser = async (id) => {
        try {
            await axios.put(`/api/users/${id}`, editUserData);
            setEditingUserId(null);
            fetchUsers();
        } catch (err) {
            console.error("Ошибка сохранения пользователя", err);
        }
    };

    const handleDeleteUser = async (id) => {
        if (!window.confirm("Удалить пользователя?")) return;
        try {
            await axios.delete(`/api/users/${id}`);
            fetchUsers();
        } catch (err) {
            console.error("Ошибка удаления", err);
        }
    };

    return (
        <div className="min-h-[calc(100vh-80px)] bg-gray-50 flex">
            {/* Боковое меню */}
            <div className="w-72 bg-white shadow-sm border-r border-gray-200 flex flex-col">
                <div className="p-6 border-b border-gray-100 flex items-center gap-3">
                    <LayoutDashboard className="w-8 h-8 text-indigo-600" />
                    <h1 className="text-xl font-black text-gray-900 tracking-tight">АДМИНКА</h1>
                </div>
                <div className="p-4 flex flex-col gap-2 flex-1">
                    <button onClick={() => setActiveTab('addProduct')} className={`flex items-center gap-3 p-4 rounded-xl font-bold transition-all ${activeTab === 'addProduct' ? 'bg-indigo-50 text-indigo-600' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900'}`}>
                        <PlusCircle className="w-5 h-5" /> Добавить товар
                    </button>
                    <button onClick={() => setActiveTab('users')} className={`flex items-center gap-3 p-4 rounded-xl font-bold transition-all ${activeTab === 'users' ? 'bg-indigo-50 text-indigo-600' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900'}`}>
                        <Users className="w-5 h-5" /> Пользователи
                    </button>
                </div>
                <div className="p-4 border-t border-gray-100">
                    <Link to="/" className="flex items-center gap-3 p-4 text-gray-500 hover:text-gray-900 transition-colors font-bold rounded-xl hover:bg-gray-50">
                        <ArrowLeft className="w-5 h-5" /> В магазин
                    </Link>
                </div>
            </div>

            {/* Контент */}
            <div className="flex-1 p-10 overflow-y-auto">
                {activeTab === 'addProduct' && (
                    <div className="max-w-3xl bg-white rounded-3xl shadow-sm border border-gray-100 p-10">
                        <h2 className="text-3xl font-black text-gray-900 mb-8">Новый товар</h2>
                        {msg && <div className="mb-6 p-4 rounded-xl bg-green-50 text-green-700 font-bold border border-green-100">{msg}</div>}

                        <form onSubmit={handleProductSubmit} className="space-y-6">
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
                                <label className="block text-sm font-bold text-gray-700 mb-2">Категория</label>
                                <div className="relative">
                                    <select required value={formData.category_id} onChange={e => setFormData({...formData, category_id: e.target.value})} className="w-full p-4 pl-10 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:ring-2 focus:ring-indigo-500 outline-none transition-all appearance-none">
                                        <option value="" disabled>Выберите категорию</option>
                                        {categories.map(cat => (
                                            <option key={cat.id} value={cat.id}>{cat.name}</option>
                                        ))}
                                    </select>
                                    <Tag className="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400 pointer-events-none" />
                                </div>
                            </div>

                            {/* Загрузка фото с компьютера */}
                            <div>
                                <label className="block text-sm font-bold text-gray-700 mb-2">Фотографии товара</label>
                                <div className="relative w-full border-2 border-dashed border-gray-300 rounded-xl p-6 flex flex-col items-center justify-center text-gray-500 hover:bg-gray-50 transition-colors">
                                    <ImageIcon className="w-8 h-8 mb-2 text-indigo-400" />
                                    <span className="text-sm font-medium">Нажмите или перетащите файлы сюда</span>
                                    <input
                                        type="file"
                                        multiple
                                        accept="image/*"
                                        onChange={e => setImages(e.target.files)}
                                        className="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                    />
                                </div>
                                {images.length > 0 && <p className="text-xs text-green-600 font-bold mt-2">Выбрано файлов: {images.length}</p>}
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

                {/* Таблица пользователей */}
                {activeTab === 'users' && (
                    <div className="max-w-5xl bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                        <div className="p-8 border-b border-gray-100 flex justify-between items-center bg-white">
                            <h2 className="text-3xl font-black text-gray-900">Пользователи</h2>
                            <span className="bg-indigo-50 text-indigo-600 px-4 py-2 rounded-full font-bold text-sm">Всего: {users.length}</span>
                        </div>
                        <div className="overflow-x-auto p-8 pt-0 mt-6">
                            <table className="w-full text-left">
                                <thead>
                                <tr className="text-gray-400 border-b border-gray-100">
                                    <th className="pb-4 font-semibold pl-4">ID</th>
                                    <th className="pb-4 font-semibold">Имя</th>
                                    <th className="pb-4 font-semibold">Email</th>
                                    <th className="pb-4 font-semibold">Роль</th>
                                    <th className="pb-4 font-semibold text-right pr-4">Действия</th>
                                </tr>
                                </thead>
                                <tbody>
                                {users.map(user => (
                                    <tr key={user.id} className="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                                        <td className="py-4 pl-4 text-gray-500 font-medium">#{user.id}</td>

                                        {/* Режим редактирования */}
                                        {editingUserId === user.id ? (
                                            <>
                                                <td className="py-4"><input className="border p-1 rounded w-full" value={editUserData.name} onChange={e => setEditUserData({...editUserData, name: e.target.value})} /></td>
                                                <td className="py-4"><input className="border p-1 rounded w-full" value={editUserData.email} onChange={e => setEditUserData({...editUserData, email: e.target.value})} /></td>
                                                <td className="py-4">
                                                    <select className="border p-1 rounded" value={editUserData.role} onChange={e => setEditUserData({...editUserData, role: e.target.value})}>
                                                        <option value="user">User</option>
                                                        <option value="admin">Admin</option>
                                                    </select>
                                                </td>
                                                <td className="py-4 text-right pr-4">
                                                    <button onClick={() => handleSaveUser(user.id)} className="bg-green-500 text-white px-3 py-1 rounded text-xs font-bold mr-2">Сохранить</button>
                                                    <button onClick={() => setEditingUserId(null)} className="bg-gray-300 text-gray-700 px-3 py-1 rounded text-xs font-bold">Отмена</button>
                                                </td>
                                            </>
                                        ) : (
                                            <>
                                                <td className="py-4 font-bold text-gray-900">{user.name}</td>
                                                <td className="py-4 text-gray-600">{user.email}</td>
                                                <td className="py-4">
                                                    {user.role === 'admin'
                                                        ? <span className="bg-red-100 text-red-600 px-3 py-1 rounded-full text-xs font-black uppercase">Admin</span>
                                                        : <span className="bg-green-100 text-green-600 px-3 py-1 rounded-full text-xs font-black uppercase">User</span>
                                                    }
                                                </td>
                                                <td className="py-4 text-right pr-4 flex justify-end gap-2">
                                                    <button onClick={() => handleEditUser(user)} className="p-2 text-blue-500 hover:bg-blue-50 rounded-full transition"><Edit className="w-4 h-4" /></button>
                                                    <button onClick={() => handleDeleteUser(user.id)} className="p-2 text-red-500 hover:bg-red-50 rounded-full transition"><Trash2 className="w-4 h-4" /></button>
                                                </td>
                                            </>
                                        )}
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
