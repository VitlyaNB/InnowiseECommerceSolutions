import React, { useEffect, useState } from 'react';
import { useParams, Link } from 'react-router-dom';
import api from '../api';
import { ShoppingCart, ArrowLeft } from 'lucide-react';
import { useAuth } from '../contexts/AuthContext';

export default function CategoryProductsPage() {
    const { id } = useParams();
    const [products, setProducts] = useState([]);
    const [categoryName, setCategoryName] = useState('');
    const [loading, setLoading] = useState(true);
    const { loadCartCount } = useAuth();

    useEffect(() => {
        setLoading(true);
        api.get(`/categories/${id}/products`)
            .then(res => {
                const data = res.data.data || res.data;
                setProducts(data);
            })
            .catch(err => console.error(err))
            .finally(() => setLoading(false));

        api.get('/categories').then(res => {
            const cats = res.data.data || res.data;
            const current = cats.find(c => c.id == id);
            if(current) setCategoryName(current.name);
        });

    }, [id]);

    const addToCart = async (e, productId) => {
        e.preventDefault(); // Предотвращаем переход по ссылке
        try {
            await api.post('/cart', { product_id: productId, quantity: 1 });
            await loadCartCount();
            alert('Добавлено в корзину');
        } catch (err) {
            console.error(err);
            alert(err.response?.data?.message || 'Ошибка добавления');
        }
    };

    return (
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <Link to="/catalog" className="inline-flex items-center gap-2 text-gray-500 hover:text-indigo-600 mb-6 font-bold transition-colors">
                <ArrowLeft className="w-4 h-4" /> Все категории
            </Link>

            <h1 className="text-3xl font-black text-gray-900 dark:text-white mb-8">
                {categoryName || 'Товары категории'}
            </h1>

            {loading ? (
                <div className="flex justify-center p-20"><div className="animate-spin rounded-full h-10 w-10 border-b-2 border-indigo-600"></div></div>
            ) : products.length === 0 ? (
                <div className="text-center py-20 bg-gray-50 dark:bg-gray-800 rounded-3xl">
                    <p className="text-gray-500 font-bold">В этой категории пока нет товаров.</p>
                </div>
            ) : (
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    {products.map(product => (
                        <Link
                            key={product.id}
                            to={`/product/${product.id}`}
                            className="group bg-white dark:bg-gray-800 rounded-2xl shadow-sm hover:shadow-xl transition-all border border-gray-100 dark:border-gray-700 overflow-hidden flex flex-col"
                        >
                            <div className="relative aspect-square overflow-hidden bg-gray-100 dark:bg-gray-900">
                                {product.images && product.images.length > 0 ? (
                                    <img src={product.images[0].url} alt={product.name} className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
                                ) : (
                                    <div className="w-full h-full flex items-center justify-center text-gray-400 font-bold">NO IMAGE</div>
                                )}
                                <div className="absolute bottom-3 left-3 bg-white/90 dark:bg-gray-800/90 backdrop-blur px-3 py-1 rounded-lg font-black text-gray-900 dark:text-white text-sm shadow-sm">
                                    {parseFloat(product.price).toFixed(2)} BYN
                                </div>
                            </div>

                            <div className="p-5 flex flex-col flex-1">
                                <h3 className="font-bold text-lg text-gray-900 dark:text-white leading-tight group-hover:text-indigo-600 transition-colors line-clamp-2 mb-2">
                                    {product.name}
                                </h3>

                                <button
                                    onClick={(e) => addToCart(e, product.id)}
                                    className="mt-auto w-full bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white py-3 rounded-xl font-bold text-sm hover:bg-indigo-600 hover:text-white transition-all flex items-center justify-center gap-2"
                                >
                                    <ShoppingCart className="w-4 h-4" /> В корзину
                                </button>
                            </div>
                        </Link>
                    ))}
                </div>
            )}
        </div>
    );
}
