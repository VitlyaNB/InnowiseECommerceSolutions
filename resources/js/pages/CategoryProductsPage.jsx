import React, { useEffect, useState } from 'react';
import { useParams, Link } from 'react-router-dom';
import axios from 'axios';
import { ShoppingCart, ArrowLeft, LayoutGrid } from 'lucide-react';

export default function CategoryProductsPage() {
    const { categoryId } = useParams();
    const [products, setProducts] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        setLoading(true);
        axios.get(`/api/categories/${categoryId}/products`)
            .then(res => setProducts(res.data.data || res.data))
            .catch(err => console.error(err))
            .finally(() => setLoading(false));
    }, [categoryId]);

    return (
        <div className="min-h-screen bg-gray-50 pb-16">
            <div className="bg-white px-8 py-6 mb-8 border-b border-gray-200">
                <div className="max-w-7xl mx-auto flex items-center gap-4">
                    <Link to="/catalog" className="p-2 hover:bg-gray-100 rounded-full transition-colors"><ArrowLeft className="w-6 h-6 text-gray-900" /></Link>
                    <LayoutGrid className="w-6 h-6 text-indigo-600" />
                    <h1 className="text-2xl font-black text-gray-900">Товары категории</h1>
                </div>
            </div>

            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                {loading ? (
                    <div className="flex justify-center items-center h-64"><div className="animate-spin rounded-full h-16 w-16 border-t-2 border-indigo-600"></div></div>
                ) : products.length === 0 ? (
                    <div className="text-center py-20 bg-white rounded-3xl border border-gray-100 shadow-sm">
                        <h2 className="text-2xl font-bold text-gray-400">В этой категории пока нет товаров :(</h2>
                    </div>
                ) : (
                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                        {products.map((product) => (
                            <Link key={product.id} to={`/product/${product.id}`} className="group bg-white rounded-3xl overflow-hidden border border-gray-100 shadow-sm hover:shadow-2xl transition-all flex flex-col">
                                <div className="aspect-[4/5] bg-gray-200 relative overflow-hidden">
                                    <img src={`https://picsum.photos/seed/${product.id}/600/800`} className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" alt={product.name}/>
                                </div>
                                <div className="p-6 flex flex-col flex-1">
                                    <h3 className="text-lg font-bold text-gray-900 mb-2">{product.name}</h3>
                                    <div className="flex items-center justify-between mt-auto pt-4 border-t border-gray-100">
                                        <span className="text-xl font-black text-gray-900">{product.price} BYN</span>
                                        <button className="bg-gray-900 text-white p-3 rounded-2xl hover:bg-indigo-600 transition-colors" onClick={(e) => e.preventDefault()}>
                                            <ShoppingCart className="h-5 w-5" />
                                        </button>
                                    </div>
                                </div>
                            </Link>
                        ))}
                    </div>
                )}
            </div>
        </div>
    );
}
