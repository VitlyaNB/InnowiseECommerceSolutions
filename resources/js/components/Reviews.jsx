import React, { useEffect, useState } from 'react';
import api from '../api';
import { useAuth } from '../contexts/AuthContext';
import { Star, ThumbsUp, MessageCircle, Send } from 'lucide-react';

export default function Reviews({ productId }) {
    const { user } = useAuth();
    const [reviews, setReviews] = useState([]);
    const [canReview, setCanReview] = useState(false);
    const [newReview, setNewReview] = useState({ rating: 5, comment: '' });
    const [replyTo, setReplyTo] = useState(null);
    const [replyText, setReplyText] = useState('');

    useEffect(() => {
        loadReviews();
        if (user) checkPermission();
    }, [productId, user]);

    const loadReviews = () => {
        api.get(`/products/${productId}/reviews`)
            .then(res => {
                console.log('Reviews loaded, user in context:', user);
                setReviews(res.data.data);
            })
            .catch(err => console.error("Ошибка загрузки отзывов:", err));
    };

    const checkPermission = () => {
        api.get(`/products/${productId}/can-review`)
            .then(res => setCanReview(res.data.can_review))
            .catch(err => console.error("Ошибка проверки прав:", err));
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        try {
            await api.post('/reviews', { ...newReview, product_id: productId });
            setNewReview({ rating: 5, comment: '' });
            loadReviews();
            setCanReview(false);
        } catch (err) {
            alert(err.response?.data?.message || 'Ошибка при публикации отзыва');
        }
    };

    const handleReply = async (parentId) => {
        try {
            await api.post('/reviews', {
                product_id: productId,
                parent_id: parentId,
                comment: replyText
            });
            setReplyTo(null);
            setReplyText('');
            loadReviews();
        } catch (err) {
            alert(err.response?.data?.message || 'Ошибка при ответе');
        }
    };

    const toggleLike = async (reviewId) => {
        if (!user) return alert('Войдите, чтобы ставить лайки');
        try {
            console.log('Toggling like for review:', reviewId);
            await api.post(`/reviews/${reviewId}/like`);
            await loadReviews();
            console.log('Like toggled, reviews reloaded');
        } catch (err) {
            console.error("Ошибка при лайке:", err);
        }
    };

    const formatDate = (dateString) => {
        if (!dateString) return '';
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return '';
        return date.toLocaleDateString('ru-RU', { 
            day: 'numeric', 
            month: 'long', 
            year: 'numeric' 
        });
    };

    const renderStars = (rating, interactive = false) => {
        return (
            <div className="flex gap-1">
                {[1, 2, 3, 4, 5].map((star) => (
                    <Star
                        key={star}
                        className={`w-5 h-5 transition-colors ${
                            star <= rating ? 'fill-yellow-400 text-yellow-400' : 'text-gray-300'
                        } ${interactive ? 'cursor-pointer hover:scale-110' : ''}`}
                        onClick={() => interactive && setNewReview({ ...newReview, rating: star })}
                    />
                ))}
            </div>
        );
    };

    return (
        <div className="mt-16 border-t border-gray-100 dark:border-gray-700 pt-10">
            <h2 className="text-2xl font-black mb-8 dark:text-white flex items-center gap-3">
                Отзывы <span className="bg-gray-100 dark:bg-gray-800 text-sm px-3 py-1 rounded-full">{reviews.length}</span>
            </h2>

            {canReview && (
                <div className="mb-10 bg-gray-50 dark:bg-gray-800 p-6 rounded-2xl border border-gray-100 dark:border-gray-700">
                    <h3 className="font-bold text-lg mb-4 dark:text-white">Написать отзыв</h3>
                    <div className="flex items-center gap-4 mb-4">
                        <span className="text-sm text-gray-500">Ваша оценка:</span>
                        {renderStars(newReview.rating, true)}
                    </div>
                    <textarea
                        value={newReview.comment}
                        onChange={e => setNewReview({...newReview, comment: e.target.value})}
                        className="w-full p-4 rounded-xl border border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white mb-4 h-32 outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="Поделитесь впечатлениями о товаре..."
                    />
                    <button onClick={handleSubmit} className="bg-indigo-600 text-white px-6 py-3 rounded-xl font-bold text-sm hover:bg-indigo-700 transition-colors">
                        Опубликовать
                    </button>
                </div>
            )}

            <div className="space-y-8">
                {reviews.map(review => (
                    <div key={review.id} className="border-b border-gray-50 dark:border-gray-800 pb-8 last:border-0">
                        <div className="flex gap-4">
                            <div className="w-10 h-10 bg-indigo-100 dark:bg-indigo-900 rounded-full flex items-center justify-center font-bold text-indigo-600 shrink-0">
                                {review.user?.name ? review.user.name[0].toUpperCase() : '?'}
                            </div>
                            <div className="flex-1">
                                <div className="flex justify-between items-start mb-2">
                                    <div>
                                        <h4 className="font-bold dark:text-white">{review.user?.name || 'Аноним'}</h4>
                                        <div className="flex gap-2 text-xs text-gray-400 mt-1">
                                            {renderStars(review.rating)}
                                            <span>• {formatDate(review.created_at)}</span>
                                        </div>
                                    </div>
                                </div>
                                <p className="text-gray-700 dark:text-gray-300 mb-3 leading-relaxed">{review.comment}</p>

                                <div className="flex gap-4 items-center text-sm">
                                    <button onClick={() => toggleLike(review.id)} className={`flex items-center gap-1.5 font-bold transition-colors ${review.is_liked ? 'text-red-500' : 'text-gray-400 hover:text-gray-600'}`}>
                                        <ThumbsUp className={`w-4 h-4 ${review.is_liked ? 'fill-current' : ''}`} /> {review.likes_count || 0}
                                    </button>
                                    <button onClick={() => setReplyTo(replyTo === review.id ? null : review.id)} className="flex items-center gap-1.5 text-gray-400 hover:text-indigo-600 font-bold transition-colors">
                                        <MessageCircle className="w-4 h-4" /> Ответить
                                    </button>
                                </div>

                                {replyTo === review.id && user && (
                                    <div className="mt-4 flex gap-2 animate-in fade-in slide-in-from-top-2">
                                        <input
                                            autoFocus
                                            value={replyText}
                                            onChange={e => setReplyText(e.target.value)}
                                            className="flex-1 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2 text-sm dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none"
                                            placeholder="Напишите ответ..."
                                        />
                                        <button onClick={() => handleReply(review.id)} className="p-2 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition-colors">
                                            <Send className="w-4 h-4" />
                                        </button>
                                    </div>
                                )}

                                {review.replies?.length > 0 && (
                                    <div className="mt-4 pl-4 border-l-2 border-gray-100 dark:border-gray-800 space-y-4">
                                        {review.replies.map(reply => (
                                            <div key={reply.id} className="bg-gray-50 dark:bg-gray-800/50 p-4 rounded-xl">
                                                <div className="flex gap-2 items-center mb-2">
                                                    <span className="font-bold text-sm dark:text-white">{reply.user?.name || 'Аноним'}</span>
                                                    <span className="text-xs text-gray-400">{formatDate(reply.created_at)}</span>
                                                </div>
                                                <p className="text-sm text-gray-600 dark:text-gray-400 mb-2">{reply.comment}</p>
                                                <button onClick={() => toggleLike(reply.id)} className={`text-xs flex items-center gap-1 font-bold transition-colors ${reply.is_liked ? 'text-red-500' : 'text-gray-400'}`}>
                                                    <ThumbsUp className={`w-3 h-3 ${reply.is_liked ? 'fill-current' : ''}`} /> {reply.likes_count || 0}
                                                </button>
                                            </div>
                                        ))}
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>
                ))}
            </div>
        </div>
    );
}
