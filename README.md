# Event Booking API

REST API for event management and ticket booking. Roles: **Admin**, **Organizer**, **Customer**. Authentication via Laravel Sanctum.

---

## Requirements

-   PHP 8.2+
-   Composer
-   SQLite (default) or MySQL/PostgreSQL

---

## Setup

### 1. Install dependencies

```bash
composer install
```

### 2. Environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` if needed (see [Configuration](#configuration) below).

### 3. Database

Default is SQLite with a file at `database/database.sqlite`. Ensure the file exists:

```bash
touch database/database.sqlite
```

Or set `DB_CONNECTION=mysql` (or `pgsql`) and configure `DB_*` in `.env`.

Run migrations:

```bash
php artisan migrate
```

### 4. Seed data

Seeds **2 admins**, **3 organizers**, **10 customers**, **5 events**, **15 tickets**, and **20 bookings** (with payments):

```bash
php artisan db:seed
```

Or reset and seed in one go:

```bash
php artisan migrate:fresh --seed
```

### 5. Run the server

```bash
php artisan serve
```

API base URL: **http://127.0.0.1:8000/api**

---

## Seeded data

After `php artisan db:seed`, you get:

| Type       | Count |
| ---------- | ----- |
| Admins     | 2     |
| Organizers | 3     |
| Customers  | 10    |
| Events     | 5     |
| Tickets    | 15    |
| Bookings   | 20    |
| Payments   | 20    |

All seeded users use password: **`password`**.

### Seeded accounts (login with these)

| Role          | Emails                                                                       |
| ------------- | ---------------------------------------------------------------------------- |
| **Admin**     | `admin1@example.com`, `admin2@example.com`                                   |
| **Organizer** | `organizer1@example.com`, `organizer2@example.com`, `organizer3@example.com` |
| **Customer**  | `customer1@example.com` … `customer10@example.com`                           |

Example login:

```bash
curl -X POST http://127.0.0.1:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin1@example.com","password":"password"}'
```

Use the returned `token` in `Authorization: Bearer <token>` for protected endpoints.

---

## Configuration

### Mail (e.g. MailHog)

To capture confirmation emails locally (MailHog on port 1025, web UI on 8025), in `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

If the app runs inside Docker with a MailHog service, use `MAIL_HOST=mailhog`.

### Queue (confirmation emails)

Confirmation emails are queued. Either:

-   Run a worker: `php artisan queue:work` (with `QUEUE_CONNECTION=database` or `redis`)

---

## Tests

```bash
php artisan test
```

-   **Feature:** Registration, Login, Event creation, Ticket booking, Payment.
-   **Unit:** `PaymentService` (mock payment success/failure, booking confirmation).

---

## Documentation

-   **[API User Manual](docs/API-User-Manual.md)** – Endpoints, roles, request/response format, flows.
-   **[Implementation Overview](docs/Implementation-Overview.md)** – Architecture, layers, flows, testing, Mail/queue/observer config.

---

## License

MIT.
