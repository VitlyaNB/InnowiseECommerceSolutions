import './bootstrap';
import '../css/app.css';
import React from 'react';
import { createRoot } from 'react-dom/client';
import { BrowserRouter, Routes, Route } from 'react-router-dom';

import Catalog from './pages/Catalog';
import CategoriesPage from './pages/CategoriesPage';
import AboutPage from './pages/AboutPage';
import LoginPage from './pages/LoginPage';
import AdminPage from './pages/AdminPage';
import AdminRoute from './components/AdminRoute';

function App() {
    return (
        <BrowserRouter>
            <Routes>
                {/* Публичные страницы */}
                <Route path="/" element={<Catalog />} />
                <Route path="/catalog" element={<CategoriesPage />} />
                <Route path="/about" element={<AboutPage />} />
                <Route path="/login" element={<LoginPage />} />

                {/* Заглушка для товаров конкретной категории */}
                <Route path="/catalog/:id" element={<div className="p-20 text-center text-2xl font-bold">Здесь будут товары выбранной категории</div>} />

                {/* Защищенная админка */}
                <Route path="/admin" element={
                    <AdminRoute>
                        <AdminPage />
                    </AdminRoute>
                } />
            </Routes>
        </BrowserRouter>
    );
}

const container = document.getElementById('root');
if (container) {
    const root = createRoot(container);
    root.render(<App />);
}
