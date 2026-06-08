14325060/T.24
ALEX DISHON TIMOTHEO 
# DUKALANGU — Database setup

1. Place this folder in your XAMPP `htdocs` (e.g. `c:\xampp\htdocs\DUKALANGU`).
2. Edit database credentials in `install/create_db.php` if necessary (defaults to `root` with no password).
3. Open in browser: `http://localhost/DUKALANGU/install/create_db.php` to create database and tables.
4. Visit `http://localhost/DUKALANGU/` to see the demo page or open the new integrated UI at `http://localhost/DUKALANGU/dukalangu.html`.

Quick DB check:
- After starting XAMPP, open `http://localhost/DUKALANGU/test_db.php` to verify the PHP app can connect to MySQL. It returns JSON with `success: true` when connection is OK.

Notes:
- Make sure XAMPP Apache & MySQL are running.
- If your MySQL `root` user has a password, update `install/create_db.php` and `config/db.php` (or set environment variables `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`).
- The API endpoints are under `api/` (e.g. `api/auth.php`, `api/products.php`, `api/sell.php`, `api/stats.php`).
