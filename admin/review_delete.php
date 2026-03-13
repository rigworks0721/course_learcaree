<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/review_helpers.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    $_SESSION['flash_message'] = '不正なIDです。';
    header('Location: review_dashboard.php');
    exit;
}

$pdo = getPdo();
ensureReviewsTable($pdo);

try {
    $stmt = $pdo->prepare('DELETE FROM lp_reviews WHERE id = ?');
    $stmt->execute([$id]);
    $_SESSION['flash_message'] = 'レビューを削除しました。';
} catch (Throwable $e) {
    $_SESSION['flash_message'] = 'レビューの削除に失敗しました。';
}

header('Location: review_dashboard.php');
exit;
