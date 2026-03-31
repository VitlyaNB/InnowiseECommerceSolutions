import React, { useContext } from 'react';
import ChatWindow from './ChatWindow';
import { MessageCircle } from 'lucide-react';
import { AuthContext } from '../contexts/AuthContext';
import { useLocation } from 'react-router-dom';

export default function ChatWidget() {
    const { user, loading, isChatOpen, setChatOpen } = useContext(AuthContext);
    const location = useLocation();

    if (loading) return null;
    if (location.pathname !== '/') return null;
    if (!user || user.role === 'admin') return null;

    return (
        <div className="fixed bottom-8 right-8 z-50 animate-in slide-in-from-bottom-8 duration-500">
            {isChatOpen ? (
                <div className="w-[350px] shadow-2xl rounded-3xl overflow-hidden border border-white/10 glass-card">
                    <ChatWindow onClose={() => setChatOpen(false)} />
                </div>
            ) : (
                <button 
                    onClick={() => setChatOpen(true)}
                    className="w-14 h-14 bg-indigo-600 hover:bg-indigo-700 text-white rounded-full flex items-center justify-center shadow-2xl hover:scale-110 active:scale-95 transition-all group"
                >
                    <MessageCircle className="group-hover:rotate-12 transition-transform" size={28} />
                </button>
            )}
        </div>
    );
}
