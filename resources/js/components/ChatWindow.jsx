import React, { useState, useEffect, useRef, useContext } from 'react';
import api from '../api';
import { AuthContext } from '../contexts/AuthContext';
import { Send, MessageCircle, X } from 'lucide-react';

const normalizeMessage = (message) => {
    if (!message || typeof message !== 'object') {
        return null;
    }

    const userId = message.user_id ?? message.userId ?? message.user?.id ?? null;
    const userName = message.user?.name ?? message.user_name ?? message.userName ?? null;
    const userRole = message.user?.role ?? message.user_role ?? message.userRole ?? null;
    const createdAt = message.created_at ?? message.createdAt ?? new Date().toISOString();

    return {
        id: message.id,
        chat_id: message.chat_id ?? message.chatId ?? null,
        user_id: userId,
        message: message.message ?? '',
        is_read: message.is_read ?? message.isRead ?? false,
        created_at: createdAt,
        user: {
            id: userId,
            name: userName,
            role: userRole
        }
    };
};

export default function ChatWindow({ chatId, onClose, isAdmin = false }) {
    const { user: currentUser } = useContext(AuthContext);
    const [messages, setMessages] = useState([]);
    const [newMessage, setNewMessage] = useState('');
    const [loading, setLoading] = useState(false);
    const messagesEndRef = useRef(null);
    const [currentChatId, setCurrentChatId] = useState(chatId);

    useEffect(() => {
        if (isAdmin && chatId) {
            setCurrentChatId(chatId);
        }
    }, [chatId, isAdmin]);

    const scrollToBottom = () => {
        messagesEndRef.current?.scrollIntoView({ behavior: "smooth" });
    };

    useEffect(() => {
        if (!currentChatId && !isAdmin && currentUser) {
            // Start or get user's own chat
            api.post('/chats/start').then(res => {
                const payload = res.data?.data || res.data;
                if (payload?.id) {
                    setCurrentChatId(payload.id);
                }
            });
        }
    }, [currentChatId, isAdmin, currentUser]);

    useEffect(() => {
        if (currentChatId) {
            fetchMessages();
            
            const channel = window.Echo.private(`chat.${currentChatId}`)
                .listen('MessageSent', (e) => {
                    // Check if it's not our own message to avoid duplicates
                    // broadcast(...)->toOthers() already avoids sending to the sender, 
                    // but it's good to be safe.
                    const incomingMessage = normalizeMessage(e);
                    if (!incomingMessage) {
                        return;
                    }
                    setMessages(prev => {
                        if (prev.some(m => m.id === incomingMessage.id)) return prev;
                        return [...prev, incomingMessage];
                    });
                });

            return () => {
                window.Echo.leave(`chat.${currentChatId}`);
            };
        }
    }, [currentChatId]);

    useEffect(scrollToBottom, [messages]);

    const fetchMessages = async () => {
        setLoading(true);
        try {
            const res = await api.get(`/chats/${currentChatId}`);
            const payload = res.data?.data || res.data;
            const rawMessages = Array.isArray(payload?.messages) ? payload.messages : [];
            const normalized = rawMessages
                .map(normalizeMessage)
                .filter(Boolean);
            setMessages(normalized);
        } catch (err) {
            console.error("Failed to fetch messages", err);
        } finally {
            setLoading(false);
        }
    };

    const handleSendMessage = async (e) => {
        e.preventDefault();
        if (!newMessage.trim() || !currentChatId) return;

        const tempMessage = newMessage;
        setNewMessage('');

        try {
            const res = await api.post(`/chats/${currentChatId}/messages`, { message: tempMessage });
            const payload = res.data?.data || res.data;
            const normalizedMessage = normalizeMessage(payload);
            if (normalizedMessage) {
                setMessages(prev => [...prev, normalizedMessage]);
            }
        } catch (err) {
            console.error("Failed to send message", err);
        }
    };

    if (!currentUser) return null;
    if (!currentChatId && !isAdmin) return <div className="p-4 text-center">Загрузка чата...</div>;

    return (
        <div className={`flex flex-col h-full bg-slate-50 dark:bg-slate-950 ${isAdmin ? '' : 'rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800'}`}>
            {/* Header */}
            {!isAdmin && (
                <div className="p-4 border-b border-white/10 flex justify-between items-center bg-indigo-600 text-white rounded-t-2xl shadow-lg">
                    <div className="flex items-center gap-3">
                        <div className="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center font-bold">
                            <MessageCircle size={18} />
                        </div>
                        <span className="font-black uppercase tracking-tight text-sm">Поддержка</span>
                    </div>
                    {onClose && (
                        <button onClick={onClose} className="hover:bg-white/20 p-1 rounded-full transition-colors">
                            <X size={20} />
                        </button>
                    )}
                </div>
            )}

            {/* Messages Area */}
            <div className="flex-1 overflow-y-auto p-4 space-y-4 min-h-[300px] max-h-[600px] custom-scrollbar">
                {messages.length === 0 && !loading && (
                    <div className="text-center text-slate-400 mt-20 text-xs font-bold uppercase tracking-widest opacity-50">
                        Начните диалог первым
                    </div>
                )}
                {messages.map((msg) => {
                    const isOwn = msg.user_id === currentUser.id;
                    return (
                        <div key={msg.id} className={`flex ${isOwn ? 'justify-end' : 'justify-start'} animate-in fade-in slide-in-from-bottom-2 duration-300`}>
                            <div className={`max-w-[80%] px-4 py-2 rounded-2xl text-sm shadow-sm ${
                                isOwn 
                                ? 'bg-indigo-600 text-white rounded-tr-none' 
                                : 'bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 rounded-tl-none border border-slate-100 dark:border-slate-700'
                            }`}>
                                {!isOwn && isAdmin && (
                                    <div className="text-[10px] font-black uppercase tracking-widest text-indigo-500 mb-1">
                                        {msg.user?.name || 'User'}
                                    </div>
                                )}
                                <div className="leading-relaxed whitespace-pre-wrap">{msg.message}</div>
                                <div className={`text-[9px] mt-1 text-right font-bold ${isOwn ? 'text-indigo-200' : 'text-slate-400'}`}>
                                    {msg.created_at
                                        ? new Date(msg.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
                                        : ''}
                                </div>
                            </div>
                        </div>
                    );
                })}
                <div ref={messagesEndRef} />
            </div>

            {/* Input Area */}
            <form onSubmit={handleSendMessage} className="p-4 bg-white dark:bg-slate-900 border-t border-slate-100 dark:border-slate-800 flex gap-2">
                <input
                    type="text"
                    value={newMessage}
                    onChange={(e) => setNewMessage(e.target.value)}
                    placeholder="Ваш вопрос..."
                    className="flex-1 bg-slate-100 dark:bg-slate-800 border-none rounded-xl px-4 py-2 focus:ring-2 focus:ring-indigo-500 outline-none text-sm font-medium dark:text-white"
                />
                <button 
                    type="submit" 
                    disabled={!newMessage.trim() || !currentChatId}
                    className="bg-indigo-600 text-white p-2.5 rounded-xl hover:bg-indigo-700 disabled:opacity-30 transition-all shadow-md active:scale-95"
                >
                    <Send size={18} />
                </button>
            </form>
        </div>
    );
}
