<?php
require_once 'config.php';

if (isset($_GET['delete'])) {
    $u_id = intval($_GET['delete']);

    try {
        $pdo->beginTransaction();

        // remove child records first (or rely on FK with ON DELETE CASCADE)
        $pdo->prepare('DELETE FROM orders WHERE user_id = ?')
            ->execute([$u_id]);
        $pdo->prepare('DELETE FROM users WHERE user_id = ?')
            ->execute([$u_id]);

        $pdo->commit();
        header('Location: landing.php');
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        die('Delete failed: ' . $e->getMessage());
    }
}