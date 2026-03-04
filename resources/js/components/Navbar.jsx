import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { ShoppingCart, Search, Zap, Sun, Moon, Wallet, Plus } from 'lucide-react';
import UserDropdown from '../contexts/UserDropdown';
import { useTheme } from '../contexts/ThemeContext';
import { useAuth } from '../contexts/AuthContext';

export default function Navbar() {
    const { theme, toggleTheme } = useTheme();
    const { user } = useAuth();
    const [searchQuery, setSearchQuery] = useState('');
    const navigate = useNavigate();

    const handleSearch = (e) => {
        if (e.key === 'Enter' && searchQuery.trim()) {
            navigate(`/search?q=${encodeURIComponent(searchQuery)}`);
            setSearchQuery('');
        }
    };

    return (
        <nav className="fixed top-0 left-0 right-0 z-50 bg-white/80 dark:bg-gray-900/80 backdrop-blur-md border-b border-gray-200 dark:border-gray-800 h-20 flex items-center">
            <div className="max-w-7xl mx-auto px-4 w-full flex justify-between items-center gap-4">

                {/* Логотип -> Ведет на Главную (Товары) */}
                <Link to="/" className="flex items-center gap-2 group flex-shrink-0">
                    <Zap className="h-8 w-8 text-indigo-600 group-hover:scale-110 transition-transform" />
                    <span className="font-black text-2xl tracking-tighter text-gray-900 dark:text-white hidden sm:block">
                        INNO<span className="text-indigo-600">SHOP</span>
                    </span>
                </Link>

                {/* Поиск */}
                <div className="flex-1 max-w-lg relative mx-4">
                    <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" />
                    <input
                        type="text"
                        className="w-full pl-10 pr-4 py-2.5 rounded-full bg-gray-100 dark:bg-gray-800 border-transparent focus:bg-white dark:focus:bg-gray-900 focus:ring-2 focus:ring-indigo-500 outline-none transition-all dark:text-white"
                        placeholder="Поиск товаров..."
                        value={searchQuery}
                        onChange={(e) => setSearchQuery(e.target.value)}
                        onKeyDown={handleSearch}
                    />
                </div>

                <div className="flex items-center gap-3 sm:gap-6 flex-shrink-0">
                    {/* Ссылка на категории */}
                    <Link to="/catalog" className="hidden md:block font-bold text-gray-600 dark:text-gray-300 hover:text-indigo-600 transition-colors">
                        Каталог
                    </Link>

                    {/* Баланс */}
                    {user && (
                        <div className="hidden lg:flex items-center gap-2 px-3 py-1.5 bg-indigo-50 dark:bg-indigo-900/20 rounded-full border border-indigo-100 dark:border-indigo-800">
                            <Wallet className="w-4 h-4 text-indigo-600 dark:text-indigo-400" />
                            <span className="font-bold text-sm text-gray-900 dark:text-white">
                                {user.balance ? parseFloat(user.balance).toFixed(2) : '0.00'}
                            </span>
                            <Link to="/top-up" className="ml-1 hover:text-indigo-600"><Plus className="w-4 h-4" /></Link>
                        </div>
                    )}

                    <button onClick={toggleTheme} className="p-2 text-gray-500 hover:text-indigo-600 transition-colors">
                        {theme === 'light' ? <Moon className="h-5 w-5" /> : <Sun className="h-5 w-5" />}
                    </button>

                    <Link to="/cart" className="relative p-2 text-gray-500 hover:text-indigo-600 transition-colors">
                        <ShoppingCart className="h-6 w-6" />
                    </Link>

                    {user ? (
                        <UserDropdown />
                    ) : (
                        <Link to="/login" className="px-4 py-2 bg-indigo-600 text-white rounded-lg font-bold hover:bg-indigo-700 text-sm">
                            Войти
                        </Link>
                    )}
                </div>
            </div>
        </nav>
    );
}
