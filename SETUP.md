# Local Setup Guide

## Option A — Use the DB Dump (Fastest, Recommended)

This skips migrations and seeds entirely. Use this if `php artisan migrate --seed` gives errors.

### Steps

1. **Clone the repo and install dependencies**
   ```bash
   git clone <repo-url>
   cd xplatform
   composer install
   npm install && npm run build   # or: npm run dev
   ```

2. **Copy the environment file**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Edit `.env` — set your database credentials**
   ```
   DB_DATABASE=analytics_platform
   DB_USERNAME=root
   DB_PASSWORD=          # leave blank for XAMPP default
   ```

4. **Create the database**
   ```sql
   CREATE DATABASE analytics_platform CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

5. **Import the dump**
   ```bash
   # On Mac/Linux:
   mysql -u root analytics_platform < database/dump.sql

   # On Windows (XAMPP):
   C:\xampp\mysql\bin\mysql.exe -u root analytics_platform < database\dump.sql
   ```

6. **Start the server**
   ```bash
   php artisan serve
   ```

7. **Visit** `http://localhost:8000`

---

## Option B — Fresh Migrations + Seed

Use this only if you want a completely fresh database.

```bash
# 1. Create the database first
# In MySQL: CREATE DATABASE analytics_platform;

# 2. Run migrations and seed
php artisan migrate:fresh --seed
```

If you get errors, make sure:
- MySQL strict mode is not blocking nullable columns (XAMPP default is fine)
- The database exists before running migrations

---

## Login Credentials

### Client Portal
| Field    | Value            |
|----------|------------------|
| URL      | `/client/login`  |
| Email    | `test@test.com`  |
| Password | `password`       |

### Master Admin Panel
| Field    | Value                          |
|----------|--------------------------------|
| URL      | `/admin/login`                 |
| Email    | `admin@analytics-platform.com` |
| Password | `password`                     |

---

## Navigation

After logging in as a client, use the left sidebar to navigate:

| Page | URL |
|------|-----|
| Dashboard | `/client/dashboard` |
| L1 — Behavioural Signals | `/layers/l1` |
| L2 — Identity Resolution | `/layers/l2` |
| L3 — Data Processing | `/layers/l3` |
| L4 — Decision Centre | `/layers/l4` |
| L5 — Revenue Engine | `/layers/l5` |
| L6 — Decision Routing | `/layers/l6` |
| L7 — Application Layer | `/layers/l7` |
| L8 — Governance & Security | `/layers/l8` |

---

## Common Errors

### `Unknown column 'size' in field list`
Your `clients` table is missing the `size` column — the database is in a broken state from a previous failed migration.

**Fix:** Drop the database and reimport the dump (Option A), or run:
```bash
php artisan migrate:fresh --seed
```

### `Migrations not running`
Run with verbose output to see where it fails:
```bash
php artisan migrate --verbose
```

Then share the error message.

### `Class not found` errors
```bash
composer dump-autoload
php artisan config:clear
php artisan cache:clear
```

---

## Requirements

- PHP 8.2+
- MySQL 8.0+ (or MariaDB 10.6+)
- Composer
- Node.js 18+
