import './bootstrap';
import '../css/app.css';
import React from 'react';
import { createRoot } from 'react-dom/client';
import { BrowserRouter, Routes, Route } from 'react-router-dom';
import { AuthProvider } from './contexts/AuthContext';

import Navbar from './components/Navbar';
import Catalog from './pages/Catalog';
import CategoriesPage from './pages/CategoriesPage';
import AboutPage from './pages/AboutPage';
import LoginPage from './pages/LoginPage';
import AdminPage from './pages/AdminPage';
import AdminRoute from './components/AdminRoute';

function App() {
    return (
        <AuthProvider>
            <BrowserRouter>
                {/* Шапка теперь рендерится один раз для всего приложения */}
                <Navbar />

                {/* Отступ pt-20 нужен, чтобы контент не залезал под фиксированную шапку */}
                <div className="pt-20">
                    <Routes>
                        <Route path="/" element={<Catalog />} />
                        <Route path="/catalog" element={<CategoriesPage />} />
                        {/* Динамические роуты (пока заглушки, мы сделаем их на следующем этапе) */}
                        <Route path="/catalog/:categoryId" element={<div className="p-20 text-center text-2xl font-bold">Товары категории</div>} />
                        <Route path="/product/:productId" element={<div className="p-20 text-center text-2xl font-bold">Страница товара</div>} />

                        <Route path="/about" element={<AboutPage />} />
                        <Route path="/login" element={<LoginPage />} />

                        <Route path="/admin" element={
                            <AdminRoute>
                                <AdminPage />
                            </AdminRoute>
                        } />
                    </Routes>
                </div>
            </BrowserRouter>
        </AuthProvider>
    );
}

const container = document.getElementById('root');
if (container) {
    const root = createRoot(container);
    root.render(<App />);
}
