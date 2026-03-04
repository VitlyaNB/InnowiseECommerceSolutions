import React, { useState, useEffect } from 'react';
import api from '../api';
import { Link } from 'react-router-dom';
import {
    ArrowLeft, PlusCircle, Users, LayoutDashboard,
    Trash2, Edit, FolderTree, RefreshCw, X, Check,
    Package, Upload, Image as ImageIcon
} from 'lucide-react';

export default function AdminPage() {
    const [activeTab, setActiveTab] = useState('addProduct');
    const [users, setUsers] = useState([]);
    const [categories, setCategories] = useState([]);
    const [products, setProducts] = useState([]);
    const [msg, setMsg] = useState({ text: '', isError: false });

    // Состояния для товара
    const [formData, setFormData] = useState({
        name: '', description: '', price: '', quantity: '1', category_id: ''
    });
    const [productImages, setProductImages] = useState([]);
    const [productPreviews, setProductPreviews] = useState([]);

    // Состояния для категории
    const [categoryName, setCategoryName] = useState('');
    const [categoryImage, setCategoryImage] = useState(null);
    const [categoryPreview, setCategoryPreview] = useState(null);

    // Состояния для пользователей
    const [editingUserId, setEditingUserId] = useState(null);
    const [editUserData, setEditUserData] = useState({ name: '', email: '', role: 'user', balance: 0 });

    const fetchAll = () => {
        api.get('/categories').then(res => {
            const fetched = res.data.data || res.data;
            setCategories(fetched);
            if (fetched.length > 0 && !formData.category_id) {
                setFormData(prev => ({ ...prev, category_id: fetched[0].id }));
            }
        }).catch(console.error);

        if (activeTab === 'users') api.get('/users').then(res => setUsers(res.data.data || res.data)).catch(console.error);
        if (activeTab === 'manageProducts') api.get('/products').then(res => setProducts(res.data.data || res.data)).catch(console.error);
    };

    useEffect(() => { fetchAll(); }, [activeTab]);

    // --- Обработка фото товара ---
    const handleProductFiles = (e) => {
        const files = Array.from(e.target.files);
        setProductImages(prev => [...prev, ...files]);

        const newPreviews = files.map(file => URL.createObjectURL(file));
        setProductPreviews(prev => [...prev, ...newPreviews]);
    };

    const removeProductFile = (index) => {
        setProductImages(prev => prev.filter((_, i) => i !== index));
        setProductPreviews(prev => prev.filter((_, i) => i !== index));
    };

    const handleProductSubmit = async (e) => {
        e.preventDefault();
        const form = new FormData();
        Object.keys(formData).forEach(key => form.append(key, formData[key]));
        productImages.forEach(file => form.append('images[]', file));

        try {
            await api.post('/products', form);
            setMsg({ text: 'Товар успешно создан!', isError: false });
            setFormData({ name: '', description: '', price: '', quantity: '1', category_id: categories[0]?.id || '' });
            setProductImages([]);
            setProductPreviews([]);
        } catch (err) {
            setMsg({ text: err.response?.data?.message || 'Ошибка создания', isError: true });
        }
    };

    // --- Обработка фото категории ---
    const handleCategoryFile = (e) => {
        const file = e.target.files[0];
        if (file) {
            setCategoryImage(file);
            setCategoryPreview(URL.createObjectURL(file));
        }
    };

    const handleCategorySubmit = async (e) => {
        e.preventDefault();
        if (!categoryName.trim()) {
            setMsg({ text: 'Название категории обязательно', isError: true });
            return;
        }

        const form = new FormData();
        form.append('name', categoryName);
        if (categoryImage) form.append('image', categoryImage);

        try {
            await api.post('/categories', form);
            setMsg({ text: 'Категория добавлена', isError: false });
            setCategoryName('');
            setCategoryImage(null);
            setCategoryPreview(null);
            fetchAll();
        } catch (err) {
            // Здесь мы ловим 422 и выводим ошибки валидации
            const errors = err.response?.data?.errors;
            const firstError = errors ? Object.values(errors)[0][0] : 'Ошибка при добавлении';
            setMsg({ text: firstError, isError: true });
        }
    };

    const handleDeleteProduct = async (id) => {
        if (!window.confirm("Удалить товар?")) return;
        try {
            await api.delete(`/products/${id}`);
            setMsg({ text: 'Товар удален', isError: false });
            fetchAll();
        } catch (err) { setMsg({ text: 'Ошибка удаления', isError: true }); }
    };

    const handleDeleteCategory = async (id) => {
        if (!window.confirm("Удалить категорию?")) return;
        try {
            await api.delete(`/categories/${id}`);
            setMsg({ text: 'Категория удалена', isError: false });
            fetchAll();
        } catch (err) { setMsg({ text: 'Ошибка удаления', isError: true }); }
    };

    // --- Юзеры ---
    const handleEditUser = (u) => {
        setEditingUserId(u.id);
        setEditUserData({ name: u.name, email: u.email, role: u.role, balance: u.balance || 0 });
    };

    const handleSaveUser = async (id) => {
        try {
            await api.put(`/users/${id}`, editUserData);
            setEditingUserId(null);
            setMsg({ text: 'Обновлено', isError: false });
            fetchAll();
        } catch (err) { setMsg({ text: 'Ошибка', isError: true }); }
    };

    const handleDeleteUser = async (id) => {
        if (!window.confirm("Удалить пользователя?")) return;
        try {
            await api.delete(`/users/${id}`);
            fetchAll();
        } catch (err) { setMsg({ text: 'Ошибка', isError: true }); }
    };

    return (
        <div className="min-h-screen bg-gray-50 dark:bg-gray-900 flex">
            {/* Sidebar */}
            <div className="w-64 bg-white dark:bg-gray-800 border-r dark:border-gray-700 p-4">
                <h2 className="text-xl font-black mb-8 dark:text-white uppercase flex items-center gap-2">
                    <LayoutDashboard size={24} className="text-indigo-600" /> Админка
                </h2>
                <div className="space-y-2">
                    <button onClick={() => setActiveTab('addProduct')} className={`w-full flex items-center gap-3 p-3 rounded-xl font-bold transition-colors ${activeTab === 'addProduct' ? 'bg-indigo-600 text-white' : 'text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700'}`}><PlusCircle size={20}/> Создать товар</button>
                    <button onClick={() => setActiveTab('manageProducts')} className={`w-full flex items-center gap-3 p-3 rounded-xl font-bold transition-colors ${activeTab === 'manageProducts' ? 'bg-indigo-600 text-white' : 'text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700'}`}><Package size={20}/> Все товары</button>
                    <button onClick={() => setActiveTab('categories')} className={`w-full flex items-center gap-3 p-3 rounded-xl font-bold transition-colors ${activeTab === 'categories' ? 'bg-indigo-600 text-white' : 'text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700'}`}><FolderTree size={20}/> Категории</button>
                    <button onClick={() => setActiveTab('users')} className={`w-full flex items-center gap-3 p-3 rounded-xl font-bold transition-colors ${activeTab === 'users' ? 'bg-indigo-600 text-white' : 'text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700'}`}><Users size={20}/> Пользователи</button>
                </div>
                <Link to="/" className="mt-10 block p-3 text-gray-400 font-bold hover:text-indigo-600 transition-colors flex items-center gap-2"><ArrowLeft size={18}/> В магазин</Link>
            </div>

            {/* Content Area */}
            <div className="flex-1 p-10 overflow-y-auto">
                {msg.text && (
                    <div className={`mb-6 p-4 rounded-xl font-bold border flex items-center justify-between ${msg.isError ? 'bg-red-50 text-red-700 border-red-100' : 'bg-green-50 text-green-700 border-green-100'}`}>
                        <span>{msg.text}</span>
                        <X size={18} className="cursor-pointer" onClick={() => setMsg({text:'', isError: false})}/>
                    </div>
                )}

                {/* ТАБ: ДОБАВЛЕНИЕ ТОВАРА */}
                {activeTab === 'addProduct' && (
                    <div className="max-w-4xl bg-white dark:bg-gray-800 p-8 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700">
                        <h3 className="text-2xl font-black mb-6 dark:text-white uppercase tracking-tight">Новый товар</h3>
                        <form onSubmit={handleProductSubmit} className="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div className="space-y-4">
                                <input type="text" placeholder="Название товара" required value={formData.name} onChange={e => setFormData({...formData, name: e.target.value})} className="w-full p-4 border rounded-2xl dark:bg-gray-700 dark:text-white dark:border-gray-600 focus:ring-2 focus:ring-indigo-500 outline-none"/>
                                <div className="grid grid-cols-2 gap-4">
                                    <input type="number" step="0.01" placeholder="Цена (BYN)" required value={formData.price} onChange={e => setFormData({...formData, price: e.target.value})} className="w-full p-4 border rounded-2xl dark:bg-gray-700 dark:text-white dark:border-gray-600 outline-none"/>
                                    <input type="number" placeholder="На складе" required value={formData.quantity} onChange={e => setFormData({...formData, quantity: e.target.value})} className="w-full p-4 border rounded-2xl dark:bg-gray-700 dark:text-white dark:border-gray-600 outline-none"/>
                                </div>
                                <select value={formData.category_id} onChange={e => setFormData({...formData, category_id: e.target.value})} className="w-full p-4 border rounded-2xl dark:bg-gray-700 dark:text-white dark:border-gray-600 outline-none">
                                    <option value="">Выберите категорию</option>
                                    {categories.map(c => <option key={c.id} value={c.id}>{c.name}</option>)}
                                </select>
                                <textarea placeholder="Описание товара..." required value={formData.description} onChange={e => setFormData({...formData, description: e.target.value})} className="w-full p-4 border rounded-2xl dark:bg-gray-700 dark:text-white dark:border-gray-600 h-40 outline-none"/>
                            </div>

                            <div className="space-y-4">
                                <label className="block text-sm font-black text-gray-500 uppercase tracking-widest text-center">Фотографии</label>
                                <div className="relative border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-3xl p-8 hover:border-indigo-500 transition-colors flex flex-col items-center justify-center bg-gray-50 dark:bg-gray-900/50">
                                    <Upload size={32} className="text-gray-400 mb-2" />
                                    <p className="text-xs text-gray-500 font-bold">Кликните для выбора фото</p>
                                    <input type="file" multiple onChange={handleProductFiles} className="absolute inset-0 opacity-0 cursor-pointer" accept="image/*"/>
                                </div>

                                <div className="grid grid-cols-3 gap-3">
                                    {productPreviews.map((url, idx) => (
                                        <div key={idx} className="relative aspect-square rounded-xl overflow-hidden border dark:border-gray-700 group">
                                            <img src={url} className="w-full h-full object-cover" alt="preview" />
                                            <button type="button" onClick={() => removeProductFile(idx)} className="absolute top-1 right-1 p-1 bg-red-500 text-white rounded-full opacity-0 group-hover:opacity-100 transition-opacity">
                                                <X size={12}/>
                                            </button>
                                        </div>
                                    ))}
                                </div>
                                <button className="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-black py-4 rounded-2xl shadow-xl transition-all active:scale-95">ОПУБЛИКОВАТЬ ТОВАР</button>
                            </div>
                        </form>
                    </div>
                )}

                {/* ТАБ: КАТЕГОРИИ */}
                {activeTab === 'categories' && (
                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        {/* Форма добавления */}
                        <div className="bg-white dark:bg-gray-800 p-8 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700">
                            <h3 className="text-xl font-black mb-6 dark:text-white uppercase tracking-tighter">Новая категория</h3>
                            <form onSubmit={handleCategorySubmit} className="space-y-6">
                                <input type="text" placeholder="Название (например: Электроника)" required value={categoryName} onChange={e => setCategoryName(e.target.value)} className="w-full p-4 border rounded-2xl dark:bg-gray-700 dark:text-white outline-none"/>

                                <div className="flex items-center gap-6">
                                    <div className="w-20 h-20 rounded-2xl bg-gray-100 dark:bg-gray-900 flex items-center justify-center border dark:border-gray-700 overflow-hidden shrink-0">
                                        {categoryPreview ? <img src={categoryPreview} className="w-full h-full object-cover" alt="icon" /> : <ImageIcon className="text-gray-400" />}
                                    </div>
                                    <div className="flex-1 relative">
                                        <button type="button" className="w-full py-3 bg-gray-100 dark:bg-gray-700 dark:text-white font-bold rounded-xl flex items-center justify-center gap-2 border border-gray-200 dark:border-gray-600">
                                            <Upload size={18}/> Выбрать иконку
                                        </button>
                                        <input type="file" onChange={handleCategoryFile} className="absolute inset-0 opacity-0 cursor-pointer" accept="image/*"/>
                                    </div>
                                </div>
                                <button className="w-full bg-indigo-600 text-white font-black py-4 rounded-2xl shadow-lg hover:bg-indigo-700 transition-colors">СОЗДАТЬ КАТЕГОРИЮ</button>
                            </form>
                        </div>

                        {/* Список существующих */}
                        <div className="bg-white dark:bg-gray-800 p-8 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700">
                            <h3 className="text-xl font-black mb-6 dark:text-white uppercase tracking-tighter">Существующие</h3>
                            <div className="space-y-3 max-h-[400px] overflow-y-auto pr-2">
                                {categories.map(c => (
                                    <div key={c.id} className="flex justify-between items-center p-4 bg-gray-50 dark:bg-gray-900 rounded-2xl border dark:border-gray-700">
                                        <div className="flex items-center gap-3">
                                            <div className="w-10 h-10 rounded-lg bg-white dark:bg-gray-800 flex items-center justify-center border dark:border-gray-700 overflow-hidden shrink-0">
                                                {c.image_path ? <img src={c.image_path} className="w-full h-full object-cover" alt="cat" /> : <FolderTree size={16} className="text-indigo-400"/>}
                                            </div>
                                            <span className="font-bold dark:text-white">{c.name}</span>
                                        </div>
                                        <button onClick={() => handleDeleteCategory(c.id)} className="p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-xl transition-colors"><Trash2 size={18}/></button>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>
                )}

                {/* ТАБ: ВСЕ ТОВАРЫ */}
                {activeTab === 'manageProducts' && (
                    <div className="bg-white dark:bg-gray-800 rounded-3xl overflow-hidden shadow-sm border border-gray-100 dark:border-gray-700">
                        <table className="w-full text-left">
                            <thead className="bg-gray-50 dark:bg-gray-700/50 text-[10px] font-black uppercase text-gray-400 tracking-widest">
                            <tr><th className="px-8 py-5">Товар</th><th className="px-8 py-5">Категория</th><th className="px-8 py-5">Цена</th><th className="px-8 py-5 text-right">Управление</th></tr>
                            </thead>
                            <tbody className="divide-y dark:divide-gray-700">
                            {products.map(p => (
                                <tr key={p.id} className="hover:bg-gray-50/50 dark:hover:bg-gray-900/30 transition-colors">
                                    <td className="px-8 py-5">
                                        <div className="flex items-center gap-3">
                                            <div className="w-12 h-12 rounded-xl bg-gray-100 dark:bg-gray-900 overflow-hidden border dark:border-gray-700 shrink-0">
                                                {p.images?.[0] ? <img src={p.images[0].url} className="w-full h-full object-cover" alt="p" /> : <ImageIcon size={18} className="text-gray-300 mx-auto mt-3" />}
                                            </div>
                                            <span className="font-black dark:text-white">{p.name}</span>
                                        </div>
                                    </td>
                                    <td className="px-8 py-5 text-gray-500 font-bold">{p.category?.name || '---'}</td>
                                    <td className="px-8 py-5 font-mono font-black text-indigo-600">{p.price} BYN</td>
                                    <td className="px-8 py-5 text-right">
                                        <button onClick={() => handleDeleteProduct(p.id)} className="p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-xl transition-colors"><Trash2 size={20}/></button>
                                    </td>
                                </tr>
                            ))}
                            </tbody>
                        </table>
                    </div>
                )}

                {/* ТАБ: ПОЛЬЗОВАТЕЛИ */}
                {activeTab === 'users' && (
                    <div className="bg-white dark:bg-gray-800 rounded-3xl overflow-hidden shadow-sm border border-gray-100 dark:border-gray-700">
                        <table className="w-full text-left">
                            <thead className="bg-gray-50 dark:bg-gray-700/50 text-[10px] font-black uppercase text-gray-400 tracking-widest">
                            <tr><th className="p-8">Имя / Email</th><th className="p-8">Роль</th><th className="p-8">Баланс</th><th className="p-8 text-right">Действия</th></tr>
                            </thead>
                            <tbody className="divide-y dark:divide-gray-700">
                            {users.map(u => (
                                <tr key={u.id} className="hover:bg-gray-50/50 dark:hover:bg-gray-900/30 transition-colors">
                                    <td className="p-8">
                                        {editingUserId === u.id ? <input className="w-full p-2 border rounded-xl dark:bg-gray-700 dark:text-white" value={editUserData.name} onChange={e => setEditUserData({...editUserData, name: e.target.value})}/> : <div><div className="font-black dark:text-white">{u.name}</div><div className="text-xs text-gray-400 font-bold">{u.email}</div></div>}
                                    </td>
                                    <td className="p-8">
                                        {editingUserId === u.id ? <select className="p-2 border rounded-xl dark:bg-gray-700 dark:text-white" value={editUserData.role} onChange={e => setEditUserData({...editUserData, role: e.target.value})}><option value="user">user</option><option value="admin">admin</option></select> : <span className={`px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest ${u.role === 'admin' ? 'bg-red-100 text-red-600' : 'bg-green-100 text-green-600'}`}>{u.role}</span>}
                                    </td>
                                    <td className="p-8 font-mono font-black dark:text-white text-indigo-600">
                                        {editingUserId === u.id ? <input type="number" className="p-2 border rounded-xl w-32 dark:bg-gray-700" value={editUserData.balance} onChange={e => setEditUserData({...editUserData, balance: e.target.value})}/> : `${u.balance} BYN`}
                                    </td>
                                    <td className="p-8 text-right flex justify-end gap-2">
                                        {editingUserId === u.id ? <button onClick={() => handleSaveUser(u.id)} className="p-2 bg-green-500 text-white rounded-xl"><Check size={20}/></button> : <button onClick={() => handleEditUser(u)} className="p-2 text-indigo-600 hover:bg-indigo-50 rounded-xl transition-colors"><Edit size={20}/></button>}
                                        <button onClick={() => handleDeleteUser(u.id)} className="p-2 text-red-500 hover:bg-red-50 rounded-xl transition-colors"><Trash2 size={20}/></button>
                                    </td>
                                </tr>
                            ))}
                            </tbody>
                        </table>
                    </div>
                )}
            </div>
        </div>
    );
}
