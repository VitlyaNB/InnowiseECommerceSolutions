import React, { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import axios from 'axios';
import { ArrowLeft, LayoutGrid } from 'lucide-react';

export default function CategoriesPage() {
    const [categories, setCategories] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        axios.get('/api/categories')
            .then(res => {
                setCategories(res.data.data || res.data);
                setLoading(false);
            })
            .catch(err => console.error(err));
    }, []);

    return (
        <div className="min-h-[calc(100vh-80px)] bg-gray-50 pb-16">
            <div className="bg-white px-8 py-6 mb-8 border-b border-gray-200 sticky top-20 z-40 shadow-sm">
                <div className="max-w-7xl mx-auto flex items-center gap-4">
                    <Link to="/" className="p-2 hover:bg-gray-100 rounded-full transition-colors">
                        <ArrowLeft className="w-6 h-6 text-gray-900" />
                    </Link>
                    <LayoutGrid className="w-6 h-6 text-indigo-600" />
                    <h1 className="text-2xl font-black text-gray-900">Все категории</h1>
                </div>
            </div>

            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                {loading ? (
                    <div className="flex justify-center items-center h-64"><div className="animate-spin rounded-full h-16 w-16 border-t-2 border-indigo-600"></div></div>
                ) : (
                    <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
                        {categories.map((cat) => (
                            <Link key={cat.id} to={`/catalog/${cat.id}`} className="group block bg-white rounded-3xl overflow-hidden shadow-sm hover:shadow-xl transition-all border border-gray-100">
                                <div className="aspect-square overflow-hidden relative bg-gray-200">
                                    {/* Генерируем уникальную картинку для каждой категории на основе её ID */}
                                    <img
                                        src={`https://picsum.photos/seed/category${cat.id}/500/500`}
                                        alt={cat.name}
                                        className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                                    />
                                    <div className="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
                                    <div className="absolute bottom-4 left-4 right-4 text-white font-black text-lg md:text-xl leading-tight">
                                        {cat.name}
                                    </div>
                                </div>
                            </Link>
                        ))}
                    </div>
                )}
            </div>
        </div>
    );
}
