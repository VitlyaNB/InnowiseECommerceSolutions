import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { ShoppingCart, ShieldCheck, Truck, Zap, X, Edit, Save } from 'lucide-react';
import { Link } from 'react-router-dom';
import { useAuth } from '../contexts/AuthContext';

function Catalog() {
    const { user } = useAuth();
    const [products, setProducts] = useState([]);
    const [categories, setCategories] = useState([]);
    const [isLoading, setIsLoading] = useState(true);

    // Стейты для редактирования
    const [editingProduct, setEditingProduct] = useState(null);
    const [editFormData, setEditFormData] = useState({});
    const [editImages, setEditImages] = useState([]);

    const fetchProducts = () => {
        setIsLoading(true);
        axios.get('/api/products')
            .then(res => setProducts(res.data.data || res.data))
            .finally(() => setIsLoading(false));
    };

    useEffect(() => {
        fetchProducts();
        axios.get('/api/categories').then(res => setCategories(res.data.data || res.data));
    }, []);

    const deleteProduct = async (e, id) => {
        e.preventDefault();
        if(!window.confirm("Точно удалить товар?")) return;
        try {
            await axios.delete(`/api/products/${id}`);
            fetchProducts();
        } catch (error) {
            console.error("Ошибка удаления", error);
        }
    };

    const openEditModal = (e, product) => {
        e.preventDefault();
        setEditingProduct(product.id);
        setEditFormData({
            name: product.name,
            description: product.description || '',
            price: product.price,
            quantity: product.quantity,
            category_id: product.category?.id || (categories[0]?.id || '')
        });
        setEditImages([]);
    };

    const handleEditSubmit = async (e) => {
        e.preventDefault();
        try {
            const form = new FormData();
            form.append('_method', 'PUT');
            form.append('name', editFormData.name);
            form.append('description', editFormData.description);
            form.append('price', editFormData.price);
            form.append('quantity', editFormData.quantity);
            form.append('category_id', editFormData.category_id);

            Array.from(editImages).forEach((file, index) => {
                form.append(`images[${index}]`, file);
            });

            await axios.post(`/api/products/${editingProduct}`, form, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });

            setEditingProduct(null);
            fetchProducts();
        } catch (err) {
            console.error("Ошибка редактирования", err);
        }
    };

    return (
        <div className="min-h-screen bg-gray-50 dark:bg-gray-900 font-sans text-gray-900 dark:text-white relative">
            {/* Модалка редактирования (поверх всего) */}
            {editingProduct && (
                <div className="fixed inset-0 z-[100] bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
                    <div className="bg-white p-8 rounded-3xl w-full max-w-lg shadow-2xl relative">
                        <button onClick={() => setEditingProduct(null)} className="absolute top-4 right-4 text-gray-400 hover:text-red-500"><X className="w-6 h-6"/></button>
                        <h2 className="text-2xl font-black mb-6">Редактировать товар</h2>
                        <form onSubmit={handleEditSubmit} className="space-y-4">
                            <input required value={editFormData.name} onChange={e=>setEditFormData({...editFormData, name: e.target.value})} className="w-full border p-3 rounded-xl bg-gray-50" placeholder="Название" />
                            <div className="flex gap-4">
                                <input required type="number" step="0.01" value={editFormData.price} onChange={e=>setEditFormData({...editFormData, price: e.target.value})} className="w-full border p-3 rounded-xl bg-gray-50" placeholder="Цена" />
                                <input required type="number" value={editFormData.quantity} onChange={e=>setEditFormData({...editFormData, quantity: e.target.value})} className="w-full border p-3 rounded-xl bg-gray-50" placeholder="Кол-во" />
                            </div>
                            <select value={editFormData.category_id} onChange={e=>setEditFormData({...editFormData, category_id: e.target.value})} className="w-full border p-3 rounded-xl bg-gray-50">
                                {categories.map(cat => <option key={cat.id} value={cat.id}>{cat.name}</option>)}
                            </select>

                            <label className="block text-sm font-bold text-gray-700">Заменить фото (выберите новые)</label>
                            <input type="file" multiple accept="image/*" onChange={e => setEditImages(e.target.files)} className="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />

                            <textarea required value={editFormData.description} onChange={e=>setEditFormData({...editFormData, description: e.target.value})} className="w-full border p-3 rounded-xl bg-gray-50 h-24" placeholder="Описание"></textarea>
                            <button type="submit" className="w-full bg-indigo-600 text-white font-bold py-3 rounded-xl flex items-center justify-center gap-2"><Save className="w-5 h-5"/> Сохранить изменения</button>
                        </form>
                    </div>
                </div>
            )}

            {/* Баннер */}
            <div className="relative bg-black overflow-hidden">
                <img className="absolute inset-0 w-full h-full object-cover opacity-40" src="https://images.unsplash.com/photo-1441986300917-64674bd600d8?q=80&w=2070" alt="Hero background" />
                <div className="relative max-w-7xl mx-auto py-24 px-4 sm:py-32 flex flex-col items-center text-center">
                    <h1 className="text-4xl font-extrabold text-white sm:text-6xl tracking-tight">Новая коллекция уже здесь</h1>
                </div>
            </div>

            {/* Товары */}
            <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
                <h2 className="text-3xl font-extrabold text-gray-900 dark:text-white mb-10 tracking-tight">Новинки</h2>

                {isLoading ? (
                    <div className="flex justify-center items-center h-64"><div className="animate-spin rounded-full h-16 w-16 border-t-2 border-indigo-600"></div></div>
                ) : (
                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                        {products.map((product) => {
                            const imageUrl = product.images?.[0]?.url;
                            return (
                                <Link key={product.id} to={`/product/${product.id}`} className="relative group bg-white dark:bg-gray-800 rounded-3xl overflow-hidden border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-xl transition-all flex flex-col">

                                    {user?.role === 'admin' && (
                                        <div className="absolute top-3 right-3 z-10 flex flex-col gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <button onClick={(e) => deleteProduct(e, product.id)} className="bg-white/90 dark:bg-gray-800/90 p-2 rounded-full shadow hover:bg-red-50 hover:text-red-500 transition-colors" title="Удалить">
                                                <X className="w-5 h-5 text-red-600" />
                                            </button>
                                            <button onClick={(e) => openEditModal(e, product)} className="bg-white/90 dark:bg-gray-800/90 p-2 rounded-full shadow hover:bg-blue-50 hover:text-blue-500 transition-colors" title="Редактировать">
                                                <Edit className="w-5 h-5 text-blue-600" />
                                            </button>
                                        </div>
                                    )}

                                    <div className="aspect-[4/5] bg-gray-200 dark:bg-gray-700 relative overflow-hidden">
                                        <img src={imageUrl} className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" alt={product.name} onError={(e) => { e.target.src = 'https://placehold.co/400x500/e2e8f0/94a3b8?text=No+Image'; }} />
                                    </div>
                                    <div className="p-6 flex flex-col flex-1">
                                        <h3 className="text-lg font-bold text-gray-900 dark:text-white mb-2">{product.name}</h3>
                                        <div className="flex items-center justify-between mt-auto pt-4 border-t border-gray-100 dark:border-gray-700">
                                            <span className="text-xl font-black text-gray-900 dark:text-white">{product.price} ₽</span>
                                            <button className="bg-gray-900 text-white p-3 rounded-2xl hover:bg-indigo-600 transition-colors shadow-md" onClick={(e) => e.preventDefault()}>
                                                <ShoppingCart className="h-5 w-5" />
                                            </button>
                                        </div>
                                    </div>
                                </Link>
                            )})}
                    </div>
                )}
            </main>
        </div>
    );
}

export default Catalog;
