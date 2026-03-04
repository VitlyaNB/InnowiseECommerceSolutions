import React, { useState, useEffect } from 'react';
import api from '../api';
import { Link } from 'react-router-dom';
import {
    ArrowLeft, PlusCircle, Users, LayoutDashboard, Tag, Image as ImageIcon,
    Trash2, Edit, AlertCircle, FolderTree, RefreshCw, X, Check, Wallet, Package
} from 'lucide-react';

export default function AdminPage() {
    const [activeTab, setActiveTab] = useState('addProduct');
    const [users, setUsers] = useState([]);
    const [categories, setCategories] = useState([]);
    const [products, setProducts] = useState([]);
    const [msg, setMsg] = useState({ text: '', isError: false });

    // Состояния форм
    const [formData, setFormData] = useState({ name: '', description: '', price: '', quantity: '1', category_id: '' });
    const [images, setImages] = useState([]);
    const [categoryName, setCategoryName] = useState('');
    const [editingUserId, setEditingUserId] = useState(null);
    const [editUserData, setEditUserData] = useState({ name: '', email: '', role: 'user', balance: 0 });

    const fetchAll = () => {
        api.get('/categories').then(res => setCategories(res.data.data || res.data)).catch(console.error);
        if (activeTab === 'users') api.get('/users').then(res => setUsers(res.data.data || res.data)).catch(console.error);
        if (activeTab === 'manageProducts') api.get('/products').then(res => setProducts(res.data.data || res.data)).catch(console.error);
    };

    useEffect(() => { fetchAll(); }, [activeTab]);

    const handleProductSubmit = async (e) => {
        e.preventDefault();
        const form = new FormData();
        Object.keys(formData).forEach(key => form.append(key, formData[key]));
        if (images) Array.from(images).forEach(file => form.append('images[]', file));
        try {
            await api.post('/products', form);
            setMsg({ text: 'Товар добавлен', isError: false });
            setFormData({ name: '', description: '', price: '', quantity: '1', category_id: categories[0]?.id || '' });
        } catch (err) { setMsg({ text: 'Ошибка', isError: true }); }
    };

    const handleDeleteProduct = async (id) => {
        if (!window.confirm("Удалить товар?")) return;
        try {
            await api.delete(`/products/${id}`);
            setMsg({ text: 'Товар удален', isError: false });
            api.get('/products').then(res => setProducts(res.data.data || res.data));
        } catch (err) { setMsg({ text: 'Ошибка удаления', isError: true }); }
    };

    const handleDeleteCategory = async (id) => {
        if (!window.confirm("Удалить категорию?")) return;
        try {
            await api.delete(`/categories/${id}`);
            setMsg({ text: 'Категория удалена', isError: false });
            api.get('/categories').then(res => setCategories(res.data.data || res.data));
        } catch (err) { setMsg({ text: 'Ошибка', isError: true }); }
    };

    const handleEditUser = (u) => {
        setEditingUserId(u.id);
        setEditUserData({ name: u.name, email: u.email, role: u.role, balance: u.balance || 0 });
    };

    const handleSaveUser = async (id) => {
        try {
            await api.put(`/users/${id}`, editUserData);
            setEditingUserId(null);
            setMsg({ text: 'Обновлено', isError: false });
            api.get('/users').then(res => setUsers(res.data.data || res.data));
        } catch (err) { setMsg({ text: 'Ошибка', isError: true }); }
    };

    const handleDeleteUser = async (id) => {
        if (!window.confirm("Удалить пользователя?")) return;
        try {
            await api.delete(`/users/${id}`);
            api.get('/users').then(res => setUsers(res.data.data || res.data));
        } catch (err) { setMsg({ text: 'Ошибка', isError: true }); }
    };

    return (
        <div className="min-h-screen bg-gray-50 dark:bg-gray-900 flex">
            {/* Sidebar */}
            <div className="w-64 bg-white dark:bg-gray-800 border-r dark:border-gray-700 p-4">
                <h2 className="text-xl font-black mb-8 dark:text-white uppercase">Админка</h2>
                <div className="space-y-2">
                    <button onClick={() => setActiveTab('addProduct')} className={`w-full flex items-center gap-3 p-3 rounded-xl font-bold ${activeTab === 'addProduct' ? 'bg-indigo-600 text-white' : 'text-gray-500'}`}><PlusCircle size={20}/> Создать товар</button>
                    <button onClick={() => setActiveTab('manageProducts')} className={`w-full flex items-center gap-3 p-3 rounded-xl font-bold ${activeTab === 'manageProducts' ? 'bg-indigo-600 text-white' : 'text-gray-500'}`}><Package size={20}/> Все товары</button>
                    <button onClick={() => setActiveTab('categories')} className={`w-full flex items-center gap-3 p-3 rounded-xl font-bold ${activeTab === 'categories' ? 'bg-indigo-600 text-white' : 'text-gray-500'}`}><FolderTree size={20}/> Категории</button>
                    <button onClick={() => setActiveTab('users')} className={`w-full flex items-center gap-3 p-3 rounded-xl font-bold ${activeTab === 'users' ? 'bg-indigo-600 text-white' : 'text-gray-500'}`}><Users size={20}/> Пользователи</button>
                </div>
                <Link to="/" className="mt-10 block p-3 text-gray-400 font-bold hover:text-indigo-600 transition-colors flex items-center gap-2"><ArrowLeft size={18}/> В магазин</Link>
            </div>

            {/* Content */}
            <div className="flex-1 p-10 overflow-y-auto">
                {msg.text && <div className={`mb-6 p-4 rounded-xl font-bold border ${msg.isError ? 'bg-red-50 text-red-700' : 'bg-green-50 text-green-700'}`}>{msg.text}</div>}

                {activeTab === 'addProduct' && (
                    <div className="max-w-2xl bg-white dark:bg-gray-800 p-8 rounded-3xl shadow-sm">
                        <h3 className="text-2xl font-black mb-6 dark:text-white uppercase">Новый товар</h3>
                        <form onSubmit={handleProductSubmit} className="space-y-4">
                            <input type="text" placeholder="Название" required value={formData.name} onChange={e => setFormData({...formData, name: e.target.value})} className="w-full p-4 border rounded-xl dark:bg-gray-700 dark:text-white"/>
                            <div className="grid grid-cols-2 gap-4">
                                <input type="number" step="0.01" placeholder="Цена" required value={formData.price} onChange={e => setFormData({...formData, price: e.target.value})} className="w-full p-4 border rounded-xl dark:bg-gray-700 dark:text-white"/>
                                <input type="number" placeholder="Склад" required value={formData.quantity} onChange={e => setFormData({...formData, quantity: e.target.value})} className="w-full p-4 border rounded-xl dark:bg-gray-700 dark:text-white"/>
                            </div>
                            <select value={formData.category_id} onChange={e => setFormData({...formData, category_id: e.target.value})} className="w-full p-4 border rounded-xl dark:bg-gray-700 dark:text-white">
                                {categories.map(c => <option key={c.id} value={c.id}>{c.name}</option>)}
                            </select>
                            <input type="file" multiple onChange={e => setImages(e.target.files)} className="w-full text-sm text-gray-500"/>
                            <textarea placeholder="Описание" required value={formData.description} onChange={e => setFormData({...formData, description: e.target.value})} className="w-full p-4 border rounded-xl dark:bg-gray-700 dark:text-white h-32"/>
                            <button className="w-full bg-indigo-600 text-white font-black py-4 rounded-2xl">СОЗДАТЬ</button>
                        </form>
                    </div>
                )}

                {activeTab === 'manageProducts' && (
                    <div className="bg-white dark:bg-gray-800 rounded-3xl overflow-hidden shadow-sm">
                        <table className="w-full text-left">
                            <thead className="bg-gray-50 dark:bg-gray-700 text-xs font-black uppercase text-gray-400">
                            <tr><th className="p-5">Товар</th><th className="p-5">Цена</th><th className="p-5 text-right">Действие</th></tr>
                            </thead>
                            <tbody className="divide-y dark:divide-gray-700">
                            {products.map(p => (
                                <tr key={p.id} className="dark:text-white">
                                    <td className="p-5 font-bold">{p.name}</td>
                                    <td className="p-5 font-mono">{p.price} BYN</td>
                                    <td className="p-5 text-right">
                                        <button onClick={() => handleDeleteProduct(p.id)} className="text-red-500 hover:bg-red-50 p-2 rounded-lg"><Trash2 size={20}/></button>
                                    </td>
                                </tr>
                            ))}
                            </tbody>
                        </table>
                    </div>
                )}

                {activeTab === 'users' && (
                    <div className="bg-white dark:bg-gray-800 rounded-3xl overflow-hidden shadow-sm">
                        <table className="w-full text-left">
                            <thead className="bg-gray-50 dark:bg-gray-700 text-xs font-black uppercase text-gray-400">
                            <tr><th className="p-5">Имя / Email</th><th className="p-5">Роль</th><th className="p-5">Баланс</th><th className="p-5 text-right">Действия</th></tr>
                            </thead>
                            <tbody className="divide-y dark:divide-gray-700">
                            {users.map(u => (
                                <tr key={u.id} className="dark:text-white">
                                    <td className="p-5">
                                        {editingUserId === u.id ? <input className="border p-2 rounded w-full dark:bg-gray-700" value={editUserData.name} onChange={e => setEditUserData({...editUserData, name: e.target.value})}/> : <div><div className="font-bold">{u.name}</div><div className="text-xs text-gray-500">{u.email}</div></div>}
                                    </td>
                                    <td className="p-5">
                                        {editingUserId === u.id ? <select className="border p-2 rounded dark:bg-gray-700" value={editUserData.role} onChange={e => setEditUserData({...editUserData, role: e.target.value})}><option value="user">user</option><option value="admin">admin</option></select> : <span className={`px-2 py-1 rounded-full text-[10px] font-black uppercase ${u.role === 'admin' ? 'bg-red-100 text-red-600' : 'bg-green-100 text-green-600'}`}>{u.role}</span>}
                                    </td>
                                    <td className="p-5 font-mono">
                                        {editingUserId === u.id ? <input type="number" className="border p-2 rounded w-24 dark:bg-gray-700" value={editUserData.balance} onChange={e => setEditUserData({...editUserData, balance: e.target.value})}/> : `${u.balance} BYN`}
                                    </td>
                                    <td className="p-5 text-right flex justify-end gap-2">
                                        {editingUserId === u.id ? <button onClick={() => handleSaveUser(u.id)} className="text-green-500"><Check/></button> : <button onClick={() => handleEditUser(u)} className="text-indigo-600"><Edit size={20}/></button>}
                                        <button onClick={() => handleDeleteUser(u.id)} className="text-red-500"><Trash2 size={20}/></button>
                                    </td>
                                </tr>
                            ))}
                            </tbody>
                        </table>
                    </div>
                )}

                {activeTab === 'categories' && (
                    <div className="max-w-xl bg-white dark:bg-gray-800 p-8 rounded-3xl shadow-sm">
                        <div className="space-y-4">
                            {categories.map(c => (
                                <div key={c.id} className="flex justify-between items-center p-4 bg-gray-50 dark:bg-gray-700 rounded-xl">
                                    <span className="font-bold dark:text-white">{c.name}</span>
                                    <button onClick={() => handleDeleteCategory(c.id)} className="text-red-500"><Trash2 size={18}/></button>
                                </div>
                            ))}
                        </div>
                    </div>
                )}
            </div>
        </div>
    );
}
