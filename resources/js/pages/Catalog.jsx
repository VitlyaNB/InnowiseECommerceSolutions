import '../bootstrap';
import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { ShoppingCart, Search, Menu, Zap, ShieldCheck, Truck } from 'lucide-react';
import { Link } from 'react-router-dom';


function Catalog() {
    const [products, setProducts] = useState([]);
    const [isLoading, setIsLoading] = useState(true);

    useEffect(() => {
        axios.get('/api/products')
            .then(response => {
                setProducts(response.data);
                setIsLoading(false);
            })
            .catch(error => {
                console.error("Ошибка API:", error);
                setIsLoading(false);
            });
    }, []);

    return (
        <div className="min-h-screen bg-gray-50 font-sans text-gray-900 selection:bg-black selection:text-white">

            {/* Навигация (Navbar) */}
            <nav className="sticky top-0 z-50 bg-white/80 backdrop-blur-md border-b border-gray-200">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="flex justify-between items-center h-20">
                        {/* Логотип */}
                        <div className="flex-shrink-0 flex items-center gap-2 cursor-pointer">
                            <Zap className="h-8 w-8 text-indigo-600" />
                            <span className="font-black text-2xl tracking-tight text-gray-900">
                                INNO<span className="text-indigo-600">SHOP</span>
                            </span>
                        </div>

                        {/* Поиск */}
                        <div className="hidden md:flex flex-1 max-w-md mx-8">
                            <div className="relative w-full">
                                <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <Search className="h-5 w-5 text-gray-400" />
                                </div>
                                <input
                                    type="text"
                                    className="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-full leading-5 bg-gray-50 placeholder-gray-500 focus:outline-none focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition-all"
                                    placeholder="Поиск..."
                                />
                            </div>
                        </div>

                        {/* Ссылки и корзина */}
                        <div className="flex items-center gap-6">
                            <div className="hidden md:flex items-center gap-4 text-sm font-medium text-gray-700">
                                <Link to="/CategoriesPage.jsx" className="hover:text-indigo-600 transition-colors">Каталог</Link>
                                <a href="/CategoriesPage.jsx" className="hover:text-indigo-600 transition-colors">О нас</a>
                            </div>
                            <button className="relative p-2 text-gray-600 hover:text-indigo-600 transition-colors">
                                <ShoppingCart className="h-6 w-6" />
                                <span className="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/4 -translate-y-1/4 bg-red-500 rounded-full">
                                    3
                                </span>
                            </button>
                            <button className="md:hidden p-2 text-gray-600">
                                <Menu className="h-6 w-6" />
                            </button>
                        </div>
                    </div>
                </div>
            </nav>

            {/* Главный Баннер (Hero Section) */}
            <div className="relative bg-black overflow-hidden">
                <div className="absolute inset-0">
                    <img className="w-full h-full object-cover opacity-40" src="https://images.unsplash.com/photo-1441986300917-64674bd600d8?q=80&w=2070&auto=format&fit=crop" alt="Hero background" />
                </div>
                <div className="relative max-w-7xl mx-auto py-24 px-4 sm:py-32 sm:px-6 lg:px-8 flex flex-col items-center text-center">
                    <h1 className="text-4xl font-extrabold tracking-tight text-white sm:text-5xl lg:text-6xl">
                        Новая коллекция уже здесь
                    </h1>
                    <p className="mt-6 text-xl text-gray-300 max-w-3xl">
                        Премиальное качество, эксклюзивные дропы и бесплатная доставка.
                    </p>
                    <div className="mt-10 flex gap-4">
                        <button className="px-8 py-4 border border-transparent text-base font-bold rounded-full text-black bg-white hover:bg-gray-100 transition-colors shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                            Смотреть каталог
                        </button>
                    </div>
                </div>
            </div>

            {/* Преимущества */}
            <div className="bg-white border-b border-gray-200">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-8 text-center divide-y md:divide-y-0 md:divide-x divide-gray-200">
                        <div className="flex flex-col items-center pt-4 md:pt-0">
                            <Truck className="h-8 w-8 text-indigo-600 mb-3" />
                            <h3 className="font-bold text-gray-900">Быстрая доставка</h3>
                            <p className="text-sm text-gray-500 mt-1">По всей стране за 2-3 дня</p>
                        </div>
                        <div className="flex flex-col items-center pt-4 md:pt-0">
                            <ShieldCheck className="h-8 w-8 text-indigo-600 mb-3" />
                            <h3 className="font-bold text-gray-900">Оригинальные бренды</h3>
                            <p className="text-sm text-gray-500 mt-1">Гарантия качества 100%</p>
                        </div>
                        <div className="flex flex-col items-center pt-4 md:pt-0">
                            <Zap className="h-8 w-8 text-indigo-600 mb-3" />
                            <h3 className="font-bold text-gray-900">Эксклюзивные дропы</h3>
                            <p className="text-sm text-gray-500 mt-1">Будь первым</p>
                        </div>
                    </div>
                </div>
            </div>

            {/* Каталог товаров */}
            <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
                <div className="flex items-end justify-between mb-10">
                    <h2 className="text-3xl font-extrabold text-gray-900 tracking-tight">Популярное</h2>
                    <a href="#" className="text-sm font-semibold text-indigo-600 hover:text-indigo-500 hidden sm:block">Смотреть все &rarr;</a>
                </div>

                {isLoading ? (
                    <div className="flex justify-center items-center h-64">
                        <div className="animate-spin rounded-full h-16 w-16 border-t-2 border-b-2 border-indigo-600"></div>
                    </div>
                ) : (
                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                        {products.map((product) => (
                            <div key={product.id} className="group bg-white rounded-3xl overflow-hidden border border-gray-100 shadow-sm hover:shadow-2xl transition-all duration-300 flex flex-col">
                                <div className="aspect-[4/5] w-full overflow-hidden bg-gray-200 relative">
                                    <img
                                        src={`https://picsum.photos/seed/${product.id + 100}/600/800`}
                                        className="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-500"
                                    />
                                    {product.id % 3 === 0 && (
                                        <div className="absolute top-4 left-4 bg-white text-gray-900 text-xs font-black px-3 py-1 rounded-full uppercase tracking-wider shadow-md">
                                            New
                                        </div>
                                    )}
                                </div>
                                <div className="p-6 flex flex-col flex-1">
                                    <h3 className="text-lg font-bold text-gray-900 mb-2">{product.name}</h3>
                                    <p className="text-sm text-gray-500 line-clamp-2 mb-4 flex-1">
                                        {product.description || 'Идеальный выбор для вашего гардероба.'}
                                    </p>
                                    <div className="flex items-center justify-between mt-auto pt-4 border-t border-gray-100">
                                        <span className="text-xl font-black text-gray-900">{product.price} ₽</span>
                                        <button className="bg-gray-900 text-white p-3 rounded-2xl hover:bg-indigo-600 transition-colors shadow-md">
                                            <ShoppingCart className="h-5 w-5" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                )}
            </main>

            <footer className="bg-white border-t border-gray-200 py-12 text-center text-gray-400 text-sm">
                &copy; 2026 INNOSHOP. Все права защищены.
            </footer>
        </div>
    );
}

export default Catalog;
