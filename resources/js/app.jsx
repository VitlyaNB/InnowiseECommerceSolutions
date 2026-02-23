import './bootstrap';
import '../css/app.css';
import React from 'react';
import { createRoot } from 'react-dom/client';
import { BrowserRouter, Routes, Route } from 'react-router-dom';
import Catalog from './pages/Catalog';
import AdminPage from './pages/AdminPage';
import CategoriesPage from './pages/CategoriesPage';
import LoginPage from './pages/LoginPage';
import AdminRoute from './components/AdminRoute';


function App() {
    return (
        <BrowserRouter>
            <Routes>
                <Route path="/login" element={<LoginPage />} />
                <Route path="/" element={<Catalog />} />
                <Route path="/catalog" element={<CategoriesPage />} />
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
