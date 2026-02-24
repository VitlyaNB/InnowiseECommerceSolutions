import React from 'react';
import { Link } from 'react-router-dom';
import { ArrowLeft, LayoutGrid } from 'lucide-react';

export default function CategoriesPage() {
    // Временные категории для визуализации в стиле Таобао
    const customCategories = [
        { id: 1, name: "Мужская одежда", img: "https://images.unsplash.com/photo-1516257984-b1b4d707412e?w=500&q=80" },
        { id: 2, name: "Обувь", img: "https://images.unsplash.com/photo-1549298916-b41d501d3772?w=500&q=80" },
        { id: 3, name: "Товары для дома", img: "https://images.unsplash.com/photo-1616046229478-9901c5536a45?w=500&q=80" },
        { id: 4, name: "Инструменты", img: "https://images.unsplash.com/photo-1586864387967-d02ef85d93e8?w=500&q=80" },
        { id: 5, name: "Товары 18+", img: "https://images.unsplash.com/photo-1512413914402-45e0d4cfa24b?w=500&q=80" },
        { id: 6, name: "Электроника", img: "https://images.unsplash.com/photo-1498049794561-7780e7231661?w=500&q=80" },
    ];

    return (
        <div className="min-h-screen bg-gray-50 pb-16">
            {/* Простая шапка для возврата */}
            <div className="bg-white px-8 py-6 mb-8 border-b border-gray-200 sticky top-0 z-50">
                <div className="max-w-7xl mx-auto flex items-center gap-4">
                    <Link to="/" className="p-2 hover:bg-gray-100 rounded-full transition-colors">
                        <ArrowLeft className="w-6 h-6 text-gray-900" />
                    </Link>
                    <LayoutGrid className="w-6 h-6 text-indigo-600" />
                    <h1 className="text-2xl font-black text-gray-900">Все категории</h1>
                </div>
            </div>

            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
                    {customCategories.map((cat) => (
                        <Link key={cat.id} to={`/catalog/${cat.id}`} className="group block bg-white rounded-3xl overflow-hidden shadow-sm hover:shadow-xl transition-all border border-gray-100">
                            <div className="aspect-square overflow-hidden relative">
                                <img src={cat.img} alt={cat.name} className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" />
                                <div className="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent"></div>
                                <div className="absolute bottom-4 left-4 right-4 text-white font-bold text-lg leading-tight">
                                    {cat.name}
                                </div>
                            </div>
                        </Link>
                    ))}
                </div>
            </div>
        </div>
    );
}
