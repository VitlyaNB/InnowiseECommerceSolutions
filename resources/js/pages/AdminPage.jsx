import React, { useState, useEffect } from 'react';
import axios from 'axios';
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
    X
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
    const [editUserData, setEditUserData] = useState({});

    const authHeader = {
        headers: { Authorization: `Bearer ${localStorage.getItem('auth_token')}` }
    };

    const fetchCategories = () => {
        axios.get('/api/categories')
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
        axios.get('/api/users', authHeader)
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

            await axios.post('/api/products', form, {
                headers: {
                    ...authHeader.headers,
                    'Content-Type': 'multipart/form-data'
                }
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
            setTimeout(() => setMsg({ text: '', isError: false }), 4000);
        } catch (error) {
            setMsg({ text: error.response?.data?.message || 'Ошибка при добавлении товара', isError: true });
        }
    };

    const handleCategorySubmit = async (e) => {
        e.preventDefault();
        if (!categoryName.trim()) return;

        const form = new FormData();
        form.append('name', categoryName.trim());
        if (categoryImage) {
            form.append('image', categoryImage);
        }

        try {
            await axios.post('/api/categories', form, {
                headers: {
                    ...authHeader.headers,
                    'Content-Type': 'multipart/form-data'
                }
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

    const handleEditUser = (user) => {
        setEditingUserId(user.id);
        setEditUserData({ name: user.name, email: user.email, role: user.role });
    };

    const handleSaveUser = async (id) => {
        try {
            await axios.put(`/api/users/${id}`, editUserData, authHeader);
            setEditingUserId(null);
            setMsg({ text: 'Пользователь обновлен', isError: false });
            fetchUsers();
        } catch (err) {
            setMsg({ text: 'Ошибка сохранения', isError: true });
        }
        setTimeout(() => setMsg({ text: '', isError: false }), 3000);
    };

    const handleDeleteUser = async (id) => {
        if (!window.confirm("Вы уверены?")) return;
        try {
            await axios.delete(`/api/users/${id}`, authHeader);
            setMsg({ text: 'Пользователь удален', isError: false });
            fetchUsers();
        } catch (err) {
            setMsg({ text: 'Ошибка удаления', isError: true });
        }
        setTimeout(() => setMsg({ text: '', isError: false }), 3000);
    };

    return (
        <div className="min-h-[calc(100vh-80px)] bg-gray-50 flex dark:bg-gray-900">
            {/* Боковое меню */}
            <div className="w-72 bg-white shadow-sm border-r border-gray-200 flex flex-col dark:bg-gray-800 dark:border-gray-700">
                <div className="p-6 border-b border-gray-100 dark:border-gray-700 flex items-center gap-3">
                    <LayoutDashboard className="w-8 h-8 text-indigo-600" />
                    <h1 className="text-xl font-black text-gray-900 tracking-tight dark:text-white">АДМИНКА</h1>
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
                        <ArrowLeft className="w-5 h-5" /> В магазин
                    </Link>
                </div>
            </div>

            {/* Контент */}
            <div className="flex-1 p-10 overflow-y-auto">
                {msg.text && (
                    <div className={`fixed top-24 right-10 z-50 p-4 rounded-xl flex items-center gap-3 font-bold border shadow-lg animate-in fade-in slide-in-from-top-4 ${msg.isError ? 'bg-red-50 text-red-700 border-red-100' : 'bg-green-50 text-green-700 border-green-100'}`}>
                        {msg.isError ? <AlertCircle className="w-5 h-5" /> : <PlusCircle className="w-5 h-5" />}
                        {msg.text}
                        <button onClick={() => setMsg({text: '', isError: false})}><X className="w-4 h-4" /></button>
                    </div>
                )}

                {activeTab === 'addProduct' && (
                    <div className="max-w-3xl bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-10">
                        <h2 className="text-3xl font-black text-gray-900 dark:text-white mb-8">Новый товар</h2>
                        <form onSubmit={handleProductSubmit} className="space-y-6">
                            <div>
                                <label className="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Название товара</label>
                                <input type="text" required value={formData.name} onChange={e => setFormData({...formData, name: e.target.value})} className="w-full p-4 border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none" />
                            </div>
                            <div className="grid grid-cols-2 gap-6">
                                <div>
                                    <label className="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Цена (BYN)</label>
                                    <input type="number" step="0.01" required value={formData.price} onChange={e => setFormData({...formData, price: e.target.value})} className="w-full p-4 border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none" />
                                </div>
                                <div>
                                    <label className="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Количество</label>
                                    <input type="number" required value={formData.quantity} onChange={e => setFormData({...formData, quantity: e.target.value})} className="w-full p-4 border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none" />
                                </div>
                            </div>
                            <div>
                                <label className="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Категория</label>
                                <select required value={formData.category_id} onChange={e => setFormData({...formData, category_id: e.target.value})} className="w-full p-4 border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 dark:text-white outline-none">
                                    {categories.map(cat => <option key={cat.id} value={cat.id}>{cat.name}</option>)}
                                </select>
                            </div>
                            <div>
                                <label className="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Фотографии товара</label>
                                <div className="relative w-full border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl p-8 flex flex-col items-center justify-center text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors cursor-pointer">
                                    <ImageIcon className="w-10 h-10 mb-2 text-indigo-400" />
                                    <span className="text-sm font-bold">Выберите изображения</span>
                                    <input type="file" multiple accept="image/*" onChange={e => setImages(e.target.files)} className="absolute inset-0 w-full h-full opacity-0 cursor-pointer" />
                                </div>
                                {images.length > 0 && <p className="mt-2 text-indigo-600 font-bold text-sm">Выбрано: {images.length}</p>}
                            </div>
                            <div>
                                <label className="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Описание</label>
                                <textarea required value={formData.description} onChange={e => setFormData({...formData, description: e.target.value})} className="w-full p-4 border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700 dark:text-white h-32 outline-none"></textarea>
                            </div>
                            <button type="submit" className="w-full bg-indigo-600 text-white font-black py-5 rounded-2xl hover:bg-indigo-700 transition-all shadow-lg active:scale-[0.98]">Создать товар</button>
                        </form>
                    </div>
                )}

                {activeTab === 'categories' && (
                    <div className="max-w-3xl bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 p-10">
                        <div className="flex items-center justify-between mb-8">
                            <h2 className="text-3xl font-black text-gray-900 dark:text-white">Категории</h2>
                            <button onClick={async () => {
                                setSyncing(true);
                                try {
                                    const res = await axios.post('/api/categories/sync', {}, authHeader);
                                    setMsg({ text: res.data?.message || 'Синхронизация завершена', isError: false });
                                    fetchCategories();
                                } catch (err) { setMsg({ text: 'Ошибка синхронизации', isError: true }); }
                                finally { setSyncing(false); setTimeout(() => setMsg({ text: '', isError: false }), 5000); }
                            }} disabled={syncing} className="flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-xl font-bold text-gray-700 dark:text-gray-300 disabled:opacity-50">
                            </button>
                        </div>

                        <form onSubmit={handleCategorySubmit} className="flex flex-col gap-4 mb-8 bg-gray-50 dark:bg-gray-700/50 p-6 rounded-2xl border border-gray-100 dark:border-gray-600">
                            <div className="flex gap-4">
                                <input type="text" value={categoryName} onChange={e => setCategoryName(e.target.value)} placeholder="Название категории" className="flex-1 p-4 border border-gray-200 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none" />
                                <button type="submit" className="px-6 py-4 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 transition-all">Добавить</button>
                            </div>
                            <div className="relative border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl p-4 flex flex-col items-center justify-center text-gray-500 hover:bg-white dark:hover:bg-gray-700 transition-colors cursor-pointer">
                                <ImageIcon className="w-6 h-6 mb-1 text-indigo-400" />
                                <span className="text-xs font-bold">{categoryImage ? `Выбран: ${categoryImage.name}` : "Фото категории"}</span>
                                <input type="file" accept="image/*" onChange={e => setCategoryImage(e.target.files[0])} className="absolute inset-0 w-full h-full opacity-0 cursor-pointer" />
                            </div>
                        </form>

                        <div className="space-y-2">
                            {categories.map(cat => (
                                <div key={cat.id} className="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl border border-transparent hover:border-indigo-100 transition-all">
                                    <div className="flex items-center gap-4">
                                        {cat.image_path && <img src={cat.image_path} alt="" className="w-10 h-10 rounded-lg object-cover" />}
                                        <span className="font-bold text-gray-900 dark:text-white">{cat.name}</span>
                                    </div>
                                    <div className="flex gap-2">
                                        <button onClick={() => { setEditingCategoryId(cat.id); setEditCategoryName(cat.name); }} className="p-2 text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 rounded-lg"><Edit className="w-4 h-4" /></button>
                                        <button onClick={async () => { if (window.confirm("Удалить?")) try { await axios.delete(`/api/categories/${cat.id}`, authHeader); fetchCategories(); } catch(e){} }} className="p-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg"><Trash2 className="w-4 h-4" /></button>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>
                )}

                {activeTab === 'users' && (
                    <div className="max-w-5xl bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                        <div className="p-8 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                            <h2 className="text-3xl font-black text-gray-900 dark:text-white">Пользователи</h2>
                        </div>
                        <div className="overflow-x-auto p-8">
                            <table className="w-full text-left">
                                <thead>
                                <tr className="text-gray-400 border-b border-gray-100 dark:border-gray-700">
                                    <th className="pb-4 font-bold pl-4 uppercase text-xs">ID</th>
                                    <th className="pb-4 font-bold uppercase text-xs">Имя</th>
                                    <th className="pb-4 font-bold uppercase text-xs">Email</th>
                                    <th className="pb-4 font-bold uppercase text-xs">Роль</th>
                                    <th className="pb-4 font-bold text-right pr-4 uppercase text-xs">Действия</th>
                                </tr>
                                </thead>
                                <tbody>
                                {users.map(user => (
                                    <tr key={user.id} className="border-b border-gray-50 dark:border-gray-700/50 hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-colors">
                                        <td className="py-5 pl-4 text-gray-500 font-mono">#{user.id}</td>
                                        <td className="py-5">
                                            {editingUserId === user.id ?
                                                <input className="border rounded-lg px-2 py-1 dark:bg-gray-700 dark:text-white" value={editUserData.name} onChange={e => setEditUserData({...editUserData, name: e.target.value})} />
                                                : <span className="font-bold text-gray-900 dark:text-white">{user.name}</span>}
                                        </td>
                                        <td className="py-5 dark:text-gray-300">{user.email}</td>
                                        <td className="py-5">
                                            <span className={`px-3 py-1 rounded-full text-[10px] font-black uppercase ${user.role === 'admin' ? 'bg-red-100 text-red-600' : 'bg-green-100 text-green-600'}`}>{user.role}</span>
                                        </td>
                                        <td className="py-5 text-right pr-4">
                                            <div className="flex justify-end gap-2">
                                                {editingUserId === user.id ?
                                                    <button onClick={() => handleSaveUser(user.id)} className="bg-indigo-600 text-white px-3 py-1 rounded-lg text-xs">ОК</button>
                                                    : <button onClick={() => handleEditUser(user)} className="p-2 text-indigo-400 hover:bg-indigo-50 rounded-full"><Edit className="w-4 h-4" /></button>}
                                                <button onClick={() => handleDeleteUser(user.id)} className="p-2 text-red-400 hover:bg-red-50 rounded-full"><Trash2 className="w-4 h-4" /></button>
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
