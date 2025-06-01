# Laundry System API Backend – Laravel

A RESTful API built with Laravel to manage customers, services, and bookings.

## 🚀 Features

- Manage Customers (CRUD)
- Manage Services (CRUD)
- Manage Bookings (CRUD)
- Authentication using Laravel Sanctum
- Feature tests for all endpoints

---

## 🛠️ Tech Stack

- **Framework:** Laravel 11+
- **Database:** SQLite (for testing) / MySQL / PostgreSQL
- **Auth:** Laravel Sanctum
- **Testing:** PHPUnit

---

## 📦 Installation

```bash
# Clone the repo (from the root of your workspace)
git clone <your-repo-url>
cd backend

# Install dependencies
composer install

# Copy and configure environment
cp .env.example .env
php artisan key:generate

# Set DB connection in .env
# Example for SQLite
touch database/database.sqlite
DB_CONNECTION=sqlite

# Or MySQL/PostgreSQL (edit as needed)
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=your_db
# DB_USERNAME=root
# DB_PASSWORD=

# Run migrations
php artisan migrate

# (Optional) Seed sample data
php artisan db:seed

# Serve the app (default: http://127.0.0.1:8000)
php artisan serve
```

---

## 🔐 Authentication

This project uses **Laravel Sanctum** for API token auth.

To authenticate:

```bash
POST /api/login

{
  "email": "user@example.com",
  "password": "secret"
}
```

Response:
```json
{
  "token": "your_personal_access_token"
}
```

Use it in headers:
```
Authorization: Bearer your_personal_access_token
```

---

## 📘 API Endpoints

All routes are prefixed with `/api`.

### 📁 Bookings
- `GET    /bookings`
- `POST   /bookings`
- `GET    /bookings/{id}`
- `PUT    /bookings/{id}`
- `DELETE /bookings/{id}`

### 👤 Customers
- `GET    /customers`
- `POST   /customers`
- `GET    /customers/{id}`
- `PUT    /customers/{id}`
- `DELETE /customers/{id}`

### 🛠 Services
- `GET    /services`
- `POST   /services`
- `GET    /services/{id}`
- `PUT    /services/{id}`
- `DELETE /services/{id}`

---

## ✅ Running Tests

```bash
php artisan test
```

Tests use an in-memory SQLite database and cover all major endpoints.

---

## 🧪 Sample `.env` for SQLite

```env
APP_NAME=LaundrySystem
APP_ENV=local
APP_KEY=base64:...
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=sqlite
DB_DATABASE=./database/database.sqlite

SANCTUM_STATEFUL_DOMAINS=localhost
```

---

## 📂 Folder Structure Highlights

```
app/
  Http/
    Controllers/
    Requests/
    Resources/
  Models/
database/
  migrations/
  factories/
  seeders/
tests/
  Feature/
```

---

## 📝 License

[MIT](LICENSE)

---

## 👤 Author

Built by [Bonface Kabiru](https://github.com/BonfaceKabiru)

---

## 🌐 Frontend

The frontend for this project is located in the [`frontend/`](../frontend/README.md) folder and is built with Vue 3 + Vite.

---

## ℹ️ Notes

- Make sure to run both backend and frontend for full functionality.
- Configure additional environment variables as needed for mail, queue, etc.