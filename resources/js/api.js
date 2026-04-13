import axios from 'axios';

const api = axios.create({
    baseURL: '/api',
    headers: {
        'Accept': 'application/json',
    }
});

export async function refreshCsrfToken() {
    try {
        await axios.get('/sanctum/csrf-cookie');
    } catch (e) {
        console.error('CSRF refresh failed', e);
    }
}

api.interceptors.request.use(config => {
    const csrfToken = document.cookie.match(/XSRF-TOKEN=([^;]+)/)?.[1];
    if (csrfToken) {
        config.headers['X-XSRF-TOKEN'] = csrfToken;
    }
    
    const token = localStorage.getItem('auth_token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

export default api;