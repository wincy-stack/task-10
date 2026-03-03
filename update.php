<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $u_id    = intval($_POST['user_id'] ?? 0);
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $product = trim($_POST['product'] ?? '');
    $amount  = floatval($_POST['amount'] ?? 0);

    try {
        $pdo->beginTransaction();

        $stmt1 = $pdo->prepare(
            'UPDATE users SET name = ?, email = ? WHERE user_id = ?'
        );
        $stmt1->execute([$name, $email, $u_id]);

        $stmt2 = $pdo->prepare(
            'UPDATE orders SET product = ?, amount = ? WHERE user_id = ?'
        );
        $stmt2->execute([$product, $amount, $u_id]);

        $pdo->commit();
        header('Location: landing.php');
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        die('Update failed: ' . $e->getMessage());
    }
}