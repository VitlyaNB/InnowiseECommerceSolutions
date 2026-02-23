import './bootstrap';
import '../css/app.css';
import React from 'react';
import { createRoot } from 'react-dom/client';
import { BrowserRouter, Routes, Route } from 'react-router-dom';
import Catalog from './pages/Catalog';
import AdminPage from './pages/AdminPage';

function App() {
    return (
        <BrowserRouter>
            <Routes>
                {/* Обычный интерфейс для гостей и юзеров */}
                <Route path="/" element={<Catalog />} />

                {/* Админка */}
                <Route path="/admin" element={<AdminPage />} />
            </Routes>
        </BrowserRouter>
    );
}

const container = document.getElementById('root');
if (container) {
    const root = createRoot(container);
    root.render(<App />);
}
