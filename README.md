# Laravel + Vue Booking System

This is a **full-stack booking system** built with **Laravel (API)** as the backend and **Vue 3** as the frontend. The project is structured into two main directories:

- `backend/` → Laravel API for booking logic
- `frontend/` → Vue 3 application for user interface

---

## 🛠 Tech Stack

- **Backend**: Laravel 12, MySQL
- **Frontend**: Vue 3, Vite, Axios
- **Authentication**: Laravel Sanctum
- **API**: RESTful with token authentication

---

## 🚀 Features

- Register and login (API-based with Sanctum)
- Booking management
- Role-based access (Admin, User)
- Vue 3 SPA with responsive UI
- Error handling and validations

---

## 📁 Project Structure

booking-vue-laravel/
│
├── backend/ # Laravel backend (API)
│ └── ...
│
├── frontend/booking_vue/ # Vue frontend (SPA)
│ └── ...
│
└── README.md # Project documentation

## 🔧 Installation

### 1. Clone the Repository

git clone https://github.com/athumaniMfaume/laravel-vue-booking.git
cd laravel-vue-booking


Backend Setup (Laravel)

cd backend

# Install dependencies
composer install

# Create .env and configure
cp .env.example .env
php artisan key:generate

# Set DB credentials in .env
php artisan migrate

# Run server
php artisan serve


Frontend Setup (Vue 3)

cd frontend/booking_vue

# Install dependencies
npm install

# Run development server
npm run dev 

🔐 Authentication
The project uses Laravel Sanctum for SPA authentication.

After login/register, authenticated users receive a token via cookies or headers.

Protected routes are managed both in Laravel and Vue.

📦 API Endpoints (Sample)
Method	Endpoint	Description
POST	/api/login	Login user
POST	/api/register	Register new user
GET	/api/services	List available services
POST	/api/bookings	Create a booking
GET	/api/bookings/me	Get bookings by user


📬 Contact
You can reach me via:

GitHub: https://github.com/athumaniMfaume/laravel-vue-booking

Email: athumanimfaume1995@gmail.com



