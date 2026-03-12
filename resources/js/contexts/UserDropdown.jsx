import React, { useState, useRef, useEffect } from 'react';
import { LogOut, Moon, Sun, LayoutDashboard, Settings, Package, Wallet } from 'lucide-react';
import { useAuth } from '../contexts/AuthContext';
import { useTheme } from '../contexts/ThemeContext';
import { Link } from 'react-router-dom';

export default function UserDropdown() {
    const { user, logout } = useAuth();
    const { theme, toggleTheme } = useTheme();
    const [isOpen, setIsOpen] = useState(false);
    const dropdownRef = useRef(null);

    useEffect(() => {
        const handleClickOutside = (event) => {
            if (dropdownRef.current && !dropdownRef.current.contains(event.target)) setIsOpen(false);
        };
        document.addEventListener('mousedown', handleClickOutside);
        return () => document.removeEventListener('mousedown', handleClickOutside);
    }, []);

    if (!user) {
        return (
            <div className="flex items-center gap-4 border-l pl-4 border-gray-200 dark:border-gray-700">
                <Link to="/login" className="font-bold text-gray-700 dark:text-gray-300 hover:text-indigo-600 transition-colors">
                    Вход
                </Link>
            </div>
        );
    }

    return (
        <div className="relative" ref={dropdownRef}>
            <button
                onClick={() => setIsOpen(!isOpen)}
                className="w-10 h-10 rounded-full bg-gradient-to-tr from-indigo-600 to-purple-600 text-white flex items-center justify-center font-black shadow-md hover:scale-105 transition-transform ring-2 ring-white dark:ring-gray-900"
                aria-label="Меню пользователя"
            >
                {user.name?.charAt(0).toUpperCase() || user.email?.charAt(0).toUpperCase() || '?'}
            </button>

            {isOpen && (
                <div className="absolute right-0 mt-3 w-56 bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 py-2 z-50 overflow-hidden">
                    <div className="px-4 py-3 border-b border-gray-100 dark:border-gray-700">
                        <p className="text-sm font-bold text-gray-900 dark:text-white">{user.name}</p>
                        <p className="text-xs text-gray-500 dark:text-gray-400 truncate">{user.email}</p>
                        <div className="mt-2 lg:hidden flex items-center gap-2 text-indigo-600 dark:text-indigo-400">
                            <Wallet className="w-3.5 h-3.5" />
                            <span className="text-xs font-black uppercase tracking-wider">{(parseFloat(user.balance) || 0).toFixed(2)} BYN</span>
                        </div>
                    </div>

                    {user.role === 'admin' && (
                        <Link
                            to="/admin"
                            onClick={() => setIsOpen(false)}
                            className="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                        >
                            <LayoutDashboard className="w-4 h-4" /> Админ Панель
                        </Link>
                    )}
                    <Link
                        to="/orders"
                        onClick={() => setIsOpen(false)}
                        className="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                    >
                        <Package className="w-4 h-4" /> История заказов
                    </Link>

                    <div className="border-t border-gray-100 dark:border-gray-700">
                        <div className="flex items-center gap-3 px-4 py-2 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            <Settings className="w-4 h-4" /> Настройки
                        </div>
                        <button
                            onClick={() => { toggleTheme(); setIsOpen(false); }}
                            className="w-full flex items-center gap-3 px-4 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                        >
                            {theme === 'dark' ? <Sun className="w-4 h-4" /> : <Moon className="w-4 h-4" />}
                            {theme === 'dark' ? 'Светлая тема' : 'Темная тема'}
                        </button>
                    </div>

                    <button
                        onClick={() => { logout(); setIsOpen(false); }}
                        className="w-full flex items-center gap-3 px-4 py-3 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors border-t border-gray-100 dark:border-gray-700"
                    >
                        <LogOut className="w-4 h-4" /> Выйти
                    </button>
                </div>
            )}
        </div>
    );
}
