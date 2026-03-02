import React, { useState, useEffect } from 'react';
import api from '../api';
import { Cookie, X } from 'lucide-react';

export default function CookieConsent() {
    const [visible, setVisible] = useState(false);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        api.get('/cookie-consent')
            .then(res => {
                const accepted = res.data?.accepted ?? false;
                if (!accepted) {
                    setVisible(true);
                }
            })
            .catch(() => setVisible(true))
            .finally(() => setLoading(false));
    }, []);

    const accept = async () => {
        try {
            await api.post('/cookie-consent', { accepted: true }, {
                withCredentials: true,
            });
            setVisible(false);
        } catch (e) {
            setVisible(false);
        }
    };

    const decline = async () => {
        try {
            await api.post('/cookie-consent', { accepted: false }, {
                withCredentials: true,
            });
        } catch (_) {}
        setVisible(false);
    };

    if (loading || !visible) return null;

    return (
        <div className="fixed bottom-4 left-4 right-4 md:left-auto md:right-4 md:max-w-md z-[100] bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 p-6">
            <div className="flex items-start gap-4">
                <div className="flex-shrink-0 w-12 h-12 rounded-xl bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center">
                    <Cookie className="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
                </div>
                <div className="flex-1">
                    <h4 className="font-bold text-gray-900 dark:text-white mb-1">Мы используем cookie</h4>
                    <p className="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        Для сохранения корзины и улучшения работы сайта мы используем cookie. Принять — корзина будет сохраняться между сессиями. Отклонить — только текущая сессия.
                    </p>
                    <div className="flex gap-2">
                        <button
                            onClick={accept}
                            className="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl transition-colors"
                        >
                            Принять
                        </button>
                        <button
                            onClick={decline}
                            className="px-4 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-bold rounded-xl transition-colors"
                        >
                            Отклонить
                        </button>
                    </div>
                </div>
                <button
                    onClick={decline}
                    className="flex-shrink-0 p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                >
                    <X className="w-5 h-5" />
                </button>
            </div>
        </div>
    );
}
