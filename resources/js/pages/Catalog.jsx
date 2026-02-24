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
                setProducts(response.data.data || response.data);
                setIsLoading(false);
            })
            .catch(error => {
                console.error("Ошибка API:", error);
                setIsLoading(false);
            });
    }, []);

    return (
        <div className="min-h-screen bg-gray-50 font-sans text-gray-900">
            {/* Навигация */}
            <nav className="sticky top-0 z-50 bg-white/80 backdrop-blur-md border-b border-gray-200">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="flex justify-between items-center h-20">
                        <Link to="/" className="flex-shrink-0 flex items-center gap-2 cursor-pointer">
                            <Zap className="h-8 w-8 text-indigo-600" />
                            <span className="font-black text-2xl tracking-tight text-gray-900">
                                INNO<span className="text-indigo-600">SHOP</span>
                            </span>
                        </Link>

                        <div className="hidden md:flex flex-1 max-w-md mx-8">
                            <div className="relative w-full">
                                <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <Search className="h-5 w-5 text-gray-400" />
                                </div>
                                <input type="text" className="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-full bg-gray-50 focus:bg-white focus:ring-2 focus:ring-indigo-500 outline-none transition-all" placeholder="Поиск товаров..." />
                            </div>
                        </div>

                        <div className="flex items-center gap-6">
                            <div className="hidden md:flex items-center gap-6 text-sm font-bold text-gray-700">
                                <Link to="/catalog" className="hover:text-indigo-600 transition-colors">Каталог</Link>
                                <Link to="/about" className="hover:text-indigo-600 transition-colors">О нас</Link>
                                <Link to="/login" className="hover:text-indigo-600 transition-colors">Войти</Link>
                            </div>
                            <button className="relative p-2 text-gray-600 hover:text-indigo-600 transition-colors">
                                <ShoppingCart className="h-6 w-6" />
                            </button>
                        </div>
                    </div>
                </div>
            </nav>

            {/* Баннер */}
            <div className="relative bg-black overflow-hidden">
                <img className="absolute inset-0 w-full h-full object-cover opacity-40" src="https://images.unsplash.com/photo-1441986300917-64674bd600d8?q=80&w=2070&auto=format&fit=crop" alt="Hero background" />
                <div className="relative max-w-7xl mx-auto py-24 px-4 sm:py-32 flex flex-col items-center text-center">
                    <h1 className="text-4xl font-extrabold text-white sm:text-6xl">Новая коллекция уже здесь</h1>
                    <p className="mt-6 text-xl text-gray-300 max-w-3xl">Премиальное качество, эксклюзивные дропы и бесплатная доставка.</p>
                    <div className="mt-10">
                        <Link to="/catalog" className="px-8 py-4 bg-white text-black font-bold rounded-full hover:bg-gray-100 transition-all shadow-lg inline-block">
                            Смотреть каталог
                        </Link>
                    </div>
                </div>
            </div>

            {/* Товары */}
            <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
                <h2 className="text-3xl font-extrabold text-gray-900 tracking-tight mb-10">Новинки</h2>

                {isLoading ? (
                    <div className="flex justify-center items-center h-64"><div className="animate-spin rounded-full h-16 w-16 border-t-2 border-indigo-600"></div></div>
                ) : (
                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                        {products.map((product) => (
                            <div key={product.id} className="bg-white rounded-3xl overflow-hidden border border-gray-100 shadow-sm hover:shadow-xl transition-all">
                                <div className="aspect-[4/5] bg-gray-200 relative">
                                    <img src={`https://picsum.photos/seed/${product.id}/600/800`} className="w-full h-full object-cover" alt={product.name}/>
                                </div>
                                <div className="p-6">
                                    <h3 className="text-lg font-bold text-gray-900 mb-2">{product.name}</h3>
                                    <div className="flex items-center justify-between mt-4">
                                        <span className="text-xl font-black text-gray-900">{product.price} ₽</span>
                                        <button className="bg-gray-900 text-white p-3 rounded-2xl hover:bg-indigo-600 transition-colors">
                                            <ShoppingCart className="h-5 w-5" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                )}
            </main>
        </div>
    );
}

export default Catalog;
