import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

window.axios.interceptors.request.use(function (config) {
    const token = localStorage.getItem('auth_token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
}, function (error) {
    return Promise.reject(error);
});

window.axios.interceptors.response.use(function (response) {
    return response;
}, function (error) {
    if (error.response) {
        if (error.response.status === 401) {
            if (error.config && error.config.skipGlobal401) {
                return Promise.reject(error);
            }

            localStorage.removeItem('auth_token');
            window.location.href = '/login'; 
        }
    }
    return Promise.reject(error);
});