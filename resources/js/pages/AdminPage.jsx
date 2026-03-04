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
    const [loading, setLoading] = useState(false);

    const [formData, setFormData] = useState({ name: '', description: '', price: '', quantity: '1', category_id: '' });
    const [productImages, setProductImages] = useState([]);
    const [productPreviews, setProductPreviews] = useState([]);

    const [categoryName, setCategoryName] = useState('');
    const [categoryImage, setCategoryImage] = useState(null);
    const [categoryPreview, setCategoryPreview] = useState(null);

    const fetchAll = () => {
        setLoading(true);
        api.get('/categories').then(res => {
            const data = res.data.data || res.data;
            setCategories(data);
            if (data.length > 0 && !formData.category_id) {
                setFormData(prev => ({ ...prev, category_id: data[0].id }));
            }
        }).catch(err => console.error("Category load error", err));

        if (activeTab === 'users') {
            api.get('/users').then(res => setUsers(res.data.data || res.data)).finally(() => setLoading(false));
        } else if (activeTab === 'manageProducts') {
            api.get('/products').then(res => setProducts(res.data.data || res.data)).finally(() => setLoading(false));
        } else {
            setLoading(false);
        }
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
            setMsg({ text: 'Товар создан!', isError: false });
            setFormData({ name: '', description: '', price: '', quantity: '1', category_id: categories[0]?.id || '' });
            setProductImages([]); setProductPreviews([]);
            fetchAll();
        } catch (err) { setMsg({ text: err.response?.data?.message || 'Ошибка', isError: true }); }
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
        } catch (err) { setMsg({ text: 'Ошибка создания', isError: true }); }
    };

    return (
        <div className="min-h-screen flex">
            {/* Sidebar */}
            <div className="w-64 glass-card border-r-0 rounded-none p-6 m-4 rounded-3xl hidden lg:block">
                <h2 className="text-xl font-black mb-8 flex items-center gap-2 text-indigo-600 uppercase">
                    <LayoutDashboard size={24} /> Админка
                </h2>
                <div className="space-y-2">
                    <button onClick={() => setActiveTab('addProduct')} className={`w-full flex items-center gap-3 p-3 rounded-2xl font-bold transition-all ${activeTab === 'addProduct' ? 'bg-indigo-600 text-white shadow-lg' : 'text-gray-500 hover:bg-white'}`}><PlusCircle size={20}/> Новый товар</button>
                    <button onClick={() => setActiveTab('manageProducts')} className={`w-full flex items-center gap-3 p-3 rounded-2xl font-bold transition-all ${activeTab === 'manageProducts' ? 'bg-indigo-600 text-white shadow-lg' : 'text-gray-500 hover:bg-white'}`}><Package size={20}/> Все товары</button>
                    <button onClick={() => setActiveTab('categories')} className={`w-full flex items-center gap-3 p-3 rounded-2xl font-bold transition-all ${activeTab === 'categories' ? 'bg-indigo-600 text-white shadow-lg' : 'text-gray-500 hover:bg-white'}`}><FolderTree size={20}/> Категории</button>
                    <button onClick={() => setActiveTab('users')} className={`w-full flex items-center gap-3 p-3 rounded-2xl font-bold transition-all ${activeTab === 'users' ? 'bg-indigo-600 text-white shadow-lg' : 'text-gray-500 hover:bg-white'}`}><Users size={20}/> Пользователи</button>
                </div>
                <Link to="/" className="mt-10 flex items-center gap-2 text-gray-400 font-bold hover:text-indigo-600 px-3 transition-colors"><ArrowLeft size={18}/> В магазин</Link>
            </div>

            <div className="flex-1 p-10 overflow-y-auto">
                {msg.text && (
                    <div className={`mb-6 p-4 rounded-2xl font-bold border flex items-center justify-between ${msg.isError ? 'bg-red-50 text-red-700 border-red-100' : 'bg-green-50 text-green-700 border-green-100'}`}>
                        <span>{msg.text}</span>
                        <X size={18} className="cursor-pointer" onClick={() => setMsg({text:'', isError: false})}/>
                    </div>
                )}

                {/* ТАБ: КАТЕГОРИИ */}
                {activeTab === 'categories' && (
                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 animate-in fade-in duration-500">
                        <div className="glass-card p-8 rounded-[2.5rem]">
                            <h3 className="text-xl font-black mb-6 uppercase">Добавить категорию</h3>
                            <form onSubmit={handleCategorySubmit} className="space-y-6">
                                <input type="text" placeholder="Название" required value={categoryName} onChange={e => setCategoryName(e.target.value)} className="admin-input"/>
                                <div className="flex items-center gap-6">
                                    <div className="w-20 h-20 rounded-2xl bg-white/50 flex items-center justify-center overflow-hidden shrink-0 border border-slate-200 dark:border-slate-700">
                                        {categoryPreview ? <img src={categoryPreview} className="w-full h-full object-cover" /> : <ImageIcon className="text-gray-400" />}
                                    </div>
                                    <div className="relative flex-1">
                                        <button type="button" className="w-full py-3 bg-indigo-50 text-indigo-600 font-bold rounded-xl border border-indigo-100">Выбрать фото</button>
                                        <input type="file" onChange={(e) => {
                                            setCategoryImage(e.target.files[0]);
                                            setCategoryPreview(URL.createObjectURL(e.target.files[0]));
                                        }} className="absolute inset-0 opacity-0 cursor-pointer" accept="image/*" />
                                    </div>
                                </div>
                                <button className="w-full bg-indigo-600 text-white font-black py-4 rounded-2xl shadow-lg">СОЗДАТЬ</button>
                            </form>
                        </div>
                        <div className="glass-card p-8 rounded-[2.5rem]">
                            <h3 className="text-xl font-black mb-6 uppercase">Существующие</h3>
                            <div className="space-y-3 max-h-[500px] overflow-y-auto pr-2">
                                {categories.map(c => (
                                    <div key={c.id} className="flex justify-between items-center p-4 bg-white/40 rounded-2xl border border-slate-200 dark:border-slate-700">
                                        <span className="font-bold">{c.name}</span>
                                        <button onClick={() => api.delete(`/categories/${c.id}`).then(fetchAll)} className="text-red-500 hover:bg-red-50 p-2 rounded-lg transition-colors"><Trash2 size={18}/></button>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>
                )}

                {/* ТАБ: ВСЕ ТОВАРЫ */}
                {activeTab === 'manageProducts' && (
                    <div className="glass-card rounded-[2.5rem] overflow-hidden animate-in fade-in duration-500">
                        <div className="p-6 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center">
                            <h3 className="text-xl font-black uppercase">Управление товарами</h3>
                            <button onClick={fetchAll} className="p-2 text-indigo-600 border border-slate-200 dark:border-slate-700 rounded-xl hover:bg-white/50"><RefreshCw size={20} /></button>
                        </div>
                        <div className="overflow-x-auto">
                            <table className="w-full text-left">
                                <thead className="bg-slate-50/50 dark:bg-slate-800/50 text-[10px] font-black uppercase text-gray-400 tracking-widest border-b border-slate-200 dark:border-slate-700">
                                <tr><th className="p-6">Товар</th><th className="p-6">Цена</th><th className="p-6 text-right">Действие</th></tr>
                                </thead>
                                <tbody className="divide-y divide-slate-200 dark:divide-slate-700">
                                {products.map(p => (
                                    <tr key={p.id} className="hover:bg-white/40 transition-colors">
                                        <td className="p-6 font-bold flex items-center gap-4">
                                            <div className="w-12 h-12 rounded-xl bg-white overflow-hidden shadow-sm shrink-0 border border-slate-100">
                                                {p.images?.[0] && <img src={p.images[0].url} className="w-full h-full object-cover"/>}
                                            </div>
                                            {p.name}
                                        </td>
                                        <td className="p-6 font-mono text-indigo-600 font-black">{p.price} BYN</td>
                                        <td className="p-6 text-right">
                                            <button onClick={() => api.delete(`/products/${p.id}`).then(fetchAll)} className="p-2 text-red-500 border border-red-100 rounded-xl hover:bg-red-50"><Trash2 size={20}/></button>
                                        </td>
                                    </tr>
                                ))}
                                </tbody>
                            </table>
                        </div>
                    </div>
                )}

                {/* ТАБ: НОВЫЙ ТОВАР */}
                {activeTab === 'addProduct' && (
                    <div className="max-w-4xl glass-card p-10 rounded-[2.5rem] animate-in slide-in-from-bottom-4 duration-500">
                        <h3 className="text-3xl font-black mb-8 tracking-tighter uppercase text-center lg:text-left">Новый продукт</h3>
                        <form onSubmit={handleProductSubmit} className="grid grid-cols-1 md:grid-cols-2 gap-10">
                            <div className="space-y-5">
                                <input type="text" placeholder="Название" required value={formData.name} onChange={e => setFormData({...formData, name: e.target.value})} className="admin-input"/>
                                <div className="grid grid-cols-2 gap-4">
                                    <input type="number" step="0.01" placeholder="Цена" required value={formData.price} onChange={e => setFormData({...formData, price: e.target.value})} className="admin-input"/>
                                    <input type="number" placeholder="Запас" required value={formData.quantity} onChange={e => setFormData({...formData, quantity: e.target.value})} className="admin-input"/>
                                </div>
                                <select value={formData.category_id} onChange={e => setFormData({...formData, category_id: e.target.value})} className="admin-input appearance-none">
                                    {categories.map(c => <option key={c.id} value={c.id}>{c.name}</option>)}
                                </select>
                                <textarea placeholder="Описание продукта..." required value={formData.description} onChange={e => setFormData({...formData, description: e.target.value})} className="admin-input h-44"/>
                            </div>
                            <div className="space-y-6">
                                <div className="relative group border-2 border-dashed border-indigo-200 hover:border-indigo-500 rounded-[2rem] p-12 transition-all flex flex-col items-center justify-center bg-indigo-50/30">
                                    <Upload className="text-indigo-400 mb-3" size={40}/>
                                    <p className="text-xs font-bold text-indigo-600 text-center">Перетащите или кликните</p>
                                    <input type="file" multiple onChange={handleProductFiles} className="absolute inset-0 opacity-0 cursor-pointer"/>
                                </div>
                                <div className="grid grid-cols-3 gap-3">
                                    {productPreviews.map((url, idx) => (
                                        <img key={idx} src={url} className="aspect-square rounded-2xl object-cover shadow-sm border border-slate-200 dark:border-slate-700" />
                                    ))}
                                </div>
                                <button className="w-full bg-indigo-600 text-white font-black py-5 rounded-[1.5rem] shadow-xl uppercase tracking-widest hover:bg-indigo-700 active:scale-95 transition-all">Опубликовать</button>
                            </div>
                        </form>
                    </div>
                )}
            </div>
        </div>
    );
}
