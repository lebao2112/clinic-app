# Laravel Workspace Project

Project Laravel được khởi tạo và chạy thông qua Docker trên hệ điều hành Ubuntu 24.04.

## Environment Versions
* **Docker Engine Version:** Docker version 29.7.1, build e9452d6
* **Docker Compose Version:** Docker Compose version v5.3.1
* **PHP Version:** 8.3-FPM

## Quick Start
1. Khởi chạy các container:
   ```bash
   docker compose up -d
   ```
2. Chạy server nội bộ:
   ```bash
   docker compose exec app php artisan serve --host=0.0.0.0 --port=8000
   ```
## Cấu hình Biến Môi Trường (Environment Setup)

Copy file `.env.example` thành `.env` để cấu hình dự án:
`cp .env.example .env`

**Giải thích chi tiết các biến:**
* **`DB_CONNECTION`**: Hệ quản trị cơ sở dữ liệu (sử dụng `pgsql` cho PostgreSQL).
* **`DB_HOST`**: Tên service của database trong Docker network (ví dụ: `db` hoặc `postgres`).
* **`EXAMINATION_FEE`**: Lệ phí thi hoặc phí dịch vụ áp dụng trong hệ thống.
* **`PAYPAL_MODE`**: Môi trường chạy cổng thanh toán PayPal (`sandbox` để test, `live` khi chạy thật).
* **`PAYPAL_CLIENT_ID`**: Khóa Client ID lấy từ PayPal Developer Dashboard.
* **`PAYPAL_SECRET`**: Khóa bảo mật (Secret Key) của PayPal. 
* **`PAYPAL_CURRENCY`**: Đơn vị tiền tệ thanh toán (VD: `USD`).
