import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { ShoppingCart, ShieldCheck, Truck, Zap } from 'lucide-react';
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
            {/* Баннер */}
            <div className="relative bg-black overflow-hidden">
                <img className="absolute inset-0 w-full h-full object-cover opacity-40" src="https://images.unsplash.com/photo-1441986300917-64674bd600d8?q=80&w=2070&auto=format&fit=crop" alt="Hero background" />
                <div className="relative max-w-7xl mx-auto py-24 px-4 sm:py-32 flex flex-col items-center text-center">
                    <h1 className="text-4xl font-extrabold text-white sm:text-6xl tracking-tight">Новая коллекция уже здесь</h1>
                    <p className="mt-6 text-xl text-gray-300 max-w-3xl">Премиальное качество, эксклюзивные дропы и бесплатная доставка.</p>
                    <div className="mt-10">
                        <Link to="/catalog" className="px-8 py-4 bg-white text-black font-bold rounded-full hover:bg-gray-100 transition-all shadow-lg inline-block hover:-translate-y-1">
                            Смотреть весь каталог
                        </Link>
                    </div>
                </div>
            </div>

            {/* Преимущества */}
            <div className="bg-white border-b border-gray-200">
                <div className="max-w-7xl mx-auto px-4 py-10">
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-8 text-center divide-y md:divide-y-0 md:divide-x divide-gray-200">
                        <div className="flex flex-col items-center pt-4 md:pt-0"><Truck className="h-8 w-8 text-indigo-600 mb-3" /><h3 className="font-bold">Быстрая доставка</h3></div>
                        <div className="flex flex-col items-center pt-4 md:pt-0"><ShieldCheck className="h-8 w-8 text-indigo-600 mb-3" /><h3 className="font-bold">Оригинальные бренды</h3></div>
                        <div className="flex flex-col items-center pt-4 md:pt-0"><Zap className="h-8 w-8 text-indigo-600 mb-3" /><h3 className="font-bold">Эксклюзивные дропы</h3></div>
                    </div>
                </div>
            </div>

            {/* Товары */}
            <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
                <h2 className="text-3xl font-extrabold text-gray-900 mb-10 tracking-tight">Новинки</h2>

                {isLoading ? (
                    <div className="flex justify-center items-center h-64"><div className="animate-spin rounded-full h-16 w-16 border-t-2 border-indigo-600"></div></div>
                ) : (
                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                        {products.map((product) => (
                            <Link key={product.id} to={`/product/${product.id}`} className="group bg-white rounded-3xl overflow-hidden border border-gray-100 shadow-sm hover:shadow-xl transition-all flex flex-col">
                                <div className="aspect-[4/5] bg-gray-200 relative overflow-hidden">
                                    <img src={`https://picsum.photos/seed/${product.id}/600/800`} className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" alt={product.name}/>
                                </div>
                                <div className="p-6 flex flex-col flex-1">
                                    <h3 className="text-lg font-bold text-gray-900 mb-2">{product.name}</h3>
                                    <div className="flex items-center justify-between mt-auto pt-4 border-t border-gray-100">
                                        <span className="text-xl font-black text-gray-900">{product.price} ₽</span>
                                        <button className="bg-gray-900 text-white p-3 rounded-2xl hover:bg-indigo-600 transition-colors shadow-md" onClick={(e) => e.preventDefault()}>
                                            <ShoppingCart className="h-5 w-5" />
                                        </button>
                                    </div>
                                </div>
                            </Link>
                        ))}
                    </div>
                )}
            </main>
        </div>
    );
}

export default Catalog;
