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

# Kiến trúc hệ thống

## 🏗 Kiến trúc đã chọn
Dự án áp dụng **Kiến trúc B: Controller + Service Pattern**.

Trong đó:
- **Controller:** Đóng vai trò như "người lễ tân". Chỉ chịu trách nhiệm tiếp nhận HTTP Request, xác thực dữ liệu đầu vào (Validation / FormRequest), gọi tới Service tương ứng và trả về HTTP Response (JSON). Tuyệt đối không chứa logic nghiệp vụ tại đây.
- **Service:** Đóng vai trò như "nhà bếp". Đảm nhận toàn bộ việc xử lý logic nghiệp vụ (Business Logic), tính toán, và tương tác với các Model (Database).

## 💡 Lý do lựa chọn
1. **Tách biệt trách nhiệm (Separation of Concerns):** Giúp Controller cực kỳ mỏng và gọn gàng (Thin Controller). Khi cần sửa logic nghiệp vụ, developer chỉ cần tìm đến file Service mà không sợ ảnh hưởng đến luồng nhận/trả request.
2. **Tối ưu hóa nguồn lực (Tránh Over-engineering):** Mặc dù Kiến trúc C (thêm Repository) giúp trừu tượng hóa tầng Data Access, nhưng bản thân Eloquent ORM của Laravel đã vận hành rất mạnh mẽ và linh hoạt. Việc dùng thêm Repository cho dự án này có thể gây thừa thãi code (boilerplate) không cần thiết.
3. **Tính tái sử dụng cao (Reusability):** Một hàm xử lý trong Service có thể được gọi từ nhiều Controller khác nhau (ví dụ: gọi từ API Controller, hoặc gọi từ một Command line trên terminal) mà không phải viết lại code.
4. **Dễ dàng Testing:** Logic nằm độc lập ở Service giúp việc viết Unit Test trở nên dễ dàng hơn nhiều so với việc test code nằm kẹt bên trong Controller.

## 🔄 Sơ đồ luồng Request (Request Flow)

Luồng đi của một request trong hệ thống sẽ tuân thủ nghiêm ngặt theo các bước sau:

```mermaid
sequenceDiagram
    autonumber
    participant Client
    participant Router as Route & Middleware
    participant Controller
    participant Service
    participant Model as Eloquent Model
    participant DB as Database

    Client->>Router: Gửi HTTP Request
    Router->>Router: Kiểm tra Auth & Permission (EnsurePermission)
    Router->>Controller: Chuyển tiếp Request (nếu hợp pháp)
    Controller->>Controller: Validate Data (Form Request)
    Controller->>Service: Truyền dữ liệu đã validate vào Service
    Service->>Model: Xử lý logic & Yêu cầu dữ liệu
    Model->>DB: Thực thi truy vấn SQL
    DB-->>Model: Trả kết quả truy vấn
    Model-->>Service: Trả dữ liệu dạng Object/Collection
    Service-->>Controller: Trả kết quả xử lý nghiệp vụ
    Controller-->>Client: Trả về HTTP Response (JSON)