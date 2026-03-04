import React, { useState, useEffect } from 'react';
import api from '../api';
import { Link } from 'react-router-dom';
import {
    ArrowLeft,
    PlusCircle,
    Users,
    LayoutDashboard,
    Tag,
    Image as ImageIcon,
    Trash2,
    Edit,
    AlertCircle,
    FolderTree,
    RefreshCw,
    X,
    Check
} from 'lucide-react';

export default function AdminPage() {
    const [activeTab, setActiveTab] = useState('addProduct');
    const [users, setUsers] = useState([]);
    const [categories, setCategories] = useState([]);

    // Состояния для товара
    const [formData, setFormData] = useState({
        name: '',
        description: '',
        price: '',
        quantity: '1',
        category_id: ''
    });
    const [images, setImages] = useState([]);

    // Состояния для категории
    const [categoryName, setCategoryName] = useState('');
    const [categoryImage, setCategoryImage] = useState(null);
    const [editingCategoryId, setEditingCategoryId] = useState(null);
    const [editCategoryName, setEditCategoryName] = useState('');

    const [msg, setMsg] = useState({ text: '', isError: false });
    const [syncing, setSyncing] = useState(false);

    // Состояния для пользователей
    const [editingUserId, setEditingUserId] = useState(null);
    const [editUserData, setEditUserData] = useState({ name: '', email: '', role: 'user' });

    // Загрузка данных
    const fetchCategories = () => {
        api.get('/categories')
            .then(res => {
                const fetched = res.data.data || res.data;
                setCategories(fetched);
                if (fetched.length > 0 && !formData.category_id) {
                    setFormData(prev => ({ ...prev, category_id: fetched[0].id }));
                }
            })
            .catch(err => console.error('Ошибка загрузки категорий:', err));
    };

    const fetchUsers = () => {
        api.get('/users')
            .then(res => setUsers(res.data.data || res.data))
            .catch(err => console.error("Ошибка загрузки пользователей:", err));
    };

    useEffect(() => {
        fetchCategories();
    }, []);

    useEffect(() => {
        if (activeTab === 'users') fetchUsers();
        if (activeTab === 'categories') fetchCategories();
    }, [activeTab]);

    // Обработка товаров
    const handleProductSubmit = async (e) => {
        e.preventDefault();
        setMsg({ text: 'Загрузка...', isError: false });

        try {
            const form = new FormData();
            form.append('name', formData.name);
            form.append('description', formData.description);
            form.append('price', formData.price);
            form.append('quantity', formData.quantity);
            form.append('category_id', formData.category_id);

            if (images && images.length > 0) {
                Array.from(images).forEach((file) => {
                    form.append('images[]', file);
                });
            }

            await api.post('/products', form, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });

            setMsg({ text: 'Товар успешно добавлен!', isError: false });
            setFormData({
                name: '',
                description: '',
                price: '',
                quantity: '1',
                category_id: categories[0]?.id || ''
            });
            setImages([]);
        } catch (error) {
            setMsg({ text: error.response?.data?.message || 'Ошибка при добавлении товара', isError: true });
        }
        setTimeout(() => setMsg({ text: '', isError: false }), 4000);
    };

    // Обработка категорий
    const handleCategorySubmit = async (e) => {
        e.preventDefault();
        if (!categoryName.trim()) return;

        const form = new FormData();
        form.append('name', categoryName.trim());
        if (categoryImage) {
            form.append('image', categoryImage);
        }

        try {
            await api.post('/categories', form, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });
            setCategoryName('');
            setCategoryImage(null);
            setMsg({ text: 'Категория успешно добавлена!', isError: false });
            fetchCategories();
        } catch (err) {
            setMsg({ text: err.response?.data?.message || 'Ошибка добавления категории', isError: true });
        }
        setTimeout(() => setMsg({ text: '', isError: false }), 4000);
    };

    // Обработка пользователей
    const handleEditUser = (user) => {
        setEditingUserId(user.id);
        setEditUserData({ name: user.name, email: user.email, role: user.role });
    };

    const handleSaveUser = async (id) => {
        try {
            await api.put(`/users/${id}`, editUserData);
            setEditingUserId(null);
            setMsg({ text: 'Пользователь обновлен', isError: false });
            fetchUsers();
        } catch (err) {
            setMsg({ text: err.response?.data?.message || 'Ошибка сохранения', isError: true });
        }
        setTimeout(() => setMsg({ text: '', isError: false }), 3000);
    };

    const handleDeleteUser = async (id) => {
        if (!window.confirm("Вы уверены, что хотите навсегда удалить пользователя из базы данных?")) return;
        try {
            await api.delete(`/users/${id}`);
            setMsg({ text: 'Пользователь удален', isError: false });
            fetchUsers();
        } catch (err) {
            setMsg({ text: err.response?.data?.message || 'Ошибка удаления', isError: true });
        }
        setTimeout(() => setMsg({ text: '', isError: false }), 3000);
    };

    return (
        <div className="min-h-[calc(100vh-80px)] bg-gray-50 flex dark:bg-gray-900">
            {/* Сайдбар */}
            <div className="w-72 bg-white shadow-sm border-r border-gray-200 flex flex-col dark:bg-gray-800 dark:border-gray-700">
                <div className="p-6 border-b border-gray-100 dark:border-gray-700 flex items-center gap-3">
                    <LayoutDashboard className="w-8 h-8 text-indigo-600" />
                    <h1 className="text-xl font-black text-gray-900 tracking-tight dark:text-white uppercase">Панель</h1>
                </div>
                <div className="p-4 flex flex-col gap-2 flex-1">
                    <button onClick={() => setActiveTab('addProduct')} className={`flex items-center gap-3 p-4 rounded-xl font-bold transition-all ${activeTab === 'addProduct' ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700'}`}>
                        <PlusCircle className="w-5 h-5" /> Добавить товар
                    </button>
                    <button onClick={() => setActiveTab('categories')} className={`flex items-center gap-3 p-4 rounded-xl font-bold transition-all ${activeTab === 'categories' ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700'}`}>
                        <FolderTree className="w-5 h-5" /> Категории
                    </button>
                    <button onClick={() => setActiveTab('users')} className={`flex items-center gap-3 p-4 rounded-xl font-bold transition-all ${activeTab === 'users' ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700'}`}>
                        <Users className="w-5 h-5" /> Пользователи
                    </button>
                </div>
                <div className="p-4 border-t border-gray-100 dark:border-gray-700">
                    <Link to="/" className="flex items-center gap-3 p-4 text-gray-500 hover:text-gray-900 dark:hover:text-white transition-colors font-bold rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700">
                        <ArrowLeft className="w-5 h-5" /> На главную
                    </Link>
                </div>
            </div>

            {/* Основной контент */}
            <div className="flex-1 p-10 overflow-y-auto">
                {msg.text && (
                    <div className={`fixed top-24 right-10 z-50 p-4 rounded-xl flex items-center gap-3 font-bold border shadow-lg animate-in fade-in slide-in-from-top-4 ${msg.isError ? 'bg-red-50 text-red-700 border-red-100' : 'bg-green-50 text-green-700 border-green-100'}`}>
                        {msg.isError ? <AlertCircle className="w-5 h-5" /> : <Check className="w-5 h-5" />}
                        {msg.text}
                        <button onClick={() => setMsg({text: '', isError: false})}><X className="w-4 h-4" /></button>
                    </div>
                )}

                {/* Вкладка: Товар */}
                {activeTab === 'addProduct' && (
                    <div className="max-w-3xl bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-10">
                        <h2 className="text-3xl font-black text-gray-900 dark:text-white mb-8">Создание товара</h2>
                        <form onSubmit={handleProductSubmit} className="space-y-6">
                            <div>
                                <label className="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Название</label>
                                <input type="text" required value={formData.name} onChange={e => setFormData({...formData, name: e.target.value})} className="w-full p-4 border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 dark:text-white outline-none focus:ring-2 focus:ring-indigo-500" />
                            </div>
                            <div className="grid grid-cols-2 gap-6">
                                <div>
                                    <label className="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Цена (BYN)</label>
                                    <input type="number" step="0.01" required value={formData.price} onChange={e => setFormData({...formData, price: e.target.value})} className="w-full p-4 border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 dark:text-white outline-none" />
                                </div>
                                <div>
                                    <label className="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Склад</label>
                                    <input type="number" required value={formData.quantity} onChange={e => setFormData({...formData, quantity: e.target.value})} className="w-full p-4 border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 dark:text-white outline-none" />
                                </div>
                            </div>
                            <div>
                                <label className="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Категория</label>
                                <select required value={formData.category_id} onChange={e => setFormData({...formData, category_id: e.target.value})} className="w-full p-4 border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 dark:text-white outline-none">
                                    {categories.map(cat => <option key={cat.id} value={cat.id}>{cat.name}</option>)}
                                </select>
                            </div>
                            <div>
                                <label className="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Изображения</label>
                                <div className="relative w-full border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl p-8 flex flex-col items-center justify-center text-gray-500 hover:bg-gray-50 transition-colors cursor-pointer">
                                    <ImageIcon className="w-10 h-10 mb-2 text-indigo-400" />
                                    <span className="text-sm font-bold">Загрузить фото</span>
                                    <input type="file" multiple accept="image/*" onChange={e => setImages(e.target.files)} className="absolute inset-0 w-full h-full opacity-0 cursor-pointer" />
                                </div>
                                {images.length > 0 && <p className="mt-2 text-indigo-600 font-bold text-sm">Файлов: {images.length}</p>}
                            </div>
                            <div>
                                <label className="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Описание</label>
                                <textarea required value={formData.description} onChange={e => setFormData({...formData, description: e.target.value})} className="w-full p-4 border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 dark:text-white h-32 outline-none"></textarea>
                            </div>
                            <button type="submit" className="w-full bg-indigo-600 text-white font-black py-5 rounded-2xl hover:bg-indigo-700 transition-all shadow-lg active:scale-95">Опубликовать</button>
                        </form>
                    </div>
                )}

                {/* Вкладка: Категории */}
                {activeTab === 'categories' && (
                    <div className="max-w-3xl bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-10">
                        <div className="flex items-center justify-between mb-8">
                            <h2 className="text-3xl font-black text-gray-900 dark:text-white">Категории</h2>
                            <button onClick={async () => {
                                setSyncing(true);
                                try {
                                    await api.post('/categories/sync', {});
                                    fetchCategories();
                                    setMsg({ text: 'Синхронизация завершена', isError: false });
                                } catch (err) { setMsg({ text: 'Ошибка синхронизации', isError: true }); }
                                finally { setSyncing(false); }
                            }} disabled={syncing} className="p-2 bg-gray-100 dark:bg-gray-700 rounded-xl hover:bg-gray-200 transition-colors">
                                <RefreshCw className={`w-5 h-5 ${syncing ? 'animate-spin' : ''}`} />
                            </button>
                        </div>

                        <form onSubmit={handleCategorySubmit} className="space-y-4 mb-10 p-6 bg-gray-50 dark:bg-gray-700/50 rounded-2xl border border-gray-100 dark:border-gray-600">
                            <input type="text" value={categoryName} onChange={e => setCategoryName(e.target.value)} placeholder="Название новой категории" className="w-full p-4 border border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 dark:text-white outline-none" />
                            <div className="relative border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl p-4 flex flex-col items-center justify-center text-gray-400">
                                <ImageIcon className="w-6 h-6 mb-1" />
                                <span className="text-xs font-bold">{categoryImage ? categoryImage.name : "Иконка категории"}</span>
                                <input type="file" onChange={e => setCategoryImage(e.target.files[0])} className="absolute inset-0 w-full h-full opacity-0 cursor-pointer" />
                            </div>
                            <button type="submit" className="w-full bg-indigo-600 text-white font-bold py-3 rounded-xl hover:bg-indigo-700">Создать категорию</button>
                        </form>

                        <div className="grid gap-3">
                            {categories.map(cat => (
                                <div key={cat.id} className="flex items-center justify-between p-4 bg-white dark:bg-gray-700 rounded-2xl border border-gray-100 dark:border-gray-600 hover:shadow-md transition-shadow">
                                    <div className="flex items-center gap-4">
                                        <div className="w-10 h-10 bg-indigo-100 dark:bg-indigo-900/50 rounded-lg flex items-center justify-center">
                                            <Tag className="w-5 h-5 text-indigo-600" />
                                        </div>
                                        <span className="font-bold text-gray-900 dark:text-white">{cat.name}</span>
                                    </div>
                                    <button onClick={async () => { if(window.confirm("Удалить категорию?")) { await api.delete(`/categories/${cat.id}`); fetchCategories(); } }} className="p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg">
                                        <Trash2 className="w-5 h-5" />
                                    </button>
                                </div>
                            ))}
                        </div>
                    </div>
                )}

                {/* Вкладка: Пользователи */}
                {activeTab === 'users' && (
                    <div className="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                        <div className="p-8 border-b border-gray-100 dark:border-gray-700">
                            <h2 className="text-3xl font-black text-gray-900 dark:text-white">Управление доступом</h2>
                        </div>
                        <div className="overflow-x-auto">
                            <table className="w-full">
                                <thead>
                                <tr className="bg-gray-50 dark:bg-gray-700/30 text-left text-gray-400 text-xs font-black uppercase tracking-wider">
                                    <th className="px-8 py-5">ID</th>
                                    <th className="px-8 py-5">Пользователь</th>
                                    <th className="px-8 py-5">Email</th>
                                    <th className="px-8 py-5">Роль</th>
                                    <th className="px-8 py-5 text-right">Действия</th>
                                </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-100 dark:divide-gray-700">
                                {users.map(user => (
                                    <tr key={user.id} className="hover:bg-gray-50/50 dark:hover:bg-gray-700/20 transition-colors">
                                        <td className="px-8 py-5 font-mono text-gray-400 text-sm">#{user.id}</td>
                                        <td className="px-8 py-5">
                                            {editingUserId === user.id ? (
                                                <input className="p-2 border rounded-lg dark:bg-gray-700 dark:text-white" value={editUserData.name} onChange={e => setEditUserData({...editUserData, name: e.target.value})} />
                                            ) : <span className="font-bold text-gray-900 dark:text-white">{user.name}</span>}
                                        </td>
                                        <td className="px-8 py-5">
                                            {editingUserId === user.id ? (
                                                <input className="p-2 border rounded-lg dark:bg-gray-700 dark:text-white" value={editUserData.email} onChange={e => setEditUserData({...editUserData, email: e.target.value})} />
                                            ) : <span className="text-gray-500 dark:text-gray-400">{user.email}</span>}
                                        </td>
                                        <td className="px-8 py-5">
                                            {editingUserId === user.id ? (
                                                <select className="p-2 border rounded-lg dark:bg-gray-700 dark:text-white" value={editUserData.role} onChange={e => setEditUserData({...editUserData, role: e.target.value})}>
                                                    <option value="user">User</option>
                                                    <option value="admin">Admin</option>
                                                </select>
                                            ) : (
                                                <span className={`px-3 py-1 rounded-full text-[10px] font-black uppercase ${user.role === 'admin' ? 'bg-red-100 text-red-600' : 'bg-green-100 text-green-600'}`}>
                                                        {user.role}
                                                    </span>
                                            )}
                                        </td>
                                        <td className="px-8 py-5 text-right">
                                            <div className="flex justify-end gap-3">
                                                {editingUserId === user.id ? (
                                                    <>
                                                        <button onClick={() => handleSaveUser(user.id)} className="p-2 text-green-600 bg-green-50 rounded-lg hover:bg-green-100"><Check className="w-5 h-5" /></button>
                                                        <button onClick={() => setEditingUserId(null)} className="p-2 text-gray-400 bg-gray-50 rounded-lg"><X className="w-5 h-5" /></button>
                                                    </>
                                                ) : (
                                                    <>
                                                        <button onClick={() => handleEditUser(user)} className="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg"><Edit className="w-5 h-5" /></button>
                                                        <button onClick={() => handleDeleteUser(user.id)} className="p-2 text-red-500 hover:bg-red-50 rounded-lg"><Trash2 className="w-5 h-5" /></button>
                                                    </>
                                                )}
                                            </div>
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
