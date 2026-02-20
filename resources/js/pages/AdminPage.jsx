import React, { useState } from 'react';
import '../bootstrap';
import axios from 'axios';
import { PackagePlus, ArrowLeft } from 'lucide-react';
import { Link } from 'react-router-dom';

export default function AdminPage() {
    const [formData, setFormData] = useState({
        name: '',
        description: '',
        price: '',
        category_id: 1
    });

    const handleSubmit = async (e) => {
        e.preventDefault();
        try {
            await axios.post('/api/products', formData);
            alert('Товар успешно добавлен!');
            setFormData({ name: '', description: '', price: '', category_id: 1 });
        } catch (error) {
            alert('Ошибка при сохранении');
        }
    };

    return (
        <div className="min-h-screen bg-gray-100 p-8">
            <Link to="/" className="flex items-center text-indigo-600 mb-6 hover:underline">
                <ArrowLeft className="w-4 h-4 mr-2" /> На главную
            </Link>

            <div className="max-w-2xl mx-auto bg-white rounded-3xl shadow-xl p-8">
                <div className="flex items-center gap-3 mb-8">
                    <PackagePlus className="w-8 h-8 text-indigo-600" />
                    <h1 className="text-2xl font-black">Добавить новый товар</h1>
                </div>

                <form onSubmit={handleSubmit} className="space-y-6">
                    <div>
                        <label className="block text-sm font-bold mb-2">Название товара</label>
                        <input
                            type="text"
                            className="w-full p-3 border rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none"
                            value={formData.name}
                            onChange={(e) => setFormData({...formData, name: e.target.value})}
                            placeholder="Например: Кроссовки Nike Air"
                            required
                        />
                    </div>
                    <div>
                        <label className="block text-sm font-bold mb-2">Описание</label>
                        <textarea
                            className="w-full p-3 border rounded-xl h-32 focus:ring-2 focus:ring-indigo-500 outline-none"
                            value={formData.description}
                            onChange={(e) => setFormData({...formData, description: e.target.value})}
                        />
                    </div>
                    <div className="grid grid-cols-2 gap-4">
                        <div>
                            <label className="block text-sm font-bold mb-2">Цена (₽)</label>
                            <input
                                type="number"
                                className="w-full p-3 border rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none"
                                value={formData.price}
                                onChange={(e) => setFormData({...formData, price: e.target.value})}
                                required
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-bold mb-2">Категория ID</label>
                            <input
                                type="number"
                                className="w-full p-3 border rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none"
                                value={formData.category_id}
                                onChange={(e) => setFormData({...formData, category_id: e.target.value})}
                            />
                        </div>
                    </div>
                    <button type="submit" className="w-full bg-indigo-600 text-white font-bold py-4 rounded-xl hover:bg-indigo-700 transition-transform active:scale-95">
                        Опубликовать в каталоге
                    </button>
                </form>
            </div>
        </div>
    );
}
