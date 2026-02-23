import React from 'react';
import { Navigate } from 'react-router-dom';

const AdminRoute = ({ children }) => {
    const token = localStorage.getItem('auth_token');
    const user = JSON.parse(localStorage.getItem('user'));

    const isAuthenticated = token && user;
    const isAdmin = user?.is_admin === 1 || user?.is_admin === true;

    if (!isAuthenticated) {
        return <Navigate to="/login" />;
    }

    if (!isAdmin) {
        return <Navigate to="/" />;
    }

    return children;
};

export default AdminRoute;
