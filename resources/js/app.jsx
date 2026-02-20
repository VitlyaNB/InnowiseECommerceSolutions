import './bootstrap';
import React, { useEffect, useState } from 'react';
import { createRoot } from 'react-dom/client';
import axios from 'axios';

function App() {
    const [products, setProducts] = useState([]);

    useEffect(() => {
        // Axios автоматически поймет путь, так как мы на одном домене
        axios.get('/api/products')
            .then(response => {
                setProducts(response.data);
            })
            .catch(error => console.error("Ошибка API:", error));
    }, []);

    return (
        <div style={{ padding: '20px', fontFamily: 'sans-serif' }}>
            <h1>Каталог из Docker БД</h1>
            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: '20px' }}>
                {products.map(product => (
                    <div key={product.id} style={{ border: '1px solid #ccc', padding: '15px', borderRadius: '8px' }}>
                        <h3>{product.name}</h3>
                        <p>{product.description}</p>
                        <strong style={{ color: 'green' }}>{product.price} ₽</strong>
                    </div>
                ))}
            </div>
            {products.length === 0 && <p>Загрузка товаров или база пуста...</p>}
        </div>
    );
}

const container = document.getElementById('root');
if (container) {
    const root = createRoot(container);
    root.render(<App />);
}
