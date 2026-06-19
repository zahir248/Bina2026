# Project Handover Document

## 1. Project Summary

- **Project Name:** BINA 2026 (Bina2026)
- **Purpose of the system:** Public website and ticketing platform for **BINA** — a construction industry exhibition/event (ASEAN-focused). The site promotes the event, sells tickets online, and provides admin tools to manage events, orders, and on-site ticket validation.
- **Main features:**
  - Marketing pages (home, gallery, about, career spotlight, exhibition info, etc.)
  - Event catalogue with categories, schedules, and personnel
  - User registration/login (email/password and Google OAuth)
  - Shopping cart, checkout, and Stripe payments (card + FPX)
  - Promo codes and affiliate codes
  - Order history, PDF receipts, and QR code tickets
  - Admin dashboard (users, events, tickets, orders, reports, newsletter, logs, settings)
  - Public ticket scanner (`/scanner`) for event-day validation
  - Maintenance mode, homepage countdown, and visitor tracking
- **Target users:**
  - **Public visitors / ticket buyers** — browse events and purchase tickets
  - **Registered clients** — manage profile, cart, orders, refunds
  - **Admins** — manage content, sales, and operations via `/admin`
  - **Event staff** — use the ticket scanner on event day

---

## 2. System Overview

- **Frontend technology:** Laravel Blade templates, custom CSS (`public/css/app.css`), vanilla JavaScript (`public/js/app.js`), Bootstrap Icons, Google Fonts. Vite + Tailwind CSS exist in `resources/` for asset building, but layouts load compiled assets from `public/`.
- **Backend technology:** PHP 8.2+, Laravel 12 (monolithic MVC)
- **Database technology:** SQLite by default (local dev); MySQL/MariaDB supported via `.env` configuration
- **Overall architecture:** Classic Laravel web application. Browser requests hit `routes/web.php`, which routes to Client or Admin controllers. Business data is stored in a relational database. Payments go through Stripe; transactional emails are sent via Laravel Mail. Sessions, cache, and queues use the database driver.

---

## 3. Project Structure

Brief explanation of important folders and files.

| Folder/File | Purpose |
|------------|---------|
| `app/Http/Controllers/Client/` | Public-facing logic: auth, cart, checkout, profile, events, newsletter |
| `app/Http/Controllers/Admin/` | Admin panel: dashboard, events, orders, reports, settings, scanner |
| `app/Models/` | Eloquent models (User, Event, Ticket, Order, Cart, etc.) |
| `app/Mail/` | Email templates for payments, refunds, cancellations, newsletter |
| `app/Support/StripeConfig.php` | Stripe key selection (live vs test mode) |
| `app/helpers.php` | `storage_asset()` helper for serving uploaded files on cPanel |
| `routes/web.php` | All application routes (client, admin, API, scanner) |
| `resources/views/client/` | Public page templates |
| `resources/views/admin/` | Admin panel templates |
| `resources/views/emails/` | Email HTML views |
| `resources/css/` & `resources/js/` | Source assets (built to `public/` via Vite) |
| `public/` | Web root: `index.php`, static CSS/JS, images |
| `database/migrations/` | Database schema definitions |
| `database/seeders/AdminSeeder.php` | Seeds initial admin user |
| `config/services.php` | Google OAuth and Stripe configuration |
| `.env` / `.env.example` | Environment variables and secrets |
| `bootstrap/app.php` | Middleware registration (maintenance mode, admin access) |

---

## 4. Setup & Run

Steps to run the project locally:

### 1. Prerequisites

- PHP 8.2+ with extensions: `pdo`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`
- Composer
- Node.js 18+ and npm
- SQLite (default) or MySQL/MariaDB

### 2. Installation

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed          # optional: creates admin user
npm install
npm run build                # or copy existing public/css and public/js
```

Alternatively, use the Composer shortcut:

```bash
composer setup
```

### 3. Environment variables required

| Variable | Purpose |
|----------|---------|
| `APP_NAME`, `APP_ENV`, `APP_KEY`, `APP_DEBUG`, `APP_URL` | Core Laravel app config |
| `DB_CONNECTION`, `DB_*` | Database connection (SQLite or MySQL) |
| `SESSION_DRIVER`, `CACHE_STORE`, `QUEUE_CONNECTION` | Session/cache/queue (default: `database`) |
| `MAIL_*` | SMTP / mail delivery settings |
| `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET` | Google OAuth login |
| `STRIPE_KEY`, `STRIPE_SECRET` | Stripe live payments |
| `STRIPE_TEST_KEY`, `STRIPE_TEST_SECRET` | Stripe test payments (when admin enables test mode) |
| `STRIPE_FEE_*` (optional) | Pass Stripe processing fees to customers |

### 4. Run commands

**Development (all services):**

```bash
composer dev
```

Runs: `php artisan serve`, queue listener, log viewer (Pail), and Vite dev server.

**Manual:**

```bash
php artisan serve
php artisan queue:listen       # required if using queued jobs
npm run dev                    # optional during frontend work
```

**Health check:** `GET /up`

**Admin login:** `/admin/login`  
**Client login:** `/login`

---

## 5. Deployment

- **Hosting/server:** Not Identified (production domain not in repo). Code includes **cPanel-specific workarounds** (storage serving route, `storage_asset()` helper, Apache `.htaccess`).
- **Deployment process:** Not Identified (no CI/CD or deploy scripts in repository). Typical Laravel deployment applies:
  1. Upload code to server (document root should point to `public/`)
  2. Set `.env` on server (`.env.production` is gitignored)
  3. Run `composer install --no-dev`, `php artisan migrate --force`, `php artisan config:cache`, `php artisan route:cache`
  4. Build frontend: `npm run build` (ensure `public/css/app.css` and `public/js/app.js` are current)
  5. Set folder permissions on `storage/` and `bootstrap/cache/`
  6. Run a queue worker if background jobs are used: `php artisan queue:work`
- **Important deployment notes:**
  - `public/storage` symlink may not work on cPanel — uploads are served via `/storage/serve/{path}` instead
  - When maintenance mode is **off**, unauthenticated users get **404** on `/admin` paths (by design)
  - Stripe test mode can be toggled in **Admin → Settings** (uses `STRIPE_TEST_*` keys from `.env`)
  - Queue connection is `database` — a worker process should run in production for reliable email delivery

---

## 6. Database Overview

List of important tables.

| Table | Purpose |
|--------|---------|
| `users` | Registered users (`role`: `admin` or `client`), profile fields, Google OAuth ID |
| `events` | Event details (name, dates, location, images, content blocks, status) |
| `event_categories` | Categories for grouping events |
| `tickets` | Ticket types with pricing and quantity discounts |
| `ticket_event` | Links tickets to specific events (many-to-many) |
| `carts` | Per-user cart items (ticket + event + quantity) |
| `orders` | Completed purchases (Stripe payment intent, status, buyer/ticket-holder snapshots, refunds) |
| `order_items` | Line items within an order |
| `promo_codes` | Discount codes |
| `promo_code_event` | Promo code applicability per event |
| `affiliate_codes` | Affiliate/referral codes for checkout |
| `schedules` | Event session schedules |
| `event_personnel` | Speakers, hosts, moderators |
| `event_personnel_schedule` | Personnel-to-schedule assignments |
| `settings` | Key-value app settings (maintenance mode, countdown, Stripe test mode, admin email) |
| `newsletter_subscribers` | Email subscribers |
| `email_logs` | Sent/failed email audit trail |
| `checkout_activity_logs` | Payment/checkout flow debugging logs |
| `visitor_counts` | Daily unique visitor counts |
| `sessions` | User sessions (session driver) |
| `cache` / `jobs` | Laravel cache and queue tables |

---

## 7. API & Integrations

| Integration | Purpose |
|------------|---------|
| **Stripe** | Online payments (credit/debit card and FPX). PaymentIntents created at checkout; orders created on success |
| **Google OAuth** (Laravel Socialite) | “Login / Sign up with Google” for clients |
| **SMTP / Laravel Mail** | Transactional emails: payment success/failure, refunds, cancellations, newsletter |
| **Google Fonts** | Typography (Inter, Playfair Display) |
| **Bootstrap Icons (CDN)** | UI icons |
| **Stripe.js (CDN)** | Client-side payment form on checkout and repay flows |
| **DomPDF** (`barryvdh/laravel-dompdf`) | PDF receipt generation |
| **Endroid QR Code** | QR codes on tickets |
| **Maatwebsite Excel** | Admin exports (orders, reports, event participants) |

**Internal JSON endpoints** (not a separate API app):

- `GET /api/event-categories`
- `GET /api/events`
- `GET /api/events/upcoming`

**Not Identified:** Google Analytics, Facebook Pixel, payment gateways other than Stripe, SSO beyond Google, AWS S3 (config present but not actively used in application code).

---

## 8. Access & Credentials

DO NOT show actual secrets.

| Access Type | Where It Is Stored |
|------------|-------------------|
| Database | `.env` (`DB_*` variables) on server |
| Application key | `.env` (`APP_KEY`) |
| Server / hosting | Not Identified — obtain from project owner or hosting provider |
| SMTP / email | `.env` (`MAIL_*` variables) |
| Google OAuth | `.env` (`GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`) |
| Stripe (live) | `.env` (`STRIPE_KEY`, `STRIPE_SECRET`) |
| Stripe (test) | `.env` (`STRIPE_TEST_KEY`, `STRIPE_TEST_SECRET`) |
| Admin notification email | Admin Settings UI → stored in `settings` table |
| Initial admin account | `database/seeders/AdminSeeder.php` — obtain credentials from team lead; change password after first login |
| Maintenance / Stripe test toggles | Admin → Settings (stored in `settings` table) |

---

## 9. Important Notes

### Known issues / considerations

- **AdminSeeder contains a hardcoded admin password** in source code. This should be rotated and removed from the seeder before any public repository exposure.
- **Frontend asset pipeline:** Production layouts use `public/css/app.css` and `public/js/app.js`, not `@vite`. After editing `resources/css` or `resources/js`, run `npm run build` and verify output lands in `public/`.
- **Admin route hiding:** When maintenance mode is disabled, `/admin` URLs return 404 for guests. Admins must use `/admin/login`.
- **Ticket scanner is public** (`/scanner`) — no login required. Consider access control if this is a concern.
- **Stripe test orders** can be hidden from admin reports via Settings.

### Common troubleshooting

| Problem | Check |
|---------|-------|
| Uploaded images not showing | Use `storage_asset()` URLs; confirm files exist in `storage/app/public/`; try `/storage/serve/{path}` |
| Payments fail | Verify Stripe keys in `.env`; check Admin Settings for test mode; review `checkout_activity_logs` and `storage/logs/laravel.log` |
| Google login fails | Confirm `GOOGLE_CLIENT_ID/SECRET` and redirect URI: `{APP_URL}/auth/google/callback` |
| Emails not sent | Check `MAIL_*` in `.env`; default local config uses `MAIL_MAILER=log` (writes to log, not inbox) |
| 503 maintenance page | Disable maintenance mode in Admin → Settings, or log in as admin |
| CSS/JS changes not visible | Rebuild with `npm run build`; cache-busting uses file `mtime` in client layout |

### Things future developers should know

- User roles are `admin` and `client` (string field on `users.role`).
- Orders are created **only after successful Stripe payment**, not when the PaymentIntent is created.
- Promo codes apply in cart; affiliate codes apply at checkout.
- Refund workflow: client requests → admin approves/rejects → emails sent to both parties.
- Event countdown on homepage prioritises upcoming active events; admin fallback datetime is used only when no events exist.
- Timezone for visitor tracking is hardcoded to `Asia/Kuala_Lumpur`.
- Key routes file: `routes/web.php` — most features are defined here.
- No automated tests beyond Laravel boilerplate; run `composer test` or `php artisan test` before major changes.

---

*Document generated from codebase analysis. Last reviewed: June 2026.*
