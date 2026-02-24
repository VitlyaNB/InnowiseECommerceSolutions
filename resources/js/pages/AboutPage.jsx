import React from 'react';
import { Link } from 'react-router-dom';
import { ArrowLeft, Users, Star } from 'lucide-react';

export default function AboutPage() {
    return (
        <div className="min-h-screen bg-gray-50">
            <div className="bg-white px-8 py-6 border-b border-gray-200">
                <div className="max-w-7xl mx-auto flex items-center gap-4">
                    <Link to="/" className="p-2 hover:bg-gray-100 rounded-full transition-colors"><ArrowLeft className="w-6 h-6 text-gray-900" /></Link>
                    <h1 className="text-2xl font-black text-gray-900">О компании</h1>
                </div>
            </div>

            <div className="max-w-5xl mx-auto px-4 py-12">
                {/* Блок о владельцах */}
                <div className="bg-white rounded-3xl p-8 md:p-12 shadow-sm border border-gray-100 mb-12">
                    <div className="flex items-center gap-4 mb-8">
                        <Users className="w-8 h-8 text-indigo-600" />
                        <h2 className="text-3xl font-bold">Наша команда</h2>
                    </div>
                    <div className="grid md:grid-cols-2 gap-8">
                        <div className="flex flex-col items-center text-center">
                            <div className="w-48 h-48 rounded-full bg-gray-200 overflow-hidden mb-4 border-4 border-indigo-50">
                                {/* ЗАГЛУШКА: Вставь сюда реальное фото */}
                                <img src="https://picsum.photos/seed/founder1/400" className="w-full h-full object-cover" alt="Founder 1" />
                            </div>
                            <h3 className="text-xl font-bold">Иван Иванов</h3>
                            <p className="text-gray-500">Основатель, CEO</p>
                        </div>
                        <div className="flex flex-col items-center text-center">
                            <div className="w-48 h-48 rounded-full bg-gray-200 overflow-hidden mb-4 border-4 border-indigo-50">
                                {/* ЗАГЛУШКА: Вставь сюда реальное фото */}
                                <img src="https://picsum.photos/seed/founder2/400" className="w-full h-full object-cover" alt="Founder 2" />
                            </div>
                            <h3 className="text-xl font-bold">Анна Смирнова</h3>
                            <p className="text-gray-500">Арт-директор</p>
                        </div>
                    </div>
                </div>

                {/* Блок с отзывами */}
                <div className="bg-white rounded-3xl p-8 md:p-12 shadow-sm border border-gray-100">
                    <div className="flex items-center gap-4 mb-8">
                        <Star className="w-8 h-8 text-indigo-600" />
                        <h2 className="text-3xl font-bold">Отзывы клиентов</h2>
                    </div>
                    <div className="grid md:grid-cols-2 gap-6">
                        <div className="p-6 bg-gray-50 rounded-2xl">
                            <div className="flex text-yellow-400 mb-3"><Star fill="currentColor"/><Star fill="currentColor"/><Star fill="currentColor"/><Star fill="currentColor"/><Star fill="currentColor"/></div>
                            <p className="text-gray-700 italic">"Лучший магазин! Качество вещей просто на высоте, доставка заняла всего два дня."</p>
                            <p className="mt-4 font-bold text-sm text-gray-900">- Максим, Москва</p>
                        </div>
                        <div className="p-6 bg-gray-50 rounded-2xl">
                            <div className="flex text-yellow-400 mb-3"><Star fill="currentColor"/><Star fill="currentColor"/><Star fill="currentColor"/><Star fill="currentColor"/><Star fill="currentColor"/></div>
                            <p className="text-gray-700 italic">"Долго искал нормальные инструменты по адекватной цене. INNOSHOP выручил!"</p>
                            <p className="mt-4 font-bold text-sm text-gray-900">- Олег, Казань</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
