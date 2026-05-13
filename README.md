# X Platforms

Behavioral analytics platform built with Laravel 12. Businesses connect their data, and the platform scores user behavior to recommend the right action at the right time.

---

## What it does

- Collects user events (mobile, email, calls)
- Computes 9 behavioral scores per user
- Segments users and recommends actions (discounts, campaigns, win-backs)
- Runs through an 8-layer intelligence pipeline

**Two account types:**
- **Master Admin** — approves clients, manages the platform
- **Client** — a business that uses the dashboard to understand and act on their users

---

## Setup

**Requirements:** XAMPP (PHP 8.2+, MySQL), Composer, Node.js 18+

Place the project at `C:\xampp\htdocs\xplatform\`, then:

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Create a database named `analytics_platform` in phpMyAdmin. The `.env` file is already configured to use it.

Run migrations and build assets:

```bash
php artisan migrate
npm install && npm run build
```

Start the dev server:

```bash
composer dev
```

Or open XAMPP Apache and visit `http://localhost/xplatform/public`

---

## Seed demo data

```bash
php artisan db:seed
```

Creates a Master Admin, a test client, sample industries, and demo behavioral profiles. Check `DatabaseSeeder.php` for login credentials.

To wipe and reseed:

```bash
php artisan migrate:fresh --seed
```

---

## Key URLs

| URL | Page |
|-----|------|
| `/` | Landing page |
| `/app/register` | Client registration (7 steps) |
| `/app/login` | Client login |
| `/app/dashboard` | Client dashboard |
| `/app/layers/l4` | Decision Centre (user scores) |
| `/app/layers/l5` | Decision Scenarios (recommendations) |
| `/admin/login` | Master admin login |

---

## Registration flow

7-step wizard at `/app/register`:

1. Company details
2. Industry
3. Intelligence layers to enable
4. Data sources (CRM, mobile SDK, email)
5. Behavioral micro-signals
6. Predictive models
7. Automated actions

After step 7 the account is `pending` until a Master Admin approves it.

---

## Intelligence layers

| Layer | Purpose |
|-------|---------|
| L1 — Data Collection | Raw events ingested |
| L2 — Signal Processing | Events converted to micro-signals |
| L3 — Behavioral Modeling | Signals aggregated into patterns |
| L4 — Decision Centre | Per-user scores and segment |
| L5 — Decision Scenarios | Action recommendations |
| L6 — Campaign Orchestration | Automated campaign triggers |
| L7 — Attribution | Outcome tracking |
| L8 — Reporting | ROI summaries and insights |

---

## Behavioral scores (0–100)

| Score | Window | What it measures |
|-------|--------|-----------------|
| `intent_score` | 30d | Purchase intent |
| `engagement_score` | 30d | Breadth of product interaction |
| `buying_readiness` | 30d | Proximity to purchase |
| `churn_score` | 90d | Cancellation risk |
| `loyalty_score` | 90d | Long-term retention |
| `trust_score` | 90d | Behavioral consistency |
| `frustration_score` | 90d | Errors and failed flows |
| `dropoff_risk` | 90d | Mid-funnel abandonment |
| `reactivation_potential` | 14d | Dormant user signals |

**Segments:** `champion`, `loyal`, `at_risk`, `dormant`, `new`

**L5 decision logic:** users with `trust_score < 65` get a discount offer (1.38× conversion lift); users above 65 get full price.

---

## Tech stack

| | |
|-|-|
| Framework | Laravel 12 |
| Frontend | Blade + Vite (no JS framework) |
| Database | MySQL via XAMPP |
| Auth | Multi-guard (client + master_admin) |
| Queue | Laravel database driver |
