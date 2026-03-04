import './bootstrap';
import '../css/app.css';

import React from 'react';
import ReactDOM from 'react-dom/client';
import { BrowserRouter, Routes, Route } from 'react-router-dom';

// Контексты
import { AuthProvider } from './contexts/AuthContext';
import { ThemeProvider } from './contexts/ThemeContext';

// Компоненты
import Navbar from './components/Navbar';
import CookieConsent from './components/CookieConsent';
import AdminRoute from './components/AdminRoute';

// Страницы
import Catalog from './pages/Catalog'; // Это будет "Главная" с товарами
import CategoriesPage from './pages/CategoriesPage'; // Это будет "Каталог" с категориями
import CategoryProductsPage from './pages/CategoryProductsPage'; // Товары внутри категории

import LoginPage from './pages/LoginPage';
import CartPage from './pages/CartPage';
import AdminPage from './pages/AdminPage';
import SingleProductPage from './pages/SingleProductPage';
import SearchPage from './pages/SearchPage';
import AboutPage from './pages/AboutPage';
import TopUpPage from './pages/TopUpPage';

function App() {
    return (
        <AuthProvider>
            <ThemeProvider>
                <BrowserRouter>
                    <div className="min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors duration-200">
                        <Navbar />

                        <div className="pt-20">
                            <Routes>
                                {/* 1. ГЛАВНАЯ: Показываем товары (компонент Catalog) */}
                                <Route path="/" element={<Catalog />} />

                                {/* 2. КАТАЛОГ: Показываем категории */}
                                <Route path="/catalog" element={<CategoriesPage />} />

                                {/* 3. ВНУТРЬ КАТЕГОРИИ: Показываем товары конкретной категории */}
                                <Route path="/catalog/:id" element={<CategoryProductsPage />} />

                                {/* Остальные маршруты */}
                                <Route path="/products/:id" element={<SingleProductPage />} />
                                <Route path="/login" element={<LoginPage />} />
                                <Route path="/cart" element={<CartPage />} />
                                <Route path="/about" element={<AboutPage />} />
                                <Route path="/search" element={<SearchPage />} />
                                <Route path="/top-up" element={<TopUpPage />} />

                                {/* Админка */}
                                <Route path="/admin" element={
                                    <AdminRoute>
                                        <AdminPage />
                                    </AdminRoute>
                                } />

                                <Route path="*" element={
                                    <div className="flex flex-col items-center justify-center h-[60vh] text-center">
                                        <h1 className="text-4xl font-bold text-gray-800 dark:text-white mb-4">404</h1>
                                        <p className="text-gray-600 dark:text-gray-400">Страница не найдена</p>
                                    </div>
                                } />
                            </Routes>
                        </div>

                        <CookieConsent />
                    </div>
                </BrowserRouter>
            </ThemeProvider>
        </AuthProvider>
    );
}

const rootElement = document.getElementById('root');
if (rootElement) {
    ReactDOM.createRoot(rootElement).render(
        <React.StrictMode>
            <App />
        </React.StrictMode>
    );
}
