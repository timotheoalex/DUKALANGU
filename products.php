<?php
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/../config/db.php';

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents('php://input'), true) ?: $_POST;

if ($method === 'GET') {
    // Return products with owner username (if any)
    $stmt = $pdo->query('SELECT p.id, p.title, p.price, p.qty, p.created_at, u.username AS owner FROM products p LEFT JOIN users u ON p.user_id = u.id ORDER BY p.id DESC');
    $products = $stmt->fetchAll();
    echo json_encode(['success' => true, 'products' => $products]);
    exit;
}

if ($method === 'POST') {
    $title = trim($data['title'] ?? '');
    $price = floatval($data['price'] ?? 0);
    $qty = intval($data['qty'] ?? 0);
    $username = trim($data['username'] ?? '');
    $user_id = null;
    if ($username) {
        try {
            $colStmt = $pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users'");
            $colStmt->execute();
            $cols = $colStmt->fetchAll(PDO::FETCH_COLUMN);
            $usernameCol = null;
            foreach (['username','name','email'] as $c) { if (in_array($c, $cols)) { $usernameCol = $c; break; } }
            if ($usernameCol) {
                $s = $pdo->prepare("SELECT id FROM users WHERE {$usernameCol} = ? LIMIT 1");
                $s->execute([$username]);
                $r = $s->fetch();
                if ($r) $user_id = $r['id'];
            }
        } catch (Exception $e) {
            // ignore and leave user_id null
        }
    }
    if (!$title) {
        echo json_encode(['success' => false, 'message' => 'Missing title']);
        exit;
    }
    $stmt = $pdo->prepare('INSERT INTO products (user_id, title, price, qty) VALUES (?, ?, ?, ?)');
    try {
        $stmt->execute([$user_id, $title, $price, $qty]);
        echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

if ($method === 'DELETE') {
    // parse id from input
    $id = intval($data['id'] ?? 0);
    if (!$id) { echo json_encode(['success' => false, 'message' => 'Missing id']); exit; }
    $stmt = $pdo->prepare('DELETE FROM products WHERE id = ?');
    try {
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Unsupported method']);
