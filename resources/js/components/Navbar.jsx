import React, { useState, useEffect, useMemo, useRef } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { ShoppingCart, Search, Zap, Sun, Moon, Wallet, Plus, Filter, X } from 'lucide-react';
import Fuse from 'fuse.js';
import api from '../api';
import UserDropdown from '../contexts/UserDropdown';
import { useTheme } from '../contexts/ThemeContext';
import { useAuth } from '../contexts/AuthContext';

export default function Navbar() {
    const { theme, toggleTheme } = useTheme();
    const { user } = useAuth();
    const [searchQuery, setSearchQuery] = useState('');
    const [products, setProducts] = useState([]);
    const [suggestions, setSuggestions] = useState([]);
    const [showDropdown, setShowDropdown] = useState(false);
    const [loadingProducts, setLoadingProducts] = useState(false);
    const navigate = useNavigate();
    const dropdownRef = useRef(null);

    useEffect(() => {
        setLoadingProducts(true);
        api.get('/products?per_page=100')
            .then(res => {
                const items = res.data.data || res.data || [];
                setProducts(items);
            })
            .catch(() => setProducts([]))
            .finally(() => setLoadingProducts(false));
    }, []);

    const fuse = useMemo(() => new Fuse(products, {
        keys: ['name', 'description'],
        threshold: 0.4,
        includeScore: true,
        minMatchCharLength: 2,
    }), [products]);

    useEffect(() => {
        if (searchQuery.trim().length >= 2) {
            const results = fuse.search(searchQuery).slice(0, 6);
            setSuggestions(results.map(r => r.item));
            setShowDropdown(true);
        } else {
            setSuggestions([]);
            setShowDropdown(false);
        }
    }, [searchQuery, fuse]);

    useEffect(() => {
        const handleClickOutside = (e) => {
            if (dropdownRef.current && !dropdownRef.current.contains(e.target)) {
                setShowDropdown(false);
            }
        };
        document.addEventListener('mousedown', handleClickOutside);
        return () => document.removeEventListener('mousedown', handleClickOutside);
    }, []);

    const handleSearch = (e) => {
        if (e.key === 'Enter' && searchQuery.trim()) {
            navigate(`/search?q=${encodeURIComponent(searchQuery)}`);
            setSearchQuery('');
            setShowDropdown(false);
        }
    };

    const handleSuggestionClick = (productId) => {
        navigate(`/product/${productId}`);
        setSearchQuery('');
        setShowDropdown(false);
    };

    return (
        <nav className="fixed top-0 left-0 right-0 z-50 bg-slate-100/90 dark:bg-slate-950/90 backdrop-blur-xl border-b border-slate-200 dark:border-slate-800 h-20 flex items-center transition-colors duration-300">
            <div className="max-w-7xl mx-auto px-4 w-full flex justify-between items-center gap-4">

                {/* Логотип */}
                <Link to="/" className="flex items-center gap-2 group flex-shrink-0">
                    <div className="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-500/30 group-hover:scale-110 transition-transform duration-300">
                        <Zap className="h-6 w-6 text-white" />
                    </div>
                    <span className="font-black text-2xl tracking-tighter text-slate-800 dark:text-white hidden sm:block">
                        INNO<span className="text-indigo-600">SHOP</span>
                    </span>
                </Link>

                {/* Поиск с Fuse.js */}
                <div className="flex-1 max-w-xl relative mx-4 hidden md:block" ref={dropdownRef}>
                    <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" />
                    <input
                        type="text"
                        className="w-full pl-10 pr-4 py-2.5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none transition-all dark:text-white shadow-sm"
                        placeholder="Поиск товаров..."
                        value={searchQuery}
                        onChange={(e) => setSearchQuery(e.target.value)}
                        onKeyDown={handleSearch}
                        onFocus={() => searchQuery.length >= 2 && setShowDropdown(true)}
                    />
                    {searchQuery && (
                        <button
                            onClick={() => { setSearchQuery(''); setShowDropdown(false); }}
                            className="absolute right-24 top-1/2 -translate-y-1/2 p-1 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-full"
                        >
                            <X className="w-4 h-4 text-gray-400" />
                        </button>
                    )}
                    <button
                        onClick={() => window.dispatchEvent(new CustomEvent('open-filters'))}
                        className="absolute right-2 top-1/2 -translate-y-1/2 inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-slate-900 text-white dark:bg-white dark:text-slate-900 font-bold text-xs hover:scale-105 transition-transform shadow-lg"
                        type="button"
                    >
                        <Filter className="w-4 h-4" />
                        Фильтры
                    </button>

                    {/* Выпадающий список результатов */}
                    {showDropdown && suggestions.length > 0 && (
                        <div className="absolute top-full left-0 right-0 mt-2 bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 overflow-hidden z-50">
                            {suggestions.map(product => (
                                <button
                                    key={product.id}
                                    onClick={() => handleSuggestionClick(product.id)}
                                    className="w-full flex items-center gap-3 p-3 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors text-left"
                                >
                                    {product.images && product.images[0]?.url ? (
                                        <img src={product.images[0].url} className="w-10 h-10 rounded-lg object-cover" alt="" />
                                    ) : (
                                        <div className="w-10 h-10 rounded-lg bg-slate-200 dark:bg-slate-600 flex items-center justify-center text-xs text-slate-400">IMG</div>
                                    )}
                                    <div className="flex-1 min-w-0">
                                        <p className="font-bold text-sm text-slate-900 dark:text-white truncate">{product.name}</p>
                                        <p className="text-xs text-slate-500 dark:text-slate-400">{parseFloat(product.price).toFixed(2)} BYN</p>
                                    </div>
                                </button>
                            ))}
                            <button
                                onClick={() => { navigate(`/search?q=${encodeURIComponent(searchQuery)}`); setShowDropdown(false); }}
                                className="w-full p-3 text-center text-sm font-bold text-indigo-600 hover:bg-slate-50 dark:hover:bg-slate-700 border-t border-slate-100 dark:border-slate-700"
                            >
                                Все результаты для "{searchQuery}"
                            </button>
                        </div>
                    )}
                </div>

                <div className="flex items-center gap-3 sm:gap-5 flex-shrink-0">
                    {/* Ссылка на категории */}
                    <Link to="/catalog" className="hidden lg:block font-bold text-slate-600 dark:text-slate-300 hover:text-indigo-600 transition-colors">
                        Каталог
                    </Link>

                    {/* Баланс */}
                    {user && (
                        <div className="hidden lg:flex items-center gap-2 px-3 py-1.5 bg-white dark:bg-slate-900 rounded-full border border-slate-200 dark:border-slate-700 shadow-sm">
                            <Wallet className="w-4 h-4 text-indigo-600 dark:text-indigo-400" />
                            <span className="font-bold text-sm text-slate-900 dark:text-white">
                                {(parseFloat(user.balance) || 0).toFixed(2)}
                            </span>
                            <Link to="/top-up" className="ml-1 hover:text-indigo-600 p-1 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-full transition-colors"><Plus className="w-3 h-3" /></Link>
                        </div>
                    )}

                    <div className="h-6 w-[1px] bg-slate-300 dark:bg-slate-700 hidden sm:block"></div>

                    <button onClick={toggleTheme} className="p-2 text-slate-500 hover:text-indigo-600 hover:bg-white dark:hover:bg-slate-800 rounded-xl transition-all shadow-sm">
                        {theme === 'light' ? <Moon className="h-5 w-5" /> : <Sun className="h-5 w-5" />}
                    </button>

                    <Link to="/cart" className="relative p-2 text-slate-500 hover:text-indigo-600 hover:bg-white dark:hover:bg-slate-800 rounded-xl transition-all shadow-sm">
                        <ShoppingCart className="h-5 w-5" />
                    </Link>

                    {user ? (
                        <UserDropdown />
                    ) : (
                        <Link to="/login" className="px-5 py-2.5 bg-slate-900 dark:bg-white text-white dark:text-slate-900 rounded-xl font-bold text-sm hover:shadow-lg hover:-translate-y-0.5 transition-all">
                            Войти
                        </Link>
                    )}
                </div>
            </div>
        </nav>
    );
}
