import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { ShoppingCart, Search, Zap, Sun, Moon, Wallet, Plus } from 'lucide-react'; // Добавил иконки
import UserDropdown from '../contexts/UserDropdown';
import { useTheme } from '../contexts/ThemeContext';
import { useAuth } from '../contexts/AuthContext'; // Импортируем AuthContext

export default function Navbar() {
    const { theme, toggleTheme } = useTheme();
    const { user } = useAuth(); // Достаем пользователя
    const [searchQuery, setSearchQuery] = useState('');
    const navigate = useNavigate();

    const handleSearch = (e) => {
        if (e.key === 'Enter' && searchQuery.trim()) {
            navigate(`/catalog?search=${encodeURIComponent(searchQuery)}`); // Исправил путь на каталог
            setSearchQuery('');
        }
    };

    return (
        <nav className="fixed top-0 left-0 right-0 z-50 bg-white/80 dark:bg-gray-900/80 backdrop-blur-md border-b border-gray-200 dark:border-gray-800 h-20 flex items-center">
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full flex justify-between items-center">

                <Link to="/" className="flex items-center gap-2">
                    <Zap className="h-8 w-8 text-indigo-600" />
                    <span className="font-black text-2xl tracking-tight text-gray-900 dark:text-white">
                        INNO<span className="text-indigo-600">SHOP</span>
                    </span>
                </Link>

                <div className="hidden md:flex flex-1 max-w-md mx-8 relative">
                    <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" />
                    <input
                        type="text"
                        className="w-full pl-10 pr-4 py-2 border rounded-full bg-gray-50 dark:bg-gray-800 dark:border-gray-700 focus:ring-2 focus:ring-indigo-500 outline-none transition-all text-gray-900 dark:text-white"
                        placeholder="Искать товары..."
                        value={searchQuery}
                        onChange={(e) => setSearchQuery(e.target.value)}
                        onKeyDown={handleSearch}
                    />
                </div>

                <div className="flex items-center gap-4 md:gap-6">
                    <Link to="/catalog" className="hidden md:block font-bold text-gray-700 dark:text-gray-300 hover:text-indigo-600 transition-colors">Каталог</Link>

                    {/* === БЛОК БАЛАНСА (Виден только если пользователь вошел) === */}
                    {user && (
                        <div className="hidden sm:flex items-center gap-2 px-3 py-1.5 bg-indigo-50 dark:bg-indigo-900/20 rounded-full border border-indigo-100 dark:border-indigo-800/50">
                            <Wallet className="w-4 h-4 text-indigo-600 dark:text-indigo-400" />
                            <span className="font-bold text-sm text-gray-900 dark:text-white tabular-nums">
                                {user.balance ? parseFloat(user.balance).toFixed(2) : '0.00'} BYN
                            </span>
                            <Link
                                to="/top-up"
                                className="ml-1 w-5 h-5 flex items-center justify-center bg-indigo-600 hover:bg-indigo-700 text-white rounded-full transition-colors"
                                title="Пополнить баланс"
                            >
                                <Plus className="w-3 h-3" />
                            </Link>
                        </div>
                    )}
                    {/* ========================================================== */}

                    <button
                        onClick={toggleTheme}
                        className="p-2 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:text-indigo-600 transition-all"
                        title={theme === 'light' ? 'Включить темную тему' : 'Включить светлую тему'}
                    >
                        {theme === 'light' ? <Moon className="h-5 w-5" /> : <Sun className="h-5 w-5" />}
                    </button>

                    <Link
                        to="/cart"
                        className="relative p-2 text-gray-600 dark:text-gray-400 hover:text-indigo-600 transition-colors"
                        title="Корзина"
                    >
                        <ShoppingCart className="h-6 w-6" />
                    </Link>

                    {/* Если юзер есть - дропдаун, если нет - кнопка Войти */}
                    {user ? (
                        <UserDropdown />
                    ) : (
                        <Link to="/login" className="font-bold text-gray-700 dark:text-gray-300 hover:text-indigo-600">Войти</Link>
                    )}
                </div>
            </div>
        </nav>
    );
}
