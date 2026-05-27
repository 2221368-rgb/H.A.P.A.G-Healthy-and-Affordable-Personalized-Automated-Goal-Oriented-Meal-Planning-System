# H.A.P.A.G. — XAMPP Backend Setup Guide

## 📁 Project Structure

```
htdocs/hapag/
├── index.php              ← Main landing page (PHP-wired)
├── hapag-styles.css       ← Frontend stylesheet (copy from upload)
│
├── config/
│   └── db.php             ← Database credentials
│
├── includes/
│   ├── auth.php           ← Session / login helpers
│   └── helpers.php        ← Utilities (json_response, calc_calories…)
│
├── api/
│   ├── register.php       ← POST /api/register.php
│   ├── login.php          ← POST /api/login.php
│   ├── logout.php         ← GET  /api/logout.php
│   ├── prices.php         ← GET/POST/DELETE /api/prices.php
│   ├── recipes.php        ← GET  /api/recipes.php
│   └── meal_plan.php      ← GET/POST/DELETE /api/meal_plan.php
│
├── admin/
│   └── prices.php         ← Admin price management UI
│
└── sql/
    └── hapag_schema.sql   ← Full DB schema + seed data
```

---

## 🚀 Quick Start

### 1 — Install XAMPP
Download from https://www.apachefriends.org and install.
Start **Apache** and **MySQL** in the XAMPP Control Panel.

### 2 — Copy the project
Place the `hapag/` folder inside:
```
C:\xampp\htdocs\hapag\        (Windows)
/opt/lampp/htdocs/hapag/      (Linux/Mac)
```

### 3 — Import the database
1. Open **phpMyAdmin**: http://localhost/phpmyadmin
2. Click **Import** → choose `sql/hapag_schema.sql` → click **Go**

   _Or via terminal:_
   ```bash
   mysql -u root -p < hapag_schema.sql
   ```

### 4 — Configure DB credentials
Edit `config/db.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');       // ← your MySQL root password (blank by default in XAMPP)
define('DB_NAME', 'hapag_db');
```

### 5 — Open the app
Visit: **http://localhost/hapag/**

---

## 🔑 Default Admin Account
| Field    | Value            |
|----------|------------------|
| Email    | admin@hapag.local|
| Password | Admin@1234       |

> ⚠️ Change this password immediately after first login!

---

## 🌐 API Reference

### Auth
| Method | Endpoint              | Description              |
|--------|-----------------------|--------------------------|
| POST   | `/api/register.php`   | Create account + auto-login |
| POST   | `/api/login.php`      | Sign in                  |
| GET    | `/api/logout.php`     | Sign out                 |

### Content
| Method | Endpoint                | Description              |
|--------|-------------------------|--------------------------|
| GET    | `/api/recipes.php`      | All recipes              |
| GET    | `/api/recipes.php?id=N` | Single recipe + ingredients |
| GET    | `/api/prices.php`       | All Bantay Presyo prices |
| GET    | `/api/prices.php?category=fish` | Filter by category |

### Meal Plans (requires login)
| Method | Endpoint                | Description              |
|--------|-------------------------|--------------------------|
| GET    | `/api/meal_plan.php`    | Current user's active plan |
| POST   | `/api/meal_plan.php`    | Generate new 7-day plan  |
| DELETE | `/api/meal_plan.php?id=N` | Archive a plan         |

### Admin (requires admin login)
| Method | Endpoint                | Description              |
|--------|-------------------------|--------------------------|
| POST   | `/api/prices.php`       | Add or update a price    |
| DELETE | `/api/prices.php?id=N`  | Delete a price entry     |
| GET    | `/admin/prices.php`     | Price manager UI         |

---

## 🗄️ Database Tables

| Table               | Purpose                                 |
|---------------------|-----------------------------------------|
| `users`             | Registered accounts                     |
| `user_preferences`  | Allergies, exclusions, budget cap        |
| `recipes`           | Filipino recipe library                  |
| `recipe_ingredients`| Per-recipe ingredient list               |
| `food_prices`       | Bantay Presyo price data                 |
| `meal_plans`        | Weekly plan headers                      |
| `meal_plan_days`    | 7×3 meal assignments per plan           |
| `user_sessions`     | Server-side session store (optional)     |

---

## 🔐 Security Notes (before going live)
- Set a strong `DB_PASS` in `config/db.php`
- Change the default admin password
- Set `'secure' => true` in `auth.php` cookie params when using HTTPS
- Add `.htaccess` to block direct access to `config/` and `includes/`
- Rate-limit `/api/register.php` and `/api/login.php`

---

## 📦 PHP Requirements
- PHP 8.0+
- PDO + PDO_MySQL extension
- `password_hash()` / `password_verify()` (built-in since PHP 5.5)

XAMPP ships with all of these by default.
