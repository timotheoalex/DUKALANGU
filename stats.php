<?php
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/../config/db.php';

$stmt = $pdo->query('SELECT COALESCE(SUM(amount),0) AS total_sales FROM sales');
$total_sales = $stmt->fetchColumn();
$stmt2 = $pdo->query('SELECT COUNT(*) FROM products');
$total_items = $stmt2->fetchColumn();

echo json_encode(['success' => true, 'total_sales' => $total_sales, 'total_items' => intval($total_items)]);
