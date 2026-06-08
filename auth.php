<?php
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/../config/db.php';

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents('php://input'), true) ?: $_POST;

if ($method === 'POST') {
    $action = $data['action'] ?? '';
    if ($action === 'register') {
        $username = trim($data['username'] ?? '');
        $password = $data['password'] ?? '';
        if (!$username || !$password) {
            echo json_encode(['success' => false, 'message' => 'Missing username or password']);
            exit;
        }
        $hash = password_hash($password, PASSWORD_DEFAULT);
        // Ensure `username` column exists, try to add if missing
        try {
            $colCheck = $pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'username'");
            $colCheck->execute();
            if (!$colCheck->fetch()) {
                // add username column
                $pdo->exec("ALTER TABLE users ADD COLUMN username VARCHAR(100) NULL UNIQUE");
            }
        } catch (Exception $e) {
            // ignore schema alteration errors
        }

        $stmt = $pdo->prepare('INSERT INTO users (username, password) VALUES (?, ?)');
        try {
            $stmt->execute([$username, $hash]);
            echo json_encode(['success' => true, 'user' => ['id' => $pdo->lastInsertId(), 'username' => $username]]);
        } catch (PDOException $e) {
            // if insert fails due to missing columns, return readable message
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    } elseif ($action === 'login') {
        $username = trim($data['username'] ?? '');
        $password = $data['password'] ?? '';
        if (!$username || !$password) {
            echo json_encode(['success' => false, 'message' => 'Missing username or password']);
            exit;
        }
        // Determine which column stores the username (username, name, or email)
        try {
            $colStmt = $pdo->prepare("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users'");
            $colStmt->execute();
            $cols = $colStmt->fetchAll(PDO::FETCH_COLUMN);
            $usernameCol = null;
            foreach (['username','name','email'] as $c) { if (in_array($c, $cols)) { $usernameCol = $c; break; } }
            $passwordCol = in_array('password', $cols) ? 'password' : (in_array('pass', $cols) ? 'pass' : null);
            if (!$usernameCol || !$passwordCol) {
                echo json_encode(['success' => false, 'message' => 'Users table missing required columns. Please run installer to update schema.']);
                exit;
            }

            $sql = "SELECT id, {$usernameCol} AS uname, {$passwordCol} AS pw FROM users WHERE {$usernameCol} = ? LIMIT 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            if ($user && password_verify($password, $user['pw'])) {
                echo json_encode(['success' => true, 'user' => ['id' => $user['id'], 'username' => $user['uname']]]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
            }
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
}

echo json_encode(['success' => false, 'message' => 'Unsupported method/action']);
