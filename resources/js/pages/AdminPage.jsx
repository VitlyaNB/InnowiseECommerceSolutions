import React, { useState } from 'react';
import axios from 'axios';
import { Link } from 'react-router-dom';
import { ArrowLeft, PlusCircle } from 'lucide-react';

export default function AdminPage() {
    const [formData, setFormData] = useState({
        name: '', description: '', price: '', quantity: '1', category_id: '1' // По умолчанию категория 1
    });
    const [msg, setMsg] = useState('');

    const handleSubmit = async (e) => {
        e.preventDefault();
        try {
            const token = localStorage.getItem('auth_token');
            await axios.post('/api/products', formData, {
                headers: { Authorization: `Bearer ${token}` }
            });
            setMsg('Товар успешно добавлен!');
            setFormData({ name: '', description: '', price: '', quantity: '1', category_id: '1' });
        } catch (error) {
            setMsg('Ошибка при добавлении. Проверь консоль.');
            console.error(error);
        }
    };

    return (
        <div className="min-h-screen bg-gray-100 p-8">
            <div className="max-w-3xl mx-auto bg-white rounded-3xl shadow-md p-8">
                <div className="flex items-center gap-4 mb-8 border-b pb-4">
                    <Link to="/"><ArrowLeft className="w-6 h-6 hover:text-indigo-600" /></Link>
                    <h1 className="text-3xl font-black text-gray-900">Добавить товар</h1>
                </div>

                {msg && <div className="mb-6 p-4 rounded-xl bg-indigo-50 text-indigo-700 font-bold">{msg}</div>}

                <form onSubmit={handleSubmit} className="space-y-6">
                    <div>
                        <label className="block text-sm font-bold text-gray-700 mb-2">Название товара</label>
                        <input type="text" required value={formData.name} onChange={e => setFormData({...formData, name: e.target.value})} className="w-full p-3 border rounded-xl bg-gray-50 focus:bg-white focus:ring-2 focus:ring-indigo-500 outline-none" />
                    </div>

                    <div className="grid grid-cols-2 gap-6">
                        <div>
                            <label className="block text-sm font-bold text-gray-700 mb-2">Цена (₽)</label>
                            <input type="number" step="0.01" required value={formData.price} onChange={e => setFormData({...formData, price: e.target.value})} className="w-full p-3 border rounded-xl bg-gray-50 outline-none" />
                        </div>
                        <div>
                            <label className="block text-sm font-bold text-gray-700 mb-2">Количество на складе</label>
                            <input type="number" required value={formData.quantity} onChange={e => setFormData({...formData, quantity: e.target.value})} className="w-full p-3 border rounded-xl bg-gray-50 outline-none" />
                        </div>
                    </div>

                    <div>
                        <label className="block text-sm font-bold text-gray-700 mb-2">ID Категории (1-Мужская, 2-Обувь и т.д.)</label>
                        <input type="number" required value={formData.category_id} onChange={e => setFormData({...formData, category_id: e.target.value})} className="w-full p-3 border rounded-xl bg-gray-50 outline-none" />
                        <p className="text-xs text-gray-500 mt-1">*Перед добавлением убедись, что категории существуют в БД</p>
                    </div>

                    <div>
                        <label className="block text-sm font-bold text-gray-700 mb-2">Описание</label>
                        <textarea required value={formData.description} onChange={e => setFormData({...formData, description: e.target.value})} className="w-full p-3 border rounded-xl bg-gray-50 outline-none h-32"></textarea>
                    </div>

                    <button type="submit" className="flex items-center justify-center gap-2 w-full bg-black text-white font-bold py-4 rounded-xl hover:bg-indigo-600 transition-colors">
                        <PlusCircle className="w-5 h-5" /> Добавить в каталог
                    </button>
                </form>
            </div>
        </div>
    );
}
