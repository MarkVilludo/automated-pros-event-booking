# Event Booking API – User Manual

Base URL: `http://127.0.0.1:8000/api` (change host/port for your environment.)

All protected endpoints require the **Authorization** header:

```
Authorization: Bearer <your_token>
```

---

### Login

-   **POST** `/api/login`
-   Body (JSON):

```json
{
    "email": "admin1@example.com",
    "password": "password"
}
```

-   Response includes `user`, `token`, and `token_type`. Use the token in the `Authorization` header for all other requests.

---

## 2. All authenticated users

These work for **Admin**, **Organizer**, and **Customer** once logged in.

### Logout

-   **POST** `/api/logout`
-   Headers: `Authorization: Bearer <token>`
-   No body. Invalidates the current token.

### Get current user

-   **GET** `/api/me`
-   Returns the logged-in user (id, name, email, role, etc.).

### List events

-   **GET** `/api/events`
-   Query (optional): `per_page`, `search`, `date_from`, `date_to`, `location`
-   Examples:
    -   `GET /api/events?per_page=10`
    -   `GET /api/events?search=concert&date_from=2026-01-01&date_to=2026-12-31&location=New York`

### Get one event (with tickets)

-   **GET** `/api/events/{id}`
-   Replace `{id}` with the event ID.

---

## 3. Admin

Admin accounts:

-   `admin1@example.com`
-   `admin2@example.com`

Admins can do everything: manage all events, all tickets, and all bookings.

| Action              | Method | Endpoint                         |
| ------------------- | ------ | -------------------------------- |
| List events (all)   | GET    | `/api/events`                    |
| Get event           | GET    | `/api/events/{id}`               |
| Create event        | POST   | `/api/events`                    |
| Update event        | PUT    | `/api/events/{id}`               |
| Delete event        | DELETE | `/api/events/{id}`               |
| Add ticket to event | POST   | `/api/events/{event_id}/tickets` |
| Update ticket       | PUT    | `/api/tickets/{id}`              |
| Delete ticket       | DELETE | `/api/tickets/{id}`              |
| List all bookings   | GET    | `/api/bookings`                  |
| Get payment         | GET    | `/api/payments/{id}`             |

**Typical flow**

1. Login with an admin account.
2. Create events: **POST** `/api/events` with `title`, `description`, `date`, `location`.
3. Add tickets: **POST** `/api/events/{event_id}/tickets` with `type`, `price`, `quantity`.
4. Edit/delete events and tickets as needed.
5. View all bookings and payments.

---

## 4. Organizer

Organizer accounts:

-   `organizer1@example.com`
-   `organizer2@example.com`
-   `organizer3@example.com`

Organizers can manage **only their own** events and those events’ tickets. They can view bookings for their events.

| Action                        | Method | Endpoint                         |
| ----------------------------- | ------ | -------------------------------- |
| List my events                | GET    | `/api/events`                    |
| Get event                     | GET    | `/api/events/{id}`               |
| Create event                  | POST   | `/api/events`                    |
| Update my event               | PUT    | `/api/events/{id}`               |
| Delete my event               | DELETE | `/api/events/{id}`               |
| Add ticket to my event        | POST   | `/api/events/{event_id}/tickets` |
| Update my ticket              | PUT    | `/api/tickets/{id}`              |
| Delete my ticket              | DELETE | `/api/tickets/{id}`              |
| List bookings (for my events) | GET    | `/api/bookings`                  |
| Get payment                   | GET    | `/api/payments/{id}`             |

**Typical flow**

1. Login with an organizer account.
2. Create an event: **POST** `/api/events`  
   Body example: `{"title":"Summer Fest","description":"...","date":"2026-07-15T18:00:00","location":"Central Park"}`
3. Add ticket types: **POST** `/api/events/{event_id}/tickets`  
   Body example: `{"type":"VIP","price":199.99,"quantity":100}`
4. Update event or tickets with **PUT**, or remove with **DELETE**.
5. Check **GET** `/api/bookings` to see bookings for your events.

---

## 5. Customer

Customers can browse events, book tickets, see their bookings, cancel, and run mock payments.

Customer accounts:

-   `customer1@example.com`
-   `customer2@example.com`
-   `customer3@example.com`
-   `customer4@example.com`
-   `customer5@example.com`
-   `customer6@example.com`
-   `customer7@example.com`
-   `customer8@example.com`
-   `customer9@example.com`
-   `customer10@example.com`

    | Action                      | Method | Endpoint                            |
    | --------------------------- | ------ | ----------------------------------- |
    | List events                 | GET    | `/api/events`                       |
    | Get event (with tickets)    | GET    | `/api/events/{id}`                  |
    | Book a ticket               | POST   | `/api/tickets/{ticket_id}/bookings` |
    | List my bookings            | GET    | `/api/bookings`                     |
    | Cancel my booking           | PUT    | `/api/bookings/{id}/cancel`         |
    | Mock payment for my booking | POST   | `/api/bookings/{id}/payment`        |
    | Get my payment              | GET    | `/api/payments/{id}`                |

**Typical flow**

1. Register or login (you get a **Customer** account from register).
2. Browse: **GET** `/api/events` (use `search`, `date_from`, `date_to`, `location` if needed).
3. Open an event: **GET** `/api/events/{id}` and note the ticket `id` you want.
4. Create a booking: **POST** `/api/tickets/{ticket_id}/bookings`  
   Body: `{"quantity":2}`  
   You cannot have two active bookings for the same ticket (double booking is blocked).
5. See your bookings: **GET** `/api/bookings`.
6. Pay (mock): **POST** `/api/bookings/{booking_id}/payment`  
   Body: `{"simulate_success":true}` (or `false` to test failure).  
   When payment succeeds, the booking becomes **Confirmed** and the customer receives a **confirmation email** (queued).
7. Cancel if needed: **PUT** `/api/bookings/{booking_id}/cancel` (no body).
8. Check payment: **GET** `/api/payments/{payment_id}`.

---

## 6. Request/response format

-   **Headers:** `Content-Type: application/json`, `Accept: application/json`
-   **Success:** `{"success":true,"message":"...","data":...}`
-   **Error:** `{"success":false,"message":"...","errors":...}` (optional `errors` for validation)
-   **Status codes:** 200 OK, 201 Created, 400 Bad Request, 401 Unauthorized, 403 Forbidden, 404 Not Found, 422 Validation Error

---

## 7. Quick reference by role

| Role          | Can do                                                                                                                               |
| ------------- | ------------------------------------------------------------------------------------------------------------------------------------ |
| **Admin**     | All events/tickets/bookings/payments (create, read, update, delete).                                                                 |
| **Organizer** | Own events and their tickets (CRUD). View bookings for own events.                                                                   |
| **Customer**  | List events, view event with tickets, create booking per ticket, list own bookings, cancel booking, mock payment, view own payments. |

---

## 8. Seeded test accounts (after `php artisan db:seed`)

Seeded data: **2 admins**, **3 organizers**, **10 customers**, **5 events**, **15 tickets**, **20 bookings**. All seeded users have password: **`password`**.

| Role          | Emails                                                                       |
| ------------- | ---------------------------------------------------------------------------- |
| **Admin**     | `admin1@example.com`, `admin2@example.com`                                   |
| **Organizer** | `organizer1@example.com`, `organizer2@example.com`, `organizer3@example.com` |
| **Customer**  | `customer1@example.com` … `customer10@example.com`                           |

Use **POST** `/api/login` with one of the emails and password `password`, then use the returned `token` in `Authorization: Bearer <token>` for all subsequent requests.
