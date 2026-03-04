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

    const [formData, setFormData] = useState({ name: '', description: '', price: '', quantity: '1', category_id: '' });
    const [productImages, setProductImages] = useState([]);
    const [productPreviews, setProductPreviews] = useState([]);

    const [categoryName, setCategoryName] = useState('');
    const [categoryImage, setCategoryImage] = useState(null);
    const [categoryPreview, setCategoryPreview] = useState(null);

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

    const handleProductFiles = (e) => {
        const files = Array.from(e.target.files);
        setProductImages(prev => [...prev, ...files]);
        const newPreviews = files.map(file => URL.createObjectURL(file));
        setProductPreviews(prev => [...prev, ...newPreviews]);
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
            setProductImages([]); setProductPreviews([]);
        } catch (err) { setMsg({ text: err.response?.data?.message || 'Ошибка создания', isError: true }); }
    };

    const handleCategorySubmit = async (e) => {
        e.preventDefault();
        const form = new FormData();
        form.append('name', categoryName);
        if (categoryImage) form.append('image', categoryImage);
        try {
            await api.post('/categories', form);
            setMsg({ text: 'Категория добавлена', isError: false });
            setCategoryName(''); setCategoryImage(null); setCategoryPreview(null);
            fetchAll();
        } catch (err) { setMsg({ text: 'Ошибка (проверьте уникальность названия)', isError: true }); }
    };

    return (
        /* Изменено: Убран bg-gray-50, теперь виден основной фон сайта */
        <div className="min-h-screen flex">
            {/* Sidebar остается белым для контраста */}
            <div className="w-64 bg-white dark:bg-gray-800 border-r dark:border-gray-700 p-4">
                <h2 className="text-xl font-black mb-8 dark:text-white uppercase flex items-center gap-2">
                    <LayoutDashboard size={24} className="text-indigo-600" /> Админка
                </h2>
                <div className="space-y-2">
                    {['addProduct', 'manageProducts', 'categories', 'users'].map((tab) => (
                        <button key={tab} onClick={() => setActiveTab(tab)} className={`w-full text-left p-3 rounded-xl font-bold transition-all ${activeTab === tab ? 'bg-indigo-600 text-white shadow-lg' : 'text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700'}`}>
                            {tab === 'addProduct' && 'Создать товар'}
                            {tab === 'manageProducts' && 'Все товары'}
                            {tab === 'categories' && 'Категории'}
                            {tab === 'users' && 'Пользователи'}
                        </button>
                    ))}
                </div>
            </div>

            {/* Основной контент - прозрачный для пропуска фона body */}
            <div className="flex-1 p-10 overflow-y-auto">
                {msg.text && (
                    <div className={`mb-6 p-4 rounded-xl font-bold border flex items-center justify-between animate-in fade-in slide-in-from-top-2 ${msg.isError ? 'bg-red-50 text-red-700 border-red-100' : 'bg-green-50 text-green-700 border-green-100'}`}>
                        <span>{msg.text}</span>
                        <X size={18} className="cursor-pointer" onClick={() => setMsg({text:'', isError: false})}/>
                    </div>
                )}

                {activeTab === 'addProduct' && (
                    <div className="max-w-4xl bg-white/80 backdrop-blur-md dark:bg-gray-800/80 p-8 rounded-3xl shadow-xl border border-white/20">
                        <h3 className="text-2xl font-black mb-6 dark:text-white">Новый товар</h3>
                        {/* Форма здесь... */}
                        <form onSubmit={handleProductSubmit} className="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div className="space-y-4">
                                <input type="text" placeholder="Название" required value={formData.name} onChange={e => setFormData({...formData, name: e.target.value})} className="w-full p-4 border rounded-2xl dark:bg-gray-700 dark:text-white outline-none focus:ring-2 focus:ring-indigo-500"/>
                                <div className="grid grid-cols-2 gap-4">
                                    <input type="number" step="0.01" placeholder="Цена" required value={formData.price} onChange={e => setFormData({...formData, price: e.target.value})} className="w-full p-4 border rounded-2xl dark:bg-gray-700 outline-none"/>
                                    <input type="number" placeholder="Склад" required value={formData.quantity} onChange={e => setFormData({...formData, quantity: e.target.value})} className="w-full p-4 border rounded-2xl dark:bg-gray-700 outline-none"/>
                                </div>
                                <select value={formData.category_id} onChange={e => setFormData({...formData, category_id: e.target.value})} className="w-full p-4 border rounded-2xl dark:bg-gray-700 outline-none">
                                    {categories.map(c => <option key={c.id} value={c.id}>{c.name}</option>)}
                                </select>
                                <textarea placeholder="Описание..." required value={formData.description} onChange={e => setFormData({...formData, description: e.target.value})} className="w-full p-4 border rounded-2xl dark:bg-gray-700 h-40 outline-none"/>
                            </div>
                            <div className="space-y-4">
                                <div className="relative border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-3xl p-10 flex flex-col items-center justify-center bg-white/50">
                                    <Upload className="text-gray-400 mb-2" size={32}/>
                                    <p className="text-sm font-bold text-gray-500">Загрузить фото</p>
                                    <input type="file" multiple onChange={handleProductFiles} className="absolute inset-0 opacity-0 cursor-pointer" accept="image/*"/>
                                </div>
                                <div className="grid grid-cols-3 gap-2">
                                    {productPreviews.map((url, idx) => <img key={idx} src={url} className="aspect-square object-cover rounded-xl border" />)}
                                </div>
                                <button className="w-full bg-indigo-600 text-white font-black py-4 rounded-2xl shadow-lg active:scale-95 transition-transform">ОПУБЛИКОВАТЬ</button>
                            </div>
                        </form>
                    </div>
                )}

                {/* Другие табы аналогично используют bg-white/80 для "стеклянного" эффекта на голубом фоне */}
                {activeTab === 'categories' && (
                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <div className="bg-white/80 backdrop-blur-md dark:bg-gray-800/80 p-8 rounded-3xl shadow-xl">
                            <h3 className="text-xl font-black mb-6 dark:text-white uppercase">Добавить категорию</h3>
                            <form onSubmit={handleCategorySubmit} className="space-y-6">
                                <input type="text" placeholder="Название" required value={categoryName} onChange={e => setCategoryName(e.target.value)} className="w-full p-4 border rounded-2xl dark:bg-gray-700 outline-none"/>
                                <div className="flex items-center gap-6">
                                    <div className="w-20 h-20 rounded-2xl bg-gray-100 flex items-center justify-center overflow-hidden">
                                        {categoryPreview ? <img src={categoryPreview} className="w-full h-full object-cover" /> : <ImageIcon className="text-gray-400" />}
                                    </div>
                                    <input type="file" onChange={(e) => {
                                        setCategoryImage(e.target.files[0]);
                                        setCategoryPreview(URL.createObjectURL(e.target.files[0]));
                                    }} className="text-xs" accept="image/*" />
                                </div>
                                <button className="w-full bg-indigo-600 text-white font-black py-4 rounded-2xl shadow-lg">СОЗДАТЬ</button>
                            </form>
                        </div>
                        <div className="bg-white/80 backdrop-blur-md p-8 rounded-3xl shadow-xl">
                            <h3 className="text-xl font-black mb-6 dark:text-white uppercase">Существующие</h3>
                            <div className="space-y-3">
                                {categories.map(c => (
                                    <div key={c.id} className="flex justify-between items-center p-4 bg-white rounded-2xl border">
                                        <span className="font-bold">{c.name}</span>
                                        <button onClick={() => api.delete(`/categories/${c.id}`).then(fetchAll)} className="text-red-500"><Trash2 size={18}/></button>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>
                )}
            </div>
        </div>
    );
}
