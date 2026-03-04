import React, { useState, useEffect } from 'react';
import api from '../api';
import { Link } from 'react-router-dom';
import {
    ArrowLeft, PlusCircle, Users, LayoutDashboard,
    Trash2, FolderTree, RefreshCw, X,
    Package, Upload, Image as ImageIcon, ShieldCheck
} from 'lucide-react';

export default function AdminPage() {
    const [activeTab, setActiveTab] = useState('addProduct');
    const [users, setUsers] = useState([]);
    const [categories, setCategories] = useState([]);
    const [products, setProducts] = useState([]);
    const [msg, setMsg] = useState({ text: '', isError: false });
    const [loading, setLoading] = useState(false);

    // Form States
    const [formData, setFormData] = useState({ name: '', description: '', price: '', quantity: '1', category_id: '' });
    const [productImages, setProductImages] = useState([]);
    const [productPreviews, setProductPreviews] = useState([]);
    const [categoryName, setCategoryName] = useState('');
    const [categoryImage, setCategoryImage] = useState(null);
    const [categoryPreview, setCategoryPreview] = useState(null);

    const fetchAll = () => {
        setLoading(true);
        // Грузим категории всегда для селекта
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

    const deleteUser = async (id) => {
        if (!confirm('Вы уверены?')) return;
        try {
            await api.delete(`/users/${id}`);
            fetchAll();
        } catch (err) { alert('Ошибка удаления'); }
    };

    return (
        <div className="min-h-screen flex bg-slate-50 dark:bg-slate-900">
            {/* Sidebar */}
            <div className="w-72 glass-card border-r-0 rounded-none p-6 m-4 rounded-3xl hidden lg:block sticky top-4 h-[calc(100vh-2rem)]">
                <h2 className="text-2xl font-black mb-10 flex items-center gap-2 text-slate-800 dark:text-white uppercase tracking-tighter">
                    <LayoutDashboard className="text-indigo-600" size={28} /> Админка
                </h2>
                <div className="space-y-3">
                    <button onClick={() => setActiveTab('addProduct')} className={`w-full flex items-center gap-3 p-4 rounded-2xl font-bold transition-all ${activeTab === 'addProduct' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30' : 'text-slate-500 hover:bg-white dark:hover:bg-slate-800'}`}><PlusCircle size={20}/> Новый товар</button>
                    <button onClick={() => setActiveTab('manageProducts')} className={`w-full flex items-center gap-3 p-4 rounded-2xl font-bold transition-all ${activeTab === 'manageProducts' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30' : 'text-slate-500 hover:bg-white dark:hover:bg-slate-800'}`}><Package size={20}/> Все товары</button>
                    <button onClick={() => setActiveTab('categories')} className={`w-full flex items-center gap-3 p-4 rounded-2xl font-bold transition-all ${activeTab === 'categories' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30' : 'text-slate-500 hover:bg-white dark:hover:bg-slate-800'}`}><FolderTree size={20}/> Категории</button>
                    <button onClick={() => setActiveTab('users')} className={`w-full flex items-center gap-3 p-4 rounded-2xl font-bold transition-all ${activeTab === 'users' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30' : 'text-slate-500 hover:bg-white dark:hover:bg-slate-800'}`}><Users size={20}/> Пользователи</button>
                </div>
                <Link to="/" className="absolute bottom-8 left-8 flex items-center gap-2 text-slate-400 font-bold hover:text-indigo-600 transition-colors"><ArrowLeft size={18}/> В магазин</Link>
            </div>

            <div className="flex-1 p-4 lg:p-10 overflow-y-auto">
                {msg.text && (
                    <div className={`mb-8 p-4 rounded-2xl font-bold border flex items-center justify-between shadow-sm animate-in slide-in-from-top-2 ${msg.isError ? 'bg-red-50 text-red-700 border-red-100' : 'bg-green-50 text-green-700 border-green-100'}`}>
                        <span>{msg.text}</span>
                        <X size={18} className="cursor-pointer" onClick={() => setMsg({text:'', isError: false})}/>
                    </div>
                )}

                {/* ТАБ: ПОЛЬЗОВАТЕЛИ (РЕАЛИЗОВАНО) */}
                {activeTab === 'users' && (
                    <div className="glass-card rounded-[2.5rem] overflow-hidden animate-in fade-in duration-500 border border-white/20">
                        <div className="p-8 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center bg-white/50 dark:bg-slate-800/50 backdrop-blur-sm">
                            <div>
                                <h3 className="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Пользователи</h3>
                                <p className="text-slate-500 font-medium text-sm mt-1">Управление клиентами и администраторами</p>
                            </div>
                            <button onClick={fetchAll} className="p-3 text-indigo-600 bg-indigo-50 dark:bg-indigo-900/30 rounded-xl hover:scale-105 transition-transform"><RefreshCw size={20} /></button>
                        </div>
                        <div className="overflow-x-auto">
                            <table className="w-full text-left">
                                <thead className="bg-slate-50 dark:bg-slate-900/50 text-xs font-black uppercase text-slate-400 tracking-widest">
                                <tr>
                                    <th className="p-6 pl-8">Пользователь</th>
                                    <th className="p-6">Роль</th>
                                    <th className="p-6">Баланс</th>
                                    <th className="p-6 text-right pr-8">Действия</th>
                                </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-100 dark:divide-slate-700/50">
                                {users.map(u => (
                                    <tr key={u.id} className="hover:bg-indigo-50/30 dark:hover:bg-slate-800/30 transition-colors group">
                                        <td className="p-6 pl-8">
                                            <div className="flex items-center gap-4">
                                                <div className="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 text-white flex items-center justify-center font-black shadow-md">
                                                    {u.name[0].toUpperCase()}
                                                </div>
                                                <div>
                                                    <div className="font-bold text-slate-900 dark:text-white">{u.name}</div>
                                                    <div className="text-xs text-slate-500 font-medium">{u.email}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td className="p-6">
                                            {u.role === 'admin' ? (
                                                <span className="inline-flex items-center gap-1.5 px-3 py-1 bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300 rounded-full text-xs font-black uppercase tracking-wider">
                                                    <ShieldCheck size={12}/> Admin
                                                </span>
                                            ) : (
                                                <span className="inline-flex items-center gap-1.5 px-3 py-1 bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 rounded-full text-xs font-bold uppercase tracking-wider">
                                                    User
                                                </span>
                                            )}
                                        </td>
                                        <td className="p-6 font-mono font-bold text-slate-700 dark:text-slate-300">
                                            {parseFloat(u.balance || 0).toFixed(2)} BYN
                                        </td>
                                        <td className="p-6 text-right pr-8">
                                            <button onClick={() => deleteUser(u.id)} className="p-2 text-red-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-xl transition-all opacity-0 group-hover:opacity-100">
                                                <Trash2 size={18}/>
                                            </button>
                                        </td>
                                    </tr>
                                ))}
                                </tbody>
                            </table>
                        </div>
                    </div>
                )}

                {/* ТАБ: КАТЕГОРИИ */}
                {activeTab === 'categories' && (
                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 animate-in fade-in duration-500">
                        <div className="glass-card p-8 rounded-[2.5rem]">
                            <h3 className="text-xl font-black mb-6 dark:text-white">Новая категория</h3>
                            <form onSubmit={handleCategorySubmit} className="space-y-6">
                                <input type="text" placeholder="Название категории" required value={categoryName} onChange={e => setCategoryName(e.target.value)} className="admin-input"/>
                                <div className="flex items-center gap-6">
                                    <div className="w-24 h-24 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center overflow-hidden shrink-0 border-2 border-dashed border-slate-300 dark:border-slate-600">
                                        {categoryPreview ? <img src={categoryPreview} className="w-full h-full object-cover" /> : <ImageIcon className="text-slate-400" />}
                                    </div>
                                    <div className="relative flex-1">
                                        <div className="absolute inset-0">
                                            <button type="button" className="w-full py-3 bg-white dark:bg-slate-800 text-indigo-600 font-bold rounded-xl border border-indigo-100 dark:border-indigo-900 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 transition-colors">Загрузить обложку</button>
                                        </div>
                                        <input type="file" onChange={(e) => {
                                            setCategoryImage(e.target.files[0]);
                                            setCategoryPreview(URL.createObjectURL(e.target.files[0]));
                                        }} className="absolute inset-0 opacity-0 cursor-pointer z-10" accept="image/*" />
                                    </div>
                                </div>
                                <button className="w-full bg-indigo-600 text-white font-black py-4 rounded-2xl shadow-lg shadow-indigo-500/30 hover:scale-[1.02] active:scale-95 transition-all">СОЗДАТЬ</button>
                            </form>
                        </div>
                        <div className="glass-card p-8 rounded-[2.5rem]">
                            <h3 className="text-xl font-black mb-6 dark:text-white">Список категорий</h3>
                            <div className="space-y-3 max-h-[500px] overflow-y-auto pr-2 custom-scrollbar">
                                {categories.map(c => (
                                    <div key={c.id} className="flex justify-between items-center p-4 bg-white/60 dark:bg-slate-800/60 backdrop-blur-sm rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-md transition-all group">
                                        <div className="flex items-center gap-4">
                                            <div className="w-12 h-12 rounded-xl bg-slate-100 dark:bg-slate-900 overflow-hidden">
                                                {c.image_path && <img src={c.image_path} className="w-full h-full object-cover" />}
                                            </div>
                                            <span className="font-bold text-slate-800 dark:text-slate-200">{c.name}</span>
                                        </div>
                                        <button onClick={() => api.delete(`/categories/${c.id}`).then(fetchAll)} className="text-red-400 hover:text-red-600 p-2 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity"><Trash2 size={18}/></button>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>
                )}

                {/* ТАБ: ВСЕ ТОВАРЫ */}
                {activeTab === 'manageProducts' && (
                    <div className="glass-card rounded-[2.5rem] overflow-hidden animate-in fade-in duration-500 border border-white/20">
                        <div className="p-6 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center bg-white/50 dark:bg-slate-800/50 backdrop-blur-sm">
                            <h3 className="text-xl font-black dark:text-white">Товары</h3>
                            <button onClick={fetchAll} className="p-2 text-indigo-600 bg-indigo-50 dark:bg-indigo-900/30 rounded-xl hover:bg-white transition-all"><RefreshCw size={20} /></button>
                        </div>
                        <div className="overflow-x-auto">
                            <table className="w-full text-left">
                                <thead className="bg-slate-50 dark:bg-slate-900/50 text-[10px] font-black uppercase text-slate-400 tracking-widest border-b border-slate-200 dark:border-slate-700">
                                <tr><th className="p-6 pl-8">Товар</th><th className="p-6">Цена</th><th className="p-6 text-right pr-8">Действие</th></tr>
                                </thead>
                                <tbody className="divide-y divide-slate-100 dark:divide-slate-700/50">
                                {products.map(p => (
                                    <tr key={p.id} className="hover:bg-indigo-50/30 dark:hover:bg-slate-800/30 transition-colors group">
                                        <td className="p-4 pl-8 font-bold flex items-center gap-4 text-slate-900 dark:text-white">
                                            <div className="w-14 h-14 rounded-xl bg-white dark:bg-slate-900 overflow-hidden shadow-sm shrink-0 border border-slate-100 dark:border-slate-700">
                                                {p.images?.[0] && <img src={p.images[0].url} className="w-full h-full object-cover"/>}
                                            </div>
                                            <div>
                                                <div className="line-clamp-1">{p.name}</div>
                                                <div className="text-[10px] text-slate-400 uppercase tracking-wider font-bold mt-1">{p.category?.name || 'Без категории'}</div>
                                            </div>
                                        </td>
                                        <td className="p-4 font-mono text-indigo-600 font-black text-lg">{p.price}</td>
                                        <td className="p-4 text-right pr-8">
                                            <button onClick={() => api.delete(`/products/${p.id}`).then(fetchAll)} className="p-2 text-red-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-xl transition-all opacity-0 group-hover:opacity-100"><Trash2 size={20}/></button>
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
                    <div className="max-w-5xl glass-card p-10 rounded-[2.5rem] animate-in slide-in-from-bottom-4 duration-500 border border-white/20 shadow-2xl">
                        <h3 className="text-3xl font-black mb-10 tracking-tighter uppercase text-center lg:text-left dark:text-white">Создание товара</h3>
                        <form onSubmit={handleProductSubmit} className="grid grid-cols-1 md:grid-cols-2 gap-12">
                            <div className="space-y-6">
                                <div className="space-y-2">
                                    <label className="text-xs font-bold uppercase text-slate-400 tracking-wider ml-2">Название</label>
                                    <input type="text" placeholder="Например: iPhone 16 Pro" required value={formData.name} onChange={e => setFormData({...formData, name: e.target.value})} className="admin-input"/>
                                </div>
                                <div className="grid grid-cols-2 gap-6">
                                    <div className="space-y-2">
                                        <label className="text-xs font-bold uppercase text-slate-400 tracking-wider ml-2">Цена (BYN)</label>
                                        <input type="number" step="0.01" placeholder="0.00" required value={formData.price} onChange={e => setFormData({...formData, price: e.target.value})} className="admin-input font-mono"/>
                                    </div>
                                    <div className="space-y-2">
                                        <label className="text-xs font-bold uppercase text-slate-400 tracking-wider ml-2">Склад</label>
                                        <input type="number" placeholder="1" required value={formData.quantity} onChange={e => setFormData({...formData, quantity: e.target.value})} className="admin-input font-mono"/>
                                    </div>
                                </div>
                                <div className="space-y-2">
                                    <label className="text-xs font-bold uppercase text-slate-400 tracking-wider ml-2">Категория</label>
                                    <select value={formData.category_id} onChange={e => setFormData({...formData, category_id: e.target.value})} className="admin-input appearance-none cursor-pointer">
                                        {categories.map(c => <option key={c.id} value={c.id}>{c.name}</option>)}
                                    </select>
                                </div>
                                <div className="space-y-2">
                                    <label className="text-xs font-bold uppercase text-slate-400 tracking-wider ml-2">Описание</label>
                                    <textarea placeholder="Детальное описание продукта..." required value={formData.description} onChange={e => setFormData({...formData, description: e.target.value})} className="admin-input h-40 resize-none leading-relaxed"/>
                                </div>
                            </div>
                            <div className="space-y-8">
                                <div className="relative group border-3 border-dashed border-indigo-100 hover:border-indigo-400 dark:border-slate-700 dark:hover:border-indigo-500 rounded-[2rem] p-12 transition-all flex flex-col items-center justify-center bg-indigo-50/30 dark:bg-slate-800/30 hover:bg-indigo-50/60 h-64 cursor-pointer">
                                    <Upload className="text-indigo-400 mb-4 group-hover:scale-110 transition-transform duration-300" size={48}/>
                                    <p className="text-sm font-black text-indigo-900 dark:text-indigo-300 uppercase tracking-widest">Загрузить фото</p>
                                    <input type="file" multiple onChange={handleProductFiles} className="absolute inset-0 opacity-0 cursor-pointer"/>
                                </div>

                                {productPreviews.length > 0 && (
                                    <div className="grid grid-cols-4 gap-4">
                                        {productPreviews.map((url, idx) => (
                                            <div key={idx} className="relative group">
                                                <img src={url} className="aspect-square rounded-2xl object-cover shadow-sm border border-slate-200 dark:border-slate-700" />
                                                <button type="button" onClick={() => {
                                                    setProductPreviews(prev => prev.filter((_, i) => i !== idx));
                                                    setProductImages(prev => prev.filter((_, i) => i !== idx));
                                                }} className="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity shadow-md"><X size={12}/></button>
                                            </div>
                                        ))}
                                    </div>
                                )}

                                <button className="w-full bg-indigo-600 text-white font-black py-5 rounded-[1.5rem] shadow-xl shadow-indigo-500/30 uppercase tracking-widest hover:scale-[1.02] active:scale-95 transition-all">Опубликовать</button>
                            </div>
                        </form>
                    </div>
                )}
            </div>
        </div>
    );
}
