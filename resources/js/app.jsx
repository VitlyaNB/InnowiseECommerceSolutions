import './bootstrap';
import '../css/app.css';
import React from 'react';
import ReactDOM from 'react-dom/client';
import { BrowserRouter, Routes, Route } from 'react-router-dom';
import { AuthProvider } from './contexts/AuthContext';
import { ThemeProvider } from './contexts/ThemeContext';
import Navbar from './components/Navbar';
import CookieConsent from './components/CookieConsent';
import AdminRoute from './components/AdminRoute';

// Pages
import Catalog from './pages/Catalog';
import CategoriesPage from './pages/CategoriesPage';
import CategoryProductsPage from './pages/CategoryProductsPage';
import SingleProductPage from './pages/SingleProductPage';
import LoginPage from './pages/LoginPage';
import CartPage from './pages/CartPage';
import SearchPage from './pages/SearchPage';
import AboutPage from './pages/AboutPage';
import TopUpPage from './pages/TopUpPage';
import AdminPage from './pages/AdminPage';
import OrdersPage from './pages/OrdersPage';

function App() {
    return (
        <AuthProvider>
            <ThemeProvider>
                <BrowserRouter>
                    {/* Классы bg- убраны для работы градиента из CSS */}
                    <div className="min-h-screen text-gray-900 dark:text-gray-100 transition-colors duration-300">
                        <Navbar />
                        <main className="pt-20">
                            <Routes>
                                <Route path="/" element={<Catalog />} />
                                <Route path="/catalog" element={<CategoriesPage />} />
                                <Route path="/catalog/:id" element={<CategoryProductsPage />} />
                                <Route path="/product/:id" element={<SingleProductPage />} />
                                <Route path="/cart" element={<CartPage />} />
                                <Route path="/search" element={<SearchPage />} />
                                <Route path="/login" element={<LoginPage />} />
                                <Route path="/top-up" element={<TopUpPage />} />
                                <Route path="/orders" element={<OrdersPage />} />
                                <Route path="/about" element={<AboutPage />} />
                                <Route path="/admin" element={<AdminRoute><AdminPage /></AdminRoute>} />
                            </Routes>
                        </main>
                        <CookieConsent />
                    </div>
                </BrowserRouter>
            </ThemeProvider>
        </AuthProvider>
    );
}

const rootElement = document.getElementById('root');
if (rootElement) {
    ReactDOM.createRoot(rootElement).render(<React.StrictMode><App /></React.StrictMode>);
}
