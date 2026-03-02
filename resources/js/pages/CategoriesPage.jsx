import React, { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import axios from 'axios';
import { ArrowLeft, LayoutGrid } from 'lucide-react';
import ImageWithFallback from '../components/ImageWithFallback';

export default function CategoriesPage() {
    const [categories, setCategories] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        axios.get('/api/categories')
            .then(res => {
                // Laravel Resource оборачивает данные в объект data
                setCategories(res.data.data || res.data);
                setLoading(false);
            })
            .catch(err => {
                console.error(err);
                setLoading(false);
            });
    }, []);

    return (
        <div className="min-h-screen bg-gray-50 dark:bg-gray-900 pb-16 transition-colors duration-300">
            <div className="bg-white dark:bg-gray-800 px-8 py-6 mb-8 border-b border-gray-200 dark:border-gray-700 sticky top-20 z-40 shadow-sm">
                <div className="max-w-7xl mx-auto flex items-center gap-4">
                    <Link to="/" className="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full transition-colors">
                        <ArrowLeft className="w-6 h-6 text-gray-900 dark:text-white" />
                    </Link>
                    <LayoutGrid className="w-6 h-6 text-indigo-600" />
                    <h1 className="text-2xl font-black text-gray-900 dark:text-white">Все категории</h1>
                </div>
            </div>

            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                {loading ? (
                    <div className="flex justify-center items-center h-64"><div className="animate-spin rounded-full h-16 w-16 border-t-2 border-indigo-600"></div></div>
                ) : (
                    <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                        {categories.map((cat) => (
                            <Link key={cat.id} to={`/catalog/${cat.id}`} className="group block bg-white dark:bg-gray-800 rounded-3xl overflow-hidden shadow-sm hover:shadow-xl transition-all border border-gray-100 dark:border-gray-700">
                                <div className="aspect-square overflow-hidden relative bg-gray-200 dark:bg-gray-700">
                                    <ImageWithFallback src={cat.image_path} alt={cat.name} className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" />
                                    <div className="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
                                    <div className="absolute bottom-4 left-4 right-4 text-white font-black text-lg md:text-xl leading-tight uppercase">
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
