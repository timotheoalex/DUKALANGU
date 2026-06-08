<?php
header('Content-Type: application/json; charset=utf-8');
// Simple DB connectivity test. Returns JSON.
require __DIR__ . '/config/db.php';
try {
    $stmt = $pdo->query('SELECT 1');
    $stmt->execute();
    echo json_encode(['success' => true, 'message' => 'Connected to database: ' . ($DB_NAME ?? 'unknown')]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
