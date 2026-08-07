# Laravel Workspace Project

This Laravel project is created and run using Docker on Ubuntu 24.04.

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

## Environment Setup
Copy `.env.example` to `.env` to configure the project:
`cp .env.example .env`

**Detailed explanation of variables:**
* **`DB_CONNECTION`**: The database management system (use `pgsql` for PostgreSQL).
* **`DB_HOST`**: The database service name in the Docker network (for example: `db` or `postgres`).
* **`EXAMINATION_FEE`**: The exam fee or service fee applied in the system.
* **`PAYPAL_MODE`**: The PayPal payment environment (`sandbox` for testing, `live` for production).
* **`PAYPAL_CLIENT_ID`**: The PayPal Client ID from the PayPal Developer Dashboard.
* **`PAYPAL_SECRET`**: The PayPal secret key.
* **`PAYPAL_CURRENCY`**: The currency used for payments (e.g. `USD`).

# System Architecture

## Selected Architecture
This project applies the **Architecture B: Controller + Service Pattern**.

Where:
- **Controller:** Acts like a "receptionist." It only receives HTTP requests, validates input data (Validation / FormRequest), calls the corresponding Service, and returns the HTTP response (JSON). It should not contain business logic.
- **Service:** Acts like a "kitchen." It handles all business logic, calculations, and interactions with Models (Database).

## Why this architecture?
1. **Separation of Concerns:** Keeps Controllers very thin and clean (Thin Controller). When business logic changes, developers only need to update Service files without affecting request handling.
2. **Avoid over-engineering:** Although Architecture C (adding a Repository) can abstract data access, Laravel's Eloquent ORM is already powerful and flexible. Adding a Repository layer may create unnecessary boilerplate code in this project.
3. **High reusability:** A function in a Service can be called by multiple Controllers (for example: from an API Controller or from a command line) without rewriting code.
4. **Easier Testing:** Logic isolated in Services makes Unit Testing much easier than testing code embedded inside Controllers.

## Request Flow Diagram

The request flow in the system follows these steps:

```mermaid
sequenceDiagram
    autonumber
    participant Client
    participant Router as Route & Middleware
    participant Controller
    participant Service
    participant Model as Eloquent Model
    participant DB as Database

    Client->>Router: Send HTTP Request
    Router->>Router: Check Auth & Permission (EnsurePermission)
    Router->>Controller: Forward Request (if authorized)
    Controller->>Controller: Validate Data (Form Request)
    Controller->>Service: Pass validated data to Service
    Service->>Model: Execute logic & request data
    Model->>DB: Execute SQL query
    DB-->>Model: Return query results
    Model-->>Service: Return data as Object/Collection
    Service-->>Controller: Return business result
    Controller-->>Client: Return HTTP Response (JSON)