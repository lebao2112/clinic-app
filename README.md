# Laravel Workspace Project

This Laravel project is set up and run through Docker on Ubuntu 24.04.

## Environment Versions
* **Docker Engine Version:** Docker version 29.7.1, build e9452d6
* **Docker Compose Version:** Docker Compose version v5.3.1
* **PHP Version:** 8.3-FPM

## Quick Start
1. Start the containers:
   ```bash
   docker compose up -d
   ```
2. Start the local server:
   ```bash
   docker compose exec app php artisan serve --host=0.0.0.0 --port=8000
   ```

## Environment Configuration
Copy `.env.example` to `.env` to configure the project:
`cp .env.example .env`

**Detailed explanation of variables:**
* **`DB_CONNECTION`**: The database driver (use `pgsql` for PostgreSQL).
* **`DB_HOST`**: The database service name in the Docker network (for example: `db` or `postgres`).
* **`EXAMINATION_FEE`**: The exam fee or service fee applied in the system.
* **`PAYPAL_MODE`**: The PayPal payment mode (`sandbox` for testing, `live` for production).
* **`PAYPAL_CLIENT_ID`**: The PayPal Client ID from the PayPal Developer Dashboard.
* **`PAYPAL_SECRET`**: The PayPal secret key.
* **`PAYPAL_CURRENCY`**: The currency used for payments (e.g. `USD`).
