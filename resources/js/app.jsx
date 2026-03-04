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
import Catalog from './pages/Catalog';
import LoginPage from './pages/LoginPage';
import CartPage from './pages/CartPage';
import AdminPage from './pages/AdminPage';
import SingleProductPage from './pages/SingleProductPage';
import CategoriesPage from './pages/CategoriesPage';
import CategoryProductsPage from './pages/CategoryProductsPage';
import SearchPage from './pages/SearchPage';
import AboutPage from './pages/AboutPage';
import TopUpPage from './pages/TopUpPage'; // Страница пополнения

function App() {
    return (
        <AuthProvider>
            <ThemeProvider>
                <BrowserRouter>
                    <div className="min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors duration-200">
                        <Navbar />

                        <div className="pt-20"> {/* Отступ для фиксированного навбара */}
                            <Routes>
                                {/* Публичные маршруты */}
                                <Route path="/" element={<Catalog />} />
                                <Route path="/catalog" element={<Catalog />} />
                                <Route path="/login" element={<LoginPage />} />

                                <Route path="/cart" element={<CartPage />} />
                                <Route path="/products/:id" element={<SingleProductPage />} />
                                <Route path="/categories" element={<CategoriesPage />} />
                                <Route path="/categories/:id" element={<CategoryProductsPage />} />
                                <Route path="/search" element={<SearchPage />} />
                                <Route path="/about" element={<AboutPage />} />

                                {/* Маршрут пополнения баланса */}
                                <Route path="/top-up" element={<TopUpPage />} />

                                {/* Защищенные маршруты (Админка) */}
                                <Route path="/admin" element={
                                    <AdminRoute>
                                        <AdminPage />
                                    </AdminRoute>
                                } />

                                {/* 404 */}
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

// ВАЖНОЕ ИСПРАВЛЕНИЕ: ищем элемент 'root', как в welcome.blade.php
const rootElement = document.getElementById('root');

if (rootElement) {
    ReactDOM.createRoot(rootElement).render(
        <React.StrictMode>
            <App />
        </React.StrictMode>
    );
} else {
    console.error("Не найден элемент с id='root' в welcome.blade.php");
}
