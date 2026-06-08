<?php
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/../config/db.php';

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents('php://input'), true) ?: $_POST;

if ($method === 'POST') {
    $id = intval($data['id'] ?? 0);
    if (!$id) { echo json_encode(['success' => false, 'message' => 'Missing product id']); exit; }

    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare('SELECT id, price, qty FROM products WHERE id = ? FOR UPDATE');
        $stmt->execute([$id]);
        $product = $stmt->fetch();
        if (!$product) { $pdo->rollBack(); echo json_encode(['success' => false, 'message' => 'Product not found']); exit; }
        if ($product['qty'] <= 0) { $pdo->rollBack(); echo json_encode(['success' => false, 'message' => 'Out of stock']); exit; }
        $newQty = $product['qty'] - 1;
        $u = $pdo->prepare('UPDATE products SET qty = ? WHERE id = ?');
        $u->execute([$newQty, $id]);
        $ins = $pdo->prepare('INSERT INTO sales (product_id, amount) VALUES (?, ?)');
        $ins->execute([$id, $product['price']]);
        $pdo->commit();
        // total sales
        $t = $pdo->query('SELECT COALESCE(SUM(amount),0) AS total FROM sales');
        $total = $t->fetchColumn();
        echo json_encode(['success' => true, 'product' => ['id' => $id, 'qty' => $newQty], 'total_sales' => $total]);
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Unsupported method']);
