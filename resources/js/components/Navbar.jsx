import React from 'react';
import { Link } from 'react-router-dom';
import { ShoppingCart, Search, Zap, Sun, Moon } from 'lucide-react'; // Добавили иконки солнца и луны
import UserDropdown from '../contexts/UserDropdown';
import { useTheme } from '../contexts/ThemeContext'; // Импортируем хук темы

export default function Navbar() {
    const { theme, toggleTheme } = useTheme(); // Получаем текущую тему и функцию переключения

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
                    />
                </div>

                <div className="flex items-center gap-6">
                    <Link to="/catalog" className="hidden md:block font-bold text-gray-700 dark:text-gray-300 hover:text-indigo-600 transition-colors">Каталог</Link>

                    {/* Кнопка переключения темы */}
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

                    <UserDropdown />
                </div>
            </div>
        </nav>
    );
}
