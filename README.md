## PageTurner Bookstore

A Laravel 10 bookstore web app (Tailwind + Vite) with customer features (browse books/categories, cart, orders, reviews) and an admin area for managing books/categories and orders.

## Setup instructions

### Prerequisites

- PHP **8.1+**
- [Composer](https://getcomposer.org/)
- Node.js **18+** and npm
- A database server (this project is currently configured to use **PostgreSQL** in `.env`, but you can use MySQL if you prefer)

### Install dependencies

```bash
composer install
npm install
```

### Environment variables

1. Copy the example env file and generate an app key:

```bash
copy .env.example .env
php artisan key:generate
```

2. Edit `.env` and set your database connection details. Example for PostgreSQL:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=pageturner_bookstore
DB_USERNAME=postgres
DB_PASSWORD=your_password
```

### Migrate and seed

This will create tables and insert sample data (categories, books, reviews) plus an admin account.

```bash
php artisan migrate --seed
```

### Run the app

In one terminal:

```bash
php artisan serve
```

In a second terminal (for Vite assets):

```bash
npm run dev
```

Then open `http://127.0.0.1:8000` (or the URL shown by `artisan serve`).

## Login credentials (test accounts)

### Admin (seeded)

These credentials are created by `database/seeders/AdminUserSeeder.php` when you run `php artisan migrate --seed` (or `php artisan db:seed`).

- **Email**: `adminpageturner@gmail.com`
- **Password**: `Admin123!`

### Customer (seeded)

The database seeder also creates 10 customer users via the user factory:

- **Password for all seeded customers**: `password`
- **Email**: random per user (check your `users` table after seeding)

Tip: for a predictable customer login, just register a new account in the UI.

## Additional notes

- **Don’t commit secrets**: `.env` contains local credentials; keep it local and share only `.env.example`.
- **Database choice**: `.env.example` defaults to MySQL, but your current `.env` uses PostgreSQL. Either works as long as `DB_CONNECTION` and credentials match your local DB server.
- **Admin routes**: admin pages are under the `/admin` prefix (requires an admin user).
