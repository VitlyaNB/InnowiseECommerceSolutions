import React, { useState, useEffect } from 'react';
import api from '../api';
import { Link } from 'react-router-dom';
import ChatWindow from '../components/ChatWindow';
import {
    ArrowLeft, PlusCircle, Users, LayoutDashboard,
    Trash2, FolderTree, RefreshCw, X, MessageCircle,
    Package, Upload, Image as ImageIcon, ShieldCheck
} from 'lucide-react';

const normalizeAdminChat = (chat) => {
    const userName = chat?.user?.name ?? chat?.userName ?? 'Пользователь';
    const userEmail = chat?.user?.email ?? chat?.userEmail ?? null;
    const rawLastMessage = chat?.last_message ?? chat?.lastMessage ?? null;
    const lastMessage = rawLastMessage ? {
        ...rawLastMessage,
        message: rawLastMessage.message ?? '',
        created_at: rawLastMessage.created_at ?? rawLastMessage.createdAt ?? null
    } : null;

    return {
        ...chat,
        id: chat?.id ?? null,
        user: {
            id: chat?.user?.id ?? chat?.userId ?? null,
            name: userName,
            email: userEmail
        },
        last_message: lastMessage
    };
};

export default function AdminPage() {
    const [activeTab, setActiveTab] = useState('addProduct');
    const [chats, setChats] = useState([]);
    const [users, setUsers] = useState([]);
    const [selectedChat, setSelectedChat] = useState(null);
    const [categories, setCategories] = useState([]);
    const [products, setProducts] = useState([]);
    const [msg, setMsg] = useState({ text: '', isError: false });
    const [loading, setLoading] = useState(false);

    // Editing states
    const [editingProduct, setEditingProduct] = useState(null);
    const [editingCategory, setEditingCategory] = useState(null);
    const [editingUser, setEditingUser] = useState(null);

    // Form States
    const [formData, setFormData] = useState({ name: '', description: '', price: '', quantity: '1', category_id: '' });
    const [userData, setUserData] = useState({ name: '', email: '', role: 'user', balance: '0' });
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
        } else if (activeTab === 'chats') {
            api.get('/admin/chats')
                .then(res => {
                    const rawChats = res.data?.data || res.data || [];
                    const normalizedChats = Array.isArray(rawChats)
                        ? rawChats.map(normalizeAdminChat).filter(chat => chat.id !== null)
                        : [];
                    setChats(normalizedChats);
                })
                .finally(() => setLoading(false));
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

    // Новый обработчик для файла категории
    const handleCategoryFile = (e) => {
        const file = e.target.files[0];
        if (file) {
            setCategoryImage(file);
            setCategoryPreview(URL.createObjectURL(file));
        }
    };

    const handleProductEdit = (product) => {
        setEditingProduct(product);
        setFormData({
            name: product.name,
            description: product.description || '',
            price: product.price,
            quantity: product.quantity,
            category_id: product.category_id || (categories[0]?.id || '')
        });
        setProductPreviews([]);
        setProductImages([]);
        setActiveTab('addProduct');
    };

    const handleCategoryEdit = (category) => {
        setEditingCategory(category);
        setCategoryName(category.name);
        setCategoryPreview(category.image_url || category.image_path || null);
        setCategoryImage(null);
        setActiveTab('categories');
    };

    const handleUserEdit = (user) => {
        setEditingUser(user);
        setUserData({
            name: user.name,
            email: user.email,
            role: user.role,
            balance: user.balance
        });
    };

    const handleUserSubmit = async (e) => {
        e.preventDefault();
        try {
            await api.put(`/users/${editingUser.id}`, userData);
            setMsg({ text: 'Пользователь обновлен!', isError: false });
            setEditingUser(null);
            fetchAll();
        } catch (err) {
            const backendMessage = err.response?.data?.message;
            const validationErrors = err.response?.data?.errors;
            const firstValidationError = validationErrors
                ? Object.values(validationErrors).flat()[0]
                : null;
            setMsg({ text: firstValidationError || backendMessage || 'Ошибка обновления', isError: true });
        }
    };

    const handleProductSubmit = async (e) => {
        e.preventDefault();
        const form = new FormData();
        Object.keys(formData).forEach(key => {
            if (formData[key] !== null && formData[key] !== undefined) {
                form.append(key, formData[key]);
            }
        });
        productImages.forEach(file => form.append('images[]', file));
        
        try {
            if (editingProduct) {
                await api.post(`/products/${editingProduct.id}`, form);
                setMsg({ text: 'Товар обновлен!', isError: false });
            } else {
                await api.post('/products', form);
                setMsg({ text: 'Товар создан!', isError: false });
            }
            setFormData({ name: '', description: '', price: '', quantity: '1', category_id: categories[0]?.id || '' });
            setProductImages([]); setProductPreviews([]);
            setEditingProduct(null);
            fetchAll();
        } catch (err) { setMsg({ text: err.response?.data?.message || 'Ошибка', isError: true }); }
    };

    const handleCategorySubmit = async (e) => {
        e.preventDefault();
        const form = new FormData();
        form.append('name', categoryName);
        if (categoryImage) form.append('image', categoryImage);
        
        try {
            if (editingCategory) {
                await api.post(`/categories/${editingCategory.id}`, form);
                setMsg({ text: 'Категория обновлена', isError: false });
            } else {
                await api.post('/categories', form);
                setMsg({ text: 'Категория добавлена', isError: false });
            }
            setCategoryName(''); setCategoryImage(null); setCategoryPreview(null);
            setEditingCategory(null);
            fetchAll();
        } catch (err) { setMsg({ text: 'Ошибка сохранения', isError: true }); }
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
                    <button onClick={() => setActiveTab('chats')} className={`w-full flex items-center gap-3 p-4 rounded-2xl font-bold transition-all ${activeTab === 'chats' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30' : 'text-slate-500 hover:bg-white dark:hover:bg-slate-800'}`}><MessageCircle size={20}/> Сообщения</button>
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

                {activeTab === 'users' && (
                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 animate-in fade-in duration-500">
                        {/* Форма редактирования */}
                        <div className="glass-card p-8 rounded-[2.5rem]">
                            <h3 className="text-xl font-black mb-6 dark:text-white uppercase tracking-tight">
                                {editingUser ? `Редактирование: ${editingUser.name}` : 'Управление пользователями'}
                            </h3>
                            {editingUser ? (
                                <form onSubmit={handleUserSubmit} className="space-y-4">
                                    <div className="space-y-1">
                                        <label className="text-[10px] font-black uppercase text-slate-400 tracking-wider ml-2">Имя</label>
                                        <input type="text" value={userData.name} onChange={e => setUserData({...userData, name: e.target.value})} className="admin-input" required />
                                    </div>
                                    <div className="space-y-1">
                                        <label className="text-[10px] font-black uppercase text-slate-400 tracking-wider ml-2">Email</label>
                                        <input type="email" value={userData.email} onChange={e => setUserData({...userData, email: e.target.value})} className="admin-input" required />
                                    </div>
                                    <div className="space-y-1">
                                        <label className="text-[10px] font-black uppercase text-slate-400 tracking-wider ml-2">Баланс (BYN)</label>
                                        <input type="number" step="0.01" value={userData.balance} onChange={e => setUserData({...userData, balance: e.target.value})} className="admin-input" required />
                                    </div>
                                    <div className="space-y-1">
                                        <label className="text-[10px] font-black uppercase text-slate-400 tracking-wider ml-2">Роль</label>
                                        <select value={userData.role} onChange={e => setUserData({...userData, role: e.target.value})} className="admin-input">
                                            <option value="user">Пользователь (User)</option>
                                            <option value="admin">Администратор (Admin)</option>
                                        </select>
                                    </div>
                                    <div className="flex gap-4 pt-4">
                                        <button className="flex-1 bg-indigo-600 text-white font-black py-4 rounded-2xl shadow-lg hover:scale-[1.02] active:scale-95 transition-all uppercase tracking-widest text-xs">
                                            Сохранить
                                        </button>
                                        <button type="button" onClick={() => setEditingUser(null)} className="flex-1 bg-slate-100 dark:bg-slate-700 font-black py-4 rounded-2xl uppercase tracking-widest text-xs">
                                            Отмена
                                        </button>
                                    </div>
                                </form>
                            ) : (
                                <div className="h-64 flex flex-col items-center justify-center text-slate-400 border-2 border-dashed border-slate-100 dark:border-slate-800 rounded-[2rem]">
                                    <Users size={48} className="mb-4 opacity-20" />
                                    <p className="font-black uppercase tracking-widest text-[10px] opacity-50">Выберите пользователя для редактирования</p>
                                </div>
                            )}
                        </div>

                        {/* Список пользователей */}
                        <div className="glass-card rounded-[2.5rem] overflow-hidden border border-white/20">
                            <div className="p-6 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center bg-white/50 dark:bg-slate-800/50 backdrop-blur-sm">
                                <h3 className="text-xl font-black dark:text-white uppercase tracking-tight">Пользователи</h3>
                                <button onClick={fetchAll} className="p-2 text-indigo-600 bg-indigo-50 dark:bg-indigo-900/30 rounded-xl hover:rotate-180 transition-transform duration-500"><RefreshCw size={20} /></button>
                            </div>
                            <div className="overflow-x-auto max-h-[600px] custom-scrollbar">
                                <table className="w-full text-left">
                                    <thead className="bg-slate-50 dark:bg-slate-900/50 text-[10px] font-black uppercase text-slate-400 tracking-widest border-b border-slate-200 dark:border-slate-700">
                                        <tr>
                                            <th className="p-6 pl-8">Имя / Email</th>
                                            <th className="p-6 text-right pr-8">Действия</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-slate-100 dark:divide-slate-700/50">
                                    {users.map(u => (
                                        <tr key={u.id} className={`hover:bg-indigo-50/30 dark:hover:bg-slate-800/30 transition-colors group ${editingUser?.id === u.id ? 'bg-indigo-50/50 dark:bg-indigo-900/20' : ''}`}>
                                            <td className="p-4 pl-8">
                                                <div className="flex items-center gap-4">
                                                    <div className="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 flex items-center justify-center font-black">
                                                        {u.name[0].toUpperCase()}
                                                    </div>
                                                    <div>
                                                        <div className="font-bold text-slate-900 dark:text-white text-sm">{u.name}</div>
                                                        <div className="text-[10px] text-slate-500 font-bold uppercase tracking-tighter">{u.email}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td className="p-4 text-right pr-8">
                                                <div className="flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition-all">
                                                    <button onClick={() => handleUserEdit(u)} className="p-2 text-indigo-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 rounded-xl transition-all">
                                                        <RefreshCw size={18}/>
                                                    </button>
                                                    <button onClick={async () => {
                                                        try {
                                                            await api.delete(`/users/${u.id}`);
                                                            fetchAll();
                                                        } catch (err) {
                                                            setMsg({ text: err.response?.data?.message || 'Ошибка удаления пользователя', isError: true });
                                                        }
                                                    }} className="p-2 text-red-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-xl transition-all">
                                                        <Trash2 size={18}/>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    ))}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                )}

                {/* ТАБ: КАТЕГОРИИ (ОБНОВЛЕННЫЙ) */}
                {activeTab === 'categories' && (
                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 animate-in fade-in duration-500">
                        <div className="glass-card p-8 rounded-[2.5rem]">
                            <div className="flex justify-between items-center mb-6">
                                <h3 className="text-xl font-black dark:text-white uppercase tracking-tight">
                                    {editingCategory ? 'Редактирование категории' : 'Новая категория'}
                                </h3>
                                {editingCategory && (
                                    <button 
                                        onClick={() => {
                                            setEditingCategory(null);
                                            setCategoryName('');
                                            setCategoryPreview(null);
                                            setCategoryImage(null);
                                        }}
                                        className="px-3 py-1 bg-slate-200 dark:bg-slate-700 rounded-lg font-bold text-[10px] uppercase"
                                    >
                                        Отмена
                                    </button>
                                )}
                            </div>
                            <form onSubmit={handleCategorySubmit} className="space-y-6">
                                <div className="space-y-2">
                                    <label className="text-xs font-bold uppercase text-slate-400 tracking-wider ml-2">Название</label>
                                    <input
                                        type="text"
                                        placeholder="Название категории"
                                        required
                                        value={categoryName}
                                        onChange={e => setCategoryName(e.target.value)}
                                        className="admin-input"
                                    />
                                </div>

                                <div className="space-y-2">
                                    <label className="text-xs font-bold uppercase text-slate-400 tracking-wider ml-2">Обложка</label>
                                    <div className="relative group border-3 border-dashed border-indigo-100 hover:border-indigo-400 dark:border-slate-700 dark:hover:border-indigo-500 rounded-[2rem] p-8 transition-all flex flex-col items-center justify-center bg-indigo-50/30 dark:bg-slate-800/30 hover:bg-indigo-50/60 h-48 cursor-pointer overflow-hidden text-center">
                                        {categoryPreview ? (
                                            <div className="absolute inset-0 w-full h-full">
                                                <img src={categoryPreview} className="w-full h-full object-cover" alt="Preview" />
                                                <div className="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                                    <RefreshCw className="text-white animate-spin-slow" size={32} />
                                                </div>
                                            </div>
                                        ) : (
                                            <>
                                                <Upload className="text-indigo-400 mb-2 group-hover:scale-110 transition-transform duration-300" size={32} />
                                                <p className="text-xs font-black text-indigo-900 dark:text-indigo-300 uppercase tracking-widest">Кликните для выбора фото</p>
                                            </>
                                        )}
                                        <input
                                            type="file"
                                            onChange={handleCategoryFile}
                                            className="absolute inset-0 opacity-0 cursor-pointer z-10"
                                            accept="image/*"
                                        />
                                    </div>
                                </div>

                                <button className="w-full bg-indigo-600 text-white font-black py-4 rounded-2xl shadow-lg shadow-indigo-500/30 hover:scale-[1.02] active:scale-95 transition-all uppercase tracking-widest">
                                    {editingCategory ? 'СОХРАНИТЬ' : 'СОЗДАТЬ'}
                                </button>
                            </form>
                        </div>
                        <div className="glass-card p-8 rounded-[2.5rem]">
                            <h3 className="text-xl font-black mb-6 dark:text-white uppercase tracking-tight">Список категорий</h3>
                            <div className="space-y-3 max-h-[500px] overflow-y-auto pr-2 custom-scrollbar">
                                {categories.map(c => (
                                    <div key={c.id} className="flex justify-between items-center p-4 bg-white/60 dark:bg-slate-800/60 backdrop-blur-sm rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-md transition-all group">
                                        <div className="flex items-center gap-4">
                                            <div className="w-12 h-12 rounded-xl bg-slate-100 dark:bg-slate-900 overflow-hidden">
                                                {(c.image_url || c.image_path) && <img src={c.image_url || c.image_path} className="w-full h-full object-cover" />}
                                            </div>
                                            <span className="font-bold text-slate-800 dark:text-slate-200">{c.name}</span>
                                        </div>
                                        <div className="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <button onClick={() => handleCategoryEdit(c)} className="text-indigo-400 hover:text-indigo-600 p-2 rounded-lg transition-colors"><RefreshCw size={18}/></button>
                                            <button onClick={async () => {
                                                try {
                                                    await api.delete(`/categories/${c.id}`);
                                                    fetchAll();
                                                } catch (err) {
                                                    setMsg({ text: err.response?.data?.message || 'Ошибка удаления категории', isError: true });
                                                }
                                            }} className="text-red-400 hover:text-red-600 p-2 rounded-lg transition-colors"><Trash2 size={18}/></button>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>
                )}

                {activeTab === 'manageProducts' && (
                    <div className="glass-card rounded-[2.5rem] overflow-hidden animate-in fade-in duration-500 border border-white/20">
                        <div className="p-6 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center bg-white/50 dark:bg-slate-800/50 backdrop-blur-sm">
                            <h3 className="text-xl font-black dark:text-white">Товары</h3>
                            <button onClick={fetchAll} className="p-2 text-indigo-600 bg-indigo-50 dark:bg-indigo-900/30 rounded-xl hover:bg-white transition-all"><RefreshCw size={20} /></button>
                        </div>
                        <div className="overflow-x-auto">
                            <table className="w-full text-left">
                                <thead className="bg-slate-50 dark:bg-slate-900/50 text-[10px] font-black uppercase text-slate-400 tracking-widest border-b border-slate-200 dark:border-slate-700">
                                <tr><th className="p-6 pl-8">Товар</th><th className="p-6">Цена</th><th className="p-6">Склад</th><th className="p-6 text-right pr-8">Действие</th></tr>
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
                                                <div className="text-[10px] text-slate-400 uppercase tracking-wider font-bold mt-1">
                                                    {p.category_name || categories.find((category) => category.id === p.category_id)?.name || 'Без категории'}
                                                </div>
                                            </div>
                                        </td>
                                        <td className="p-4 font-mono text-indigo-600 font-black text-lg">{p.price}</td>
                                        <td className="p-4 font-mono text-slate-700 dark:text-slate-200 font-bold">{p.quantity}</td>
                                        <td className="p-4 text-right pr-8">
                                            <div className="flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition-all">
                                                <button onClick={() => handleProductEdit(p)} className="p-2 text-indigo-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 rounded-xl transition-all">
                                                    <RefreshCw size={18}/>
                                                </button>
                                                <button onClick={async () => {
                                                    try {
                                                        await api.delete(`/products/${p.id}`);
                                                        fetchAll();
                                                    } catch (err) {
                                                        setMsg({ text: err.response?.data?.message || 'Ошибка удаления товара', isError: true });
                                                    }
                                                }} className="p-2 text-red-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-xl transition-all">
                                                    <Trash2 size={18}/>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                                </tbody>
                            </table>
                        </div>
                    </div>
                )}

                {activeTab === 'addProduct' && (
                    <div className="max-w-5xl glass-card p-10 rounded-[2.5rem] animate-in slide-in-from-bottom-4 duration-500 border border-white/20 shadow-2xl">
                        <div className="flex justify-between items-center mb-10">
                            <h3 className="text-3xl font-black tracking-tighter uppercase dark:text-white">
                                {editingProduct ? 'Редактирование товара' : 'Создание товара'}
                            </h3>
                            {editingProduct && (
                                <button 
                                    onClick={() => {
                                        setEditingProduct(null);
                                        setFormData({ name: '', description: '', price: '', quantity: '1', category_id: categories[0]?.id || '' });
                                        setProductImages([]); setProductPreviews([]);
                                    }}
                                    className="px-4 py-2 bg-slate-200 dark:bg-slate-700 rounded-xl font-bold text-xs uppercase"
                                >
                                    Отмена
                                </button>
                            )}
                        </div>
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
                                <div className="grid grid-cols-1 gap-6">
                                    <div className="space-y-2">
                                        <label className="text-xs font-bold uppercase text-slate-400 tracking-wider ml-2">Категория</label>
                                        <select value={formData.category_id} onChange={e => setFormData({...formData, category_id: e.target.value})} className="admin-input appearance-none cursor-pointer">
                                            {categories.map(c => <option key={c.id} value={c.id}>{c.name}</option>)}
                                        </select>
                                    </div>
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
                                {editingProduct && editingProduct.images?.length > 0 && productPreviews.length === 0 && (
                                    <div className="grid grid-cols-4 gap-4">
                                        {editingProduct.images.map((img, idx) => (
                                            <img key={idx} src={img.url} className="aspect-square rounded-2xl object-cover shadow-sm border border-slate-200 dark:border-slate-700 opacity-50" />
                                        ))}
                                    </div>
                                )}

                                <button className="w-full bg-indigo-600 text-white font-black py-5 rounded-[1.5rem] shadow-xl shadow-indigo-500/30 uppercase tracking-widest hover:scale-[1.02] active:scale-95 transition-all">
                                    {editingProduct ? 'Сохранить изменения' : 'Опубликовать'}
                                </button>
                            </div>
                        </form>
                    </div>
                )}
                {activeTab === 'chats' && (
                    <div className="flex h-[700px] glass-card rounded-[2.5rem] overflow-hidden animate-in fade-in duration-500 border border-white/20">
                        {/* Chat List */}
                        <div className="w-80 border-r border-slate-200 dark:border-slate-700 flex flex-col bg-white/50 dark:bg-slate-800/50">
                            <div className="p-6 border-b border-slate-200 dark:border-slate-700">
                                <h3 className="text-xl font-black dark:text-white uppercase tracking-tight">Чаты</h3>
                            </div>
                            <div className="flex-1 overflow-y-auto custom-scrollbar">
                                {chats.map(chat => (
                                    <button
                                        key={chat.id}
                                        onClick={() => setSelectedChat(chat)}
                                        className={`w-full p-4 flex items-center gap-4 hover:bg-indigo-50 dark:hover:bg-slate-700/50 transition-colors text-left border-b border-slate-50 dark:border-slate-700/30 ${selectedChat?.id === chat.id ? 'bg-indigo-50 dark:bg-indigo-900/20' : ''}`}
                                    >
                                        <div className="w-12 h-12 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 text-white flex items-center justify-center font-black shrink-0">
                                            {chat.user.name[0]?.toUpperCase() || 'U'}
                                        </div>
                                        <div className="flex-1 min-w-0">
                                            <div className="font-bold text-slate-900 dark:text-white truncate">{chat.user.name}</div>
                                            <div className="text-xs text-slate-500 truncate">{chat.last_message?.message || 'Нет сообщений'}</div>
                                        </div>
                                        {chat.last_message && (
                                            <div className="text-[10px] text-slate-400 font-bold">
                                                {chat.last_message.created_at
                                                    ? new Date(chat.last_message.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})
                                                    : ''}
                                            </div>
                                        )}
                                    </button>
                                ))}
                                {chats.length === 0 && (
                                    <div className="p-10 text-center text-slate-400 font-bold uppercase text-xs tracking-widest opacity-50 mt-20">
                                        Чаты отсутствуют
                                    </div>
                                )}
                            </div>
                        </div>

                        {/* Chat Area */}
                        <div className="flex-1 bg-slate-50 dark:bg-slate-950 flex flex-col">
                            {selectedChat ? (
                                <ChatWindow chatId={selectedChat.id} isAdmin={true} />
                            ) : (
                                <div className="flex-1 flex flex-col items-center justify-center text-slate-400">
                                    <MessageCircle size={64} className="mb-4 opacity-20" />
                                    <p className="font-black uppercase tracking-widest text-xs opacity-50">Выберите чат для общения</p>
                                </div>
                            )}
                        </div>
                    </div>
                )}
            </div>
        </div>
    );
}
