import './bootstrap';
import '../css/app.css';
import React from 'react';
import { createRoot } from 'react-dom/client';
import { BrowserRouter, Routes, Route } from 'react-router-dom';
import { AuthProvider } from './contexts/AuthContext';
import { ThemeProvider } from './contexts/ThemeContext';

import Navbar from './components/Navbar';
import Catalog from './pages/Catalog';
import CategoriesPage from './pages/CategoriesPage';
import CategoryProductsPage from './pages/CategoryProductsPage';
import SingleProductPage from './pages/SingleProductPage';
import CartPage from './pages/CartPage';
import AboutPage from './pages/AboutPage';
import LoginPage from './pages/LoginPage';
import AdminPage from './pages/AdminPage';
import AdminRoute from './components/AdminRoute';
import CookieConsent from './components/CookieConsent';
import SearchPage from './pages/SearchPage'; // <--- 1. Добавил импорт

function App() {
    return (
        <AuthProvider>
            <ThemeProvider>
                <BrowserRouter>
                    <Navbar />
                    <div className="pt-20 min-h-screen bg-white dark:bg-gray-900 transition-colors">
                        <Routes>
                            <Route path="/" element={<Catalog />} />
                            <Route path="/catalog" element={<CategoriesPage />} />
                            <Route path="/catalog/:categoryId" element={<CategoryProductsPage />} />
                            <Route path="/product/:productId" element={<SingleProductPage />} />
                            <Route path="/cart" element={<CartPage />} />
                            <Route path="/about" element={<AboutPage />} />
                            <Route path="/login" element={<LoginPage />} />

                            {/* 2. Добавил маршрут для поиска */}
                            <Route path="/search" element={<SearchPage />} />

                            <Route path="/admin" element={
                                <AdminRoute>
                                    <AdminPage />
                                </AdminRoute>
                            } />
                        </Routes>
                    </div>
                    <CookieConsent />
                </BrowserRouter>
            </ThemeProvider>
        </AuthProvider>
    );
}

const container = document.getElementById('root');
if (container) {
    const root = createRoot(container);
    root.render(<App />);
}
