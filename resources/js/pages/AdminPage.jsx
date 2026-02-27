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
    RefreshCw
} from 'lucide-react';

export default function AdminPage() {
    const [activeTab, setActiveTab] = useState('addProduct');
    const [users, setUsers] = useState([]);
    const [categories, setCategories] = useState([]);

    const [formData, setFormData] = useState({
        name: '',
        description: '',
        price: '',
        quantity: '1',
        category_id: ''
    });
    const [images, setImages] = useState([]);
    const [msg, setMsg] = useState({ text: '', isError: false });

    const [editingUserId, setEditingUserId] = useState(null);
    const [editUserData, setEditUserData] = useState({});

    const [categoryName, setCategoryName] = useState('');
    const [editingCategoryId, setEditingCategoryId] = useState(null);
    const [editCategoryName, setEditCategoryName] = useState('');
    const [syncing, setSyncing] = useState(false);

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

    useEffect(() => {
        fetchCategories();
    }, []);

    const fetchUsers = () => {
        axios.get('/api/users')
            .then(res => setUsers(res.data.data || res.data))
            .catch(err => console.error("Ошибка загрузки пользователей:", err));
    };

    useEffect(() => {
        if (activeTab === 'users') fetchUsers();
    }, [activeTab]);

    useEffect(() => {
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

            // Оставляем только один правильный цикл добавления файлов
            if (images && images.length > 0) {
                Array.from(images).forEach((file) => {
                    form.append('images[]', file);
                });
            }

            await axios.post('/api/products', form, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });

            setMsg({ text: 'Товар успешно добавлен!', isError: false });
            // Сброс формы
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
            console.error("Ошибка сервера:", error.response?.data);
            const errorMsg = error.response?.data?.message || 'Ошибка при добавлении. Проверьте консоль.';
            setMsg({ text: errorMsg, isError: true });
        }
    };

    // --- Логика CRUD пользователей ---
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
            console.error("Ошибка сохранения пользователя:", err);
        }
    };

    const handleDeleteUser = async (id) => {
        if (!window.confirm("Вы уверены, что хотите удалить этого пользователя?")) return;
        try {
            await axios.delete(`/api/users/${id}`);
            fetchUsers();
        } catch (err) {
            console.error("Ошибка при удалении:", err);
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
                    <button
                        onClick={() => setActiveTab('addProduct')}
                        className={`flex items-center gap-3 p-4 rounded-xl font-bold transition-all ${activeTab === 'addProduct' ? 'bg-indigo-50 text-indigo-600' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900'}`}
                    >
                        <PlusCircle className="w-5 h-5" /> Добавить товар
                    </button>
                    <button
                        onClick={() => setActiveTab('categories')}
                        className={`flex items-center gap-3 p-4 rounded-xl font-bold transition-all ${activeTab === 'categories' ? 'bg-indigo-50 text-indigo-600' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900'}`}
                    >
                        <FolderTree className="w-5 h-5" /> Категории
                    </button>
                    <button
                        onClick={() => setActiveTab('users')}
                        className={`flex items-center gap-3 p-4 rounded-xl font-bold transition-all ${activeTab === 'users' ? 'bg-indigo-50 text-indigo-600' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900'}`}
                    >
                        <Users className="w-5 h-5" /> Пользователи
                    </button>
                </div>
                <div className="p-4 border-t border-gray-100">
                    <Link to="/" className="flex items-center gap-3 p-4 text-gray-500 hover:text-gray-900 transition-colors font-bold rounded-xl hover:bg-gray-50">
                        <ArrowLeft className="w-5 h-5" /> В магазин
                    </Link>
                </div>
            </div>

            {/* Контентная часть */}
            <div className="flex-1 p-10 overflow-y-auto">
                {msg.text && (
                    <div className={`mb-6 p-4 rounded-xl flex items-center gap-3 font-bold border ${msg.isError ? 'bg-red-50 text-red-700 border-red-100' : 'bg-green-50 text-green-700 border-green-100'}`}>
                        {msg.isError ? <AlertCircle className="w-5 h-5" /> : <PlusCircle className="w-5 h-5" />}
                        {msg.text}
                    </div>
                )}

                {activeTab === 'addProduct' && (
                    <div className="max-w-3xl bg-white rounded-3xl shadow-sm border border-gray-100 p-10">
                        <h2 className="text-3xl font-black text-gray-900 mb-8">Новый товар</h2>
                        <form onSubmit={handleProductSubmit} className="space-y-6">
                            <div>
                                <label className="block text-sm font-bold text-gray-700 mb-2">Название товара</label>
                                <input
                                    type="text"
                                    required
                                    value={formData.name}
                                    onChange={e => setFormData({...formData, name: e.target.value})}
                                    className="w-full p-4 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:ring-2 focus:ring-indigo-500 outline-none transition-all"
                                />
                            </div>

                            <div className="grid grid-cols-2 gap-6">
                                <div>
                                    <label className="block text-sm font-bold text-gray-700 mb-2">Цена (₽)</label>
                                    <input
                                        type="number"
                                        step="0.01"
                                        required
                                        value={formData.price}
                                        onChange={e => setFormData({...formData, price: e.target.value})}
                                        className="w-full p-4 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:ring-2 focus:ring-indigo-500 outline-none transition-all"
                                    />
                                </div>
                                <div>
                                    <label className="block text-sm font-bold text-gray-700 mb-2">Количество</label>
                                    <input
                                        type="number"
                                        required
                                        value={formData.quantity}
                                        onChange={e => setFormData({...formData, quantity: e.target.value})}
                                        className="w-full p-4 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:ring-2 focus:ring-indigo-500 outline-none transition-all"
                                    />
                                </div>
                            </div>

                            <div>
                                <label className="block text-sm font-bold text-gray-700 mb-2">Категория</label>
                                <select
                                    required
                                    value={formData.category_id}
                                    onChange={e => setFormData({...formData, category_id: e.target.value})}
                                    className="w-full p-4 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:ring-2 focus:ring-indigo-500 outline-none transition-all"
                                >
                                    {categories.map(cat => (
                                        <option key={cat.id} value={cat.id}>{cat.name}</option>
                                    ))}
                                </select>
                            </div>

                            <div>
                                <label className="block text-sm font-bold text-gray-700 mb-2">Фотографии</label>
                                <div className="relative w-full border-2 border-dashed border-gray-300 rounded-xl p-8 flex flex-col items-center justify-center text-gray-500 hover:bg-gray-50 transition-colors cursor-pointer">
                                    <ImageIcon className="w-10 h-10 mb-2 text-indigo-400" />
                                    <span className="text-sm font-bold">Нажмите для выбора изображений</span>
                                    <input
                                        type="file"
                                        multiple
                                        accept="image/*"
                                        onChange={e => setImages(e.target.files)}
                                        className="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                    />
                                </div>
                                {images.length > 0 && (
                                    <p className="mt-2 text-indigo-600 font-bold text-sm">Выбрано файлов: {images.length}</p>
                                )}
                            </div>

                            <div>
                                <label className="block text-sm font-bold text-gray-700 mb-2">Описание</label>
                                <textarea
                                    required
                                    value={formData.description}
                                    onChange={e => setFormData({...formData, description: e.target.value})}
                                    className="w-full p-4 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:ring-2 focus:ring-indigo-500 outline-none h-32 transition-all"
                                ></textarea>
                            </div>

                            <button type="submit" className="w-full bg-indigo-600 text-white font-black py-5 rounded-2xl hover:bg-indigo-700 transition-all shadow-lg active:scale-[0.98]">
                                Создать товар
                            </button>
                        </form>
                    </div>
                )}

                {activeTab === 'categories' && (
                    <div className="max-w-3xl bg-white rounded-3xl shadow-sm border border-gray-100 p-10">
                        <div className="flex items-center justify-between mb-8">
                            <h2 className="text-3xl font-black text-gray-900">Категории</h2>
                            <button
                                onClick={async () => {
                                    setSyncing(true);
                                    try {
                                        const res = await axios.post('/api/categories/sync', {}, {
                                            headers: { Authorization: `Bearer ${localStorage.getItem('auth_token')}` }
                                        });
                                        setMsg({ text: res.data?.message || 'Синхронизация завершена', isError: false });
                                        fetchCategories();
                                    } catch (err) {
                                        setMsg({ text: err.response?.data?.message || 'Ошибка синхронизации', isError: true });
                                    } finally {
                                        setSyncing(false);
                                    }
                                    setTimeout(() => setMsg({ text: '', isError: false }), 5000);
                                }}
                                disabled={syncing}
                                className="flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-xl font-bold text-gray-700 disabled:opacity-50"
                            >
                                <RefreshCw className={`w-4 h-4 ${syncing ? 'animate-spin' : ''}`} />
                                Синхр. с другим проектом
                            </button>
                        </div>

                        <form
                            onSubmit={async (e) => {
                                e.preventDefault();
                                if (!categoryName.trim()) return;
                                try {
                                    await axios.post('/api/categories', { name: categoryName.trim() }, {
                                        headers: { Authorization: `Bearer ${localStorage.getItem('auth_token')}` }
                                    });
                                    setCategoryName('');
                                    setMsg({ text: 'Категория добавлена', isError: false });
                                    fetchCategories();
                                } catch (err) {
                                    setMsg({ text: err.response?.data?.message || 'Ошибка добавления', isError: true });
                                }
                                setTimeout(() => setMsg({ text: '', isError: false }), 4000);
                            }}
                            className="flex gap-4 mb-8"
                        >
                            <input
                                type="text"
                                value={categoryName}
                                onChange={e => setCategoryName(e.target.value)}
                                placeholder="Название категории"
                                className="flex-1 p-4 border border-gray-200 rounded-xl bg-gray-50 focus:ring-2 focus:ring-indigo-500 outline-none"
                            />
                            <button type="submit" className="px-6 py-4 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700">
                                Добавить
                            </button>
                        </form>

                        <div className="space-y-2">
                            {categories.map(cat => (
                                <div key={cat.id} className="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                                    {editingCategoryId === cat.id ? (
                                        <>
                                            <input
                                                value={editCategoryName}
                                                onChange={e => setEditCategoryName(e.target.value)}
                                                className="flex-1 p-2 border rounded-lg mr-4"
                                            />
                                            <button
                                                onClick={async () => {
                                                    try {
                                                        await axios.put(`/api/categories/${cat.id}`, { name: editCategoryName }, {
                                                            headers: { Authorization: `Bearer ${localStorage.getItem('auth_token')}` }
                                                        });
                                                        setEditingCategoryId(null);
                                                        fetchCategories();
                                                    } catch (err) {
                                                        setMsg({ text: err.response?.data?.message || 'Ошибка', isError: true });
                                                    }
                                                }}
                                                className="px-4 py-2 bg-indigo-600 text-white font-bold rounded-lg"
                                            >
                                                Сохранить
                                            </button>
                                            <button onClick={() => setEditingCategoryId(null)} className="px-4 py-2 text-gray-600 font-bold">
                                                Отмена
                                            </button>
                                        </>
                                    ) : (
                                        <>
                                            <span className="font-bold text-gray-900">{cat.name}</span>
                                            <div className="flex gap-2">
                                                <button
                                                    onClick={() => {
                                                        setEditingCategoryId(cat.id);
                                                        setEditCategoryName(cat.name);
                                                    }}
                                                    className="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg"
                                                >
                                                    <Edit className="w-4 h-4" />
                                                </button>
                                                <button
                                                    onClick={async () => {
                                                        if (!window.confirm(`Удалить категорию "${cat.name}"?`)) return;
                                                        try {
                                                            await axios.delete(`/api/categories/${cat.id}`, {
                                                                headers: { Authorization: `Bearer ${localStorage.getItem('auth_token')}` }
                                                            });
                                                            fetchCategories();
                                                        } catch (err) {
                                                            setMsg({ text: err.response?.data?.message || 'Ошибка удаления', isError: true });
                                                        }
                                                    }}
                                                    className="p-2 text-red-600 hover:bg-red-50 rounded-lg"
                                                >
                                                    <Trash2 className="w-4 h-4" />
                                                </button>
                                            </div>
                                        </>
                                    )}
                                </div>
                            ))}
                            {categories.length === 0 && (
                                <p className="text-gray-500 text-center py-8">писька</p>
                            )}
                        </div>
                    </div>
                )}

                {activeTab === 'users' && (
                    <div className="max-w-5xl bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                        <div className="p-8 border-b border-gray-100 flex justify-between items-center">
                            <h2 className="text-3xl font-black text-gray-900">Пользователи</h2>
                            <span className="bg-indigo-50 text-indigo-600 px-4 py-2 rounded-full font-bold text-sm">Система управления</span>
                        </div>
                        <div className="overflow-x-auto p-8">
                            <table className="w-full text-left">
                                <thead>
                                <tr className="text-gray-400 border-b border-gray-100">
                                    <th className="pb-4 font-bold pl-4 uppercase text-xs tracking-wider">ID</th>
                                    <th className="pb-4 font-bold uppercase text-xs tracking-wider">Имя</th>
                                    <th className="pb-4 font-bold uppercase text-xs tracking-wider">Email</th>
                                    <th className="pb-4 font-bold uppercase text-xs tracking-wider">Роль</th>
                                    <th className="pb-4 font-bold text-right pr-4 uppercase text-xs tracking-wider">Действия</th>
                                </tr>
                                </thead>
                                <tbody>
                                {users.map(user => (
                                    <tr key={user.id} className="border-b border-gray-50 hover:bg-gray-50/50 transition-colors">
                                        <td className="py-5 pl-4 text-gray-500 font-mono">#{user.id}</td>
                                        <td className="py-5">
                                            {editingUserId === user.id ? (
                                                <input
                                                    className="border rounded-lg px-2 py-1 focus:ring-2 ring-indigo-200 outline-none"
                                                    value={editUserData.name}
                                                    onChange={e => setEditUserData({...editUserData, name: e.target.value})}
                                                />
                                            ) : <span className="font-bold text-gray-900">{user.name}</span>}
                                        </td>
                                        <td className="py-5">
                                            {editingUserId === user.id ? (
                                                <input
                                                    className="border rounded-lg px-2 py-1 focus:ring-2 ring-indigo-200 outline-none"
                                                    value={editUserData.email}
                                                    onChange={e => setEditUserData({...editUserData, email: e.target.value})}
                                                />
                                            ) : <span className="text-gray-600">{user.email}</span>}
                                        </td>
                                        <td className="py-5">
                                            {editingUserId === user.id ? (
                                                <select
                                                    className="border rounded-lg px-2 py-1 outline-none"
                                                    value={editUserData.role}
                                                    onChange={e => setEditUserData({...editUserData, role: e.target.value})}
                                                >
                                                    <option value="user">User</option>
                                                    <option value="admin">Admin</option>
                                                </select>
                                            ) : (
                                                <span className={`px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-tighter ${user.role === 'admin' ? 'bg-red-100 text-red-600' : 'bg-green-100 text-green-600'}`}>
                                                        {user.role}
                                                    </span>
                                            )}
                                        </td>
                                        <td className="py-5 text-right pr-4">
                                            <div className="flex justify-end gap-2">
                                                {editingUserId === user.id ? (
                                                    <>
                                                        <button onClick={() => handleSaveUser(user.id)} className="bg-indigo-600 text-white px-3 py-1 rounded-lg text-xs font-bold">ОК</button>
                                                        <button onClick={() => setEditingUserId(null)} className="bg-gray-200 text-gray-700 px-3 py-1 rounded-lg text-xs font-bold">X</button>
                                                    </>
                                                ) : (
                                                    <>
                                                        <button onClick={() => handleEditUser(user)} className="p-2 text-indigo-400 hover:bg-indigo-50 rounded-full transition"><Edit className="w-4 h-4" /></button>
                                                        <button onClick={() => handleDeleteUser(user.id)} className="p-2 text-red-400 hover:bg-red-50 rounded-full transition"><Trash2 className="w-4 h-4" /></button>
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
