# Laundry System Frontend

A modern Vue 3 + Vite SPA for managing customers, services, and bookings in a laundry business. This frontend works seamlessly with the [Laravel backend](../backend/README.md).

---

## 🚀 Features

- User authentication (login/logout) via Laravel Sanctum
- Manage bookings, customers, and services (CRUD)
- Responsive dashboard and modals
- Axios-based API integration

---

## 🛠️ Tech Stack

- **Framework:** Vue 3
- **Build Tool:** Vite
- **State Management:** Pinia
- **Routing:** Vue Router
- **HTTP Client:** Axios
- **Styling:** Tailwind CSS

---

## 📦 Installation

```bash
# From the root of your workspace
cd frontend

# Install dependencies
npm install
```

---

## 🧑‍💻 Development

```bash
npm run dev
```

- The app will be available at [http://localhost:5173](http://localhost:5173) by default.

---

## 🔗 Connecting to the Backend

- The frontend expects the Laravel backend API to be running at `http://localhost:8000`.
- All API requests are proxied to the backend using Axios (see [`src/api/axios.js`](src/api/axios.js)).
- Authentication is handled via Laravel Sanctum using Bearer tokens stored in `localStorage`.

**Backend setup:**  
See [../backend/README.md](../backend/README.md) for backend installation and API details.

---

## 🗂️ Project Structure

```
src/
  api/           # Axios instance
  components/    # Vue components (Sidebar, Modals, etc.)
  pages/         # Page-level Vue components (Dashboard, Login)
  views/         # Feature views (Bookings, Customers, Services)
  router/        # Vue Router setup
  composables/   # Reusable logic (e.g., useAuth)
  main.js        # App entry point
```

---

## 🧑‍💼 Authentication Flow

1. User logs in via `/login` page.
2. On success, a token is received and stored in `localStorage`.
3. All subsequent API requests include the `Authorization: Bearer <token>` header.
4. Logout clears the token and redirects to login.

---

## 📋 Environment Variables

- No special `.env` is required for the frontend by default.
- If you need to change the backend API URL, update `baseURL` in [`src/api/axios.js`](src/api/axios.js).

---

## 📝 License

[MIT](../backend/LICENSE)

---

## 👤 Author

Built by [Bonface Kabiru](https://github.com/BonfaceKabiru)

---

## 🌐 Backend

See the [Laravel backend README](../backend/README.md) for API endpoints and backend setup.
