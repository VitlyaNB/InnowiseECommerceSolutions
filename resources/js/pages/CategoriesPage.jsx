import React, { useEffect, useState } from 'react';
import api from '../api';
import { Link } from 'react-router-dom';
import { Tag, ArrowRight } from 'lucide-react';

export default function CategoriesPage() {
    const [categories, setCategories] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        api.get('/categories')
            .then(res => setCategories(res.data.data || res.data))
            .catch(err => console.error(err))
            .finally(() => setLoading(false));
    }, []);

    if (loading) return <div className="flex justify-center p-20"><div className="animate-spin rounded-full h-10 w-10 border-b-2 border-indigo-600"></div></div>;

    return (
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <h1 className="text-4xl font-black text-gray-900 dark:text-white mb-8">Каталог</h1>

            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                {categories.map(cat => (
                    <Link
                        key={cat.id}
                        to={`/catalog/${cat.id}`}
                        className="group relative h-48 rounded-3xl overflow-hidden shadow-sm hover:shadow-xl transition-all border border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800"
                    >
                        {/* Если есть картинка категории - показываем, иначе градиент */}
                        {cat.image_path ? (
                            <img src={cat.image_path} alt={cat.name} className="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" />
                        ) : (
                            <div className="absolute inset-0 bg-gradient-to-br from-indigo-500 to-purple-600 opacity-10 group-hover:opacity-20 transition-opacity"></div>
                        )}

                        <div className="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>

                        <div className="absolute bottom-0 left-0 p-6 w-full flex justify-between items-end">
                            <div>
                                <h3 className="text-2xl font-black text-white mb-1">{cat.name}</h3>
                                <p className="text-gray-300 text-sm font-bold flex items-center gap-2 group-hover:gap-4 transition-all">
                                    Смотреть товары <ArrowRight className="w-4 h-4" />
                                </p>
                            </div>
                        </div>
                    </Link>
                ))}
            </div>

            {categories.length === 0 && (
                <div className="text-center py-20">
                    <Tag className="w-12 h-12 text-gray-300 mx-auto mb-4" />
                    <p className="text-gray-500 font-bold">Категории еще не созданы</p>
                </div>
            )}
        </div>
    );
}
