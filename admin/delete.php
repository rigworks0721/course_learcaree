<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../db.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    $_SESSION['flash_message'] = '不正なIDです。';
    header('Location: dashboard.php');
    exit;
}

$pdo = getPdo();

try {
    $selectStmt = $pdo->prepare('SELECT image_path FROM popup_banners WHERE id = ? LIMIT 1');
    $selectStmt->execute([$id]);
    $banner = $selectStmt->fetch();

    if (!$banner) {
        $_SESSION['flash_message'] = '対象データが見つかりません。';
        header('Location: dashboard.php');
        exit;
    }

    $deleteStmt = $pdo->prepare('DELETE FROM popup_banners WHERE id = ?');
    $deleteStmt->execute([$id]);

    if (!empty($banner['image_path'])) {
        $imagePath = __DIR__ . '/..' . $banner['image_path'];
        if (is_file($imagePath)) {
            unlink($imagePath);
        }
    }

    $_SESSION['flash_message'] = 'バナーを削除しました。';
} catch (Throwable $e) {
    $_SESSION['flash_message'] = '削除に失敗しました。';
}

header('Location: dashboard.php');
exit;
