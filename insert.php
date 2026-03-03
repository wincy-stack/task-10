<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $product = trim($_POST['product'] ?? '');
    $amount  = floatval($_POST['amount'] ?? 0);

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare('INSERT INTO users (name,email) VALUES (?, ?)');
        $stmt->execute([$name, $email]);
        $userId = $pdo->lastInsertId();

        $stmt2 = $pdo->prepare(
            'INSERT INTO orders (user_id, product, amount) VALUES (?, ?, ?)'
        );
        $stmt2->execute([$userId, $product, $amount]);

        $pdo->commit();
        header('Location: landing.php');
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        die('Insert failed: ' . $e->getMessage());
    }
}