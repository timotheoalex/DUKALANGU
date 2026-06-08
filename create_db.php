<?php
// Run this script from browser or CLI to create database and tables.
// You can pass credentials via environment variables (DB_HOST, DB_USER, DB_PASS, DB_NAME)
// or via GET params: create_db.php?db_user=root&db_pass=&db_name=dukalangu
$host = getenv('DB_HOST') ?: ($_GET['db_host'] ?? '127.0.0.1');
$user = getenv('DB_USER') ?: ($_GET['db_user'] ?? 'root');
$pass = getenv('DB_PASS') ?: ($_GET['db_pass'] ?? '');
$db   = getenv('DB_NAME') ?: ($_GET['db_name'] ?? 'dukalangu');

try {
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$db`");
    $schemaFile = __DIR__ . '/../sql/schema.sql';
    if (!file_exists($schemaFile)) {
        throw new Exception('Schema file not found: ' . $schemaFile);
    }
    $schema = file_get_contents($schemaFile);
    $pdo->exec($schema);
    echo "Database and schema created/updated successfully.<br>";
    echo "Using host={$host}, db={$db}, user={$user}.";
} catch (Exception $e) {
    echo "Error: " . htmlspecialchars($e->getMessage());
}
