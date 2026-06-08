<?php
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/../config/db.php';

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents('php://input'), true) ?: $_GET ?: $_POST;

if ($method === 'GET') {
    // return recent sales joined with product title
    $stmt = $pdo->query('SELECT s.id, s.product_id, p.title AS product_title, s.amount, s.created_at FROM sales s LEFT JOIN products p ON s.product_id = p.id ORDER BY s.created_at DESC LIMIT 200');
    $sales = $stmt->fetchAll();
    echo json_encode(['success' => true, 'sales' => $sales]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Unsupported method']);
