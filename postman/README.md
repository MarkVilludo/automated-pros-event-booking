# Postman collection – Event Booking API

## Import

1. Open Postman.
2. **Import** → **File** → choose `Event-Booking-API.postman_collection.json`.
3. The collection **Event Booking API** will appear.

## Variables

- **base_url**: `http://127.0.0.1:8000/api` (change if your app runs elsewhere).
- **token**: Set automatically after **Auth → Login** (or set manually for Bearer auth).

## Quick start

1. Run **Auth → Login** (e.g. `admin@example.com` / `password` if you used the seeders).
2. The response script will save the token into the collection variable `token`.
3. Other requests (Events, Tickets, Bookings, Logout, Get user) will use this token.

## Seeded users (after `php artisan db:seed`)

| Role      | Email             | Password  |
|-----------|-------------------|-----------|
| Admin     | admin@example.com | password  |
| Organizer | (from UserFactory)| password  |
| Customer  | (from UserFactory)| password  |

New customers can use **Auth → Register**; they get a token in the response (Login script does not run on Register, so copy the token from the response and paste it into the collection variable **token** if you want to use it for the next requests).
