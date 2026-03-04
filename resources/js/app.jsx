import './bootstrap';
import '../css/app.css';

import React from 'react';
import ReactDOM from 'react-dom/client';
import { BrowserRouter, Routes, Route } from 'react-router-dom';

// Contexts
import { AuthProvider } from './contexts/AuthContext';
import { ThemeProvider } from './contexts/ThemeContext';

// Layout
import Navbar from './components/Navbar';
import CookieConsent from './components/CookieConsent';
import AdminRoute from './components/AdminRoute';

// Pages
import Catalog from './pages/Catalog';             // Главная (Витрина товаров)
import CategoriesPage from './pages/CategoriesPage'; // Каталог (Список категорий)
import CategoryProductsPage from './pages/CategoryProductsPage'; // Товары категории
import SingleProductPage from './pages/SingleProductPage'; // Один товар

import LoginPage from './pages/LoginPage';
import CartPage from './pages/CartPage';
import SearchPage from './pages/SearchPage';
import AboutPage from './pages/AboutPage';
import TopUpPage from './pages/TopUpPage';
import AdminPage from './pages/AdminPage';

function App() {
    return (
        <AuthProvider>
            <ThemeProvider>
                <BrowserRouter>
                    <div className="min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors duration-200">
                        <Navbar />

                        <div className="pt-20">
                            <Routes>
                                {/* 1. Главная страница = Товары */}
                                <Route path="/" element={<Catalog />} />

                                {/* 2. Страница Каталога = Категории */}
                                <Route path="/catalog" element={<CategoriesPage />} />

                                {/* 3. Страница товаров конкретной категории */}
                                <Route path="/catalog/:id" element={<CategoryProductsPage />} />

                                {/* 4. Страница конкретного товара */}
                                <Route path="/product/:id" element={<SingleProductPage />} />

                                {/* Служебные страницы */}
                                <Route path="/cart" element={<CartPage />} />
                                <Route path="/search" element={<SearchPage />} />
                                <Route path="/login" element={<LoginPage />} />
                                <Route path="/top-up" element={<TopUpPage />} />
                                <Route path="/about" element={<AboutPage />} />
                                {/* <Route path="/register" element={<RegisterPage />} /> */}

                                {/* Админка */}
                                <Route path="/admin" element={
                                    <AdminRoute>
                                        <AdminPage />
                                    </AdminRoute>
                                } />

                                <Route path="*" element={
                                    <div className="flex flex-col items-center justify-center h-[60vh]">
                                        <h1 className="text-4xl font-bold text-gray-400">404</h1>
                                        <p className="text-gray-500">Страница не найдена</p>
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
