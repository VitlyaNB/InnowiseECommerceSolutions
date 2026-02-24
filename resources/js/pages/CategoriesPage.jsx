import React, { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import axios from 'axios';
import { LayoutGrid, ArrowRight } from 'lucide-react';

export default function CategoriesPage() {
    const [categories, setCategories] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        axios.get('/api/categories')
            .then(res => {
                setCategories(res.data.data || res.data);
                setLoading(false);
            });
    }, []);

    return (
        <div className="min-h-screen bg-gray-50 p-8">
            <div className="max-w-7xl mx-auto">
                <div className="flex items-center gap-3 mb-12">
                    <LayoutGrid className="w-8 h-8 text-indigo-600" />
                    <h1 className="text-3xl font-black text-gray-900">Категории товаров</h1>
                </div>

                {loading ? (
                    <div className="flex justify-center py-20"><div className="animate-spin rounded-full h-12 w-12 border-t-2 border-indigo-600"></div></div>
                ) : (
                    <div className="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">
                        {categories.map(category => (
                            <Link
                                key={category.id}
                                to={`/catalog/${category.id}`}
                                className="group relative bg-white p-8 rounded-3xl border border-gray-100 shadow-sm hover:shadow-xl transition-all overflow-hidden"
                            >
                                <div className="relative z-10">
                                    <h3 className="text-xl font-bold text-gray-900 group-hover:text-indigo-600 transition-colors">
                                        {category.name}
                                    </h3>
                                    <p className="text-gray-400 mt-2 text-sm">Перейти в раздел</p>
                                </div>
                                <ArrowRight className="absolute bottom-6 right-6 w-6 h-6 text-gray-200 group-hover:text-indigo-600 group-hover:translate-x-2 transition-all" />
                                <div className="absolute -top-10 -right-10 w-32 h-32 bg-indigo-50 rounded-full opacity-0 group-hover:opacity-100 transition-opacity" />
                            </Link>
                        ))}
                    </div>
                )}
            </div>
        </div>
    );
}
