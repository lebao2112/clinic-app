/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// --- CẤU HÌNH GẮN TOKEN (INTERCEPTORS REQUEST) ---
window.axios.interceptors.request.use(function (config) {
    const token = localStorage.getItem('auth_token');
    if (token) {
        // Gắn Bearer Token vào Header của mọi request
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
}, function (error) {
    return Promise.reject(error);
});

// --- CẤU HÌNH XỬ LÝ LỖI (INTERCEPTORS RESPONSE) ---
window.axios.interceptors.response.use(function (response) {
    return response;
}, function (error) {
    if (error.response) {
        // CHỈ xử lý khi lỗi 401 (Chưa xác thực / Hết hạn token)
        if (error.response.status === 401) {
            // Xóa token cũ bị hỏng/hết hạn
            localStorage.removeItem('auth_token');
            // Đá văng ra màn hình đăng nhập
            window.location.href = '/login'; 
        }
        // Lỗi 403 (Không đủ quyền) sẽ bị bỏ qua ở đây và ném trực tiếp về cho Component (Patients.vue) tự hiển thị thông báo.
    }
    return Promise.reject(error);
});