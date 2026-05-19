# Purchase Management System - Purchase Entry & Legacy Migration Module

A modern Laravel, Livewire, and Alpine.js application for managing purchase entries, implementing Spatie Role-Based Access Control (RBAC), and performing idempotent legacy data migrations.

---

## Technical Stack
* **Framework:** Laravel 12
* **Reactivity:** Livewire 3 / 4 & Alpine.js
* **Styles:** Tailwind CSS
* **Access Control:** Spatie Laravel Permission (RBAC)
* **Database:** MySQL

---

## Features
1. **Dynamic Purchase Form:** Dynamic row addition/removal with instant subtotal and grand total calculations via Alpine.js and Livewire.
2. **Robust Validation:** Prevents duplicate item + brand combinations on a single purchase. Showcases field-level real-time validation and a disabled submit state when errors are present.
3. **Spatie RBAC System:** Complete route, controller, and view-level protection dividing users into `Admin` and `User` roles.
4. **Idempotent Migration Command:** Run migrations safely from either the command line or the Admin dashboard with automatic creation/mapping of items and brands.

---

## Installation & Setup

### 1. Prerequisites
Ensure you have the following installed on your machine:
* PHP 8.2 or 8.3
* Composer
* MySQL Database

### 2. Setup Database & Environment
Copy the environment template and configure your database parameters:
```bash
cp .env.example .env
```
Open `.env` and set your database connection details, for example:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gcs
DB_USERNAME=root
DB_PASSWORD=
```

### 3. Install Dependencies
Install composer packages:
```bash
composer install
```

### 4. Run Migrations & Seed Database
Run migrations and seed default values (this will create items, brands, roles, and default users):
```bash
php artisan migrate --seed
```

### 5. Start Development Server
Start the local server:
```bash
php artisan serve
```
Access the application at `http://127.0.0.1:8000`.

---

## Default Accounts & Roles

| Email | Password | Role | Description |
|---|---|---|---|
| `admin@example.com` | `password` | **Admin** | Full access to create, edit, delete purchases, and run legacy migration. |
| `user@example.com` | `password` | **User** | Read-only access to view purchases. |

---

## Running the Legacy Migration Script

The legacy migration imports and maps old raw purchase entries into normalized tables (`items`, `brands`, `purchases`, and `purchase_items`). It runs idempotently so running it multiple times will not insert duplicate records.

### Method A: Via Command Line (Artisan)
Run the following command in your terminal:
```bash
php artisan migrate:legacy-purchases
```

### Method B: Via Admin Dashboard
1. Log in as `admin@example.com` / `password`.
2. Click the **Run Legacy Migration** button at the top-right of the Purchase Orders dashboard.

---

## Core System Assumptions
1. **Legacy Data Mapping:** The migration script maps legacy data by creating a new `Item` or `Brand` if the name does not exist, and links them dynamically.
2. **Database Integrity:** The database enforces a composite unique constraint on `purchase_items` for `(purchase_id, item_id, brand_id)` preventing the same combination from being added to the same purchase.
3. **Validation & Submit States:** The save button on the purchase entry form automatically disables if any validation error (such as a duplicate item + brand selection) is present.
4. **Role Precedence Sync:** The application overrides Spatie's trait logic to read the user model's database `role` column as the primary source of truth. If the column value is changed manually in the database (e.g. from `admin` to `user`), the application immediately updates all gate, middleware, and blade permissions without requiring manual sync of Spatie's pivot tables.

---

## Part 6 — Debugging Task (PHP MySQLi)

A secure, optimized, and robustly handled version of the legacy PHP MySQLi code is detailed below.

### Identified Vulnerabilities in Legacy Code:
1. **SQL Injection (SQLi):** Unescaped `$_GET['id']` parameter was interpolated directly into the query string.
2. **Missing Input Validation:** Input presence and integer type check were absent.
3. **No Connection Error Handling:** Failures to connect to MySQL were unhandled.
4. **Cross-Site Scripting (XSS):** Database contents (`$row['name']`) were printed without HTML entity escaping.
5. **Inefficient Query:** Used `SELECT *` when only the single `name` column was needed.

### Corrected Implementation:
```php
<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = mysqli_connect("localhost", "root", "", "test");
} catch (mysqli_sql_exception $e) {
    error_log("Database connection failed: " . $e->getMessage());
    http_response_code(500);
    die("Service temporarily unavailable. Please try again later.");
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    mysqli_close($conn);
    die("Invalid or missing User ID.");
}

$id = (int) $_GET['id'];

try {
    $query = "SELECT name FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                echo htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') . "<br>";
            }
        } else {
            echo "No user found with the specified ID.";
        }
        mysqli_stmt_close($stmt);
    } else {
        throw new Exception("Failed to prepare SQL query statement.");
    }
} catch (Exception $e) {
    error_log("Query execution error: " . $e->getMessage());
    http_response_code(500);
    echo "An unexpected error occurred. Please try again later.";
} finally {
    if (isset($conn) && $conn) {
        mysqli_close($conn);
    }
}
?>
```

