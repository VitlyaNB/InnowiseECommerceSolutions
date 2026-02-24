import React from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { ShoppingCart, Search, Zap, LogOut, LayoutDashboard } from 'lucide-react';
import { useAuth } from '../contexts/AuthContext';

export default function Navbar() {
    const { user, logout } = useAuth();
    const navigate = useNavigate();

    const handleLogout = () => {
        logout();
        navigate('/');
    };

    return (
        <nav className="fixed top-0 left-0 right-0 z-50 bg-white/80 backdrop-blur-md border-b border-gray-200 h-20 flex items-center">
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full flex justify-between items-center">

                {/* Логотип */}
                <Link to="/" className="flex items-center gap-2">
                    <Zap className="h-8 w-8 text-indigo-600" />
                    <span className="font-black text-2xl tracking-tight text-gray-900">
                        INNO<span className="text-indigo-600">SHOP</span>
                    </span>
                </Link>

                {/* Поиск */}
                <div className="hidden md:flex flex-1 max-w-md mx-8 relative">
                    <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" />
                    <input
                        type="text"
                        className="w-full pl-10 pr-4 py-2 border rounded-full bg-gray-50 focus:bg-white focus:ring-2 focus:ring-indigo-500 outline-none transition-all"
                        placeholder="Искать товары..."
                    />
                </div>

                {/* Меню справа */}
                <div className="flex items-center gap-6">
                    <Link to="/catalog" className="hidden md:block font-bold text-gray-700 hover:text-indigo-600 transition-colors">Каталог</Link>
                    <Link to="/about" className="hidden md:block font-bold text-gray-700 hover:text-indigo-600 transition-colors">О нас</Link>

                    <button className="relative p-2 text-gray-600 hover:text-indigo-600 transition-colors">
                        <ShoppingCart className="h-6 w-6" />
                    </button>

                    {/* Если юзер залогинен */}
                    {user ? (
                        <div className="flex items-center gap-4 border-l pl-4 border-gray-200">
                            {/* Кнопка админки только для админа */}
                            {user.role === 'admin' && (
                                <Link to="/admin" className="flex items-center gap-2 text-sm font-bold text-indigo-600 bg-indigo-50 px-4 py-2 rounded-full hover:bg-indigo-100 transition-colors">
                                    <LayoutDashboard className="w-4 h-4" /> Панель
                                </Link>
                            )}

                            {/* Аватарка: кружок с первой буквой email */}
                            <div
                                title={user.email}
                                className="w-10 h-10 rounded-full bg-gradient-to-tr from-indigo-600 to-purple-600 text-white flex items-center justify-center font-black text-lg shadow-md cursor-pointer hover:scale-105 transition-transform"
                            >
                                {user.email.charAt(0).toUpperCase()}
                            </div>

                            {/* Выход */}
                            <button onClick={handleLogout} className="text-gray-400 hover:text-red-500 transition-colors" title="Выйти">
                                <LogOut className="w-5 h-5" />
                            </button>
                        </div>
                    ) : (
                        <div className="flex gap-4 border-l pl-4 border-gray-200">
                            <Link to="/login" className="font-bold text-gray-700 hover:text-indigo-600 transition-colors">Вход</Link>
                        </div>
                    )}
                </div>
            </div>
        </nav>
    );
}
