<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/db.php';

$path = $_GET['path'] ?? '';

if (!is_string($path) || $path === '') {
    echo json_encode([
        'success' => true,
        'data' => null,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

try {
    $pdo = getPdo();

    $stmt = $pdo->query(
        'SELECT id, title, image_path, link_url, target_pages, display_delay, is_active, sort_order
         FROM popup_banners
         WHERE is_active = 1
         ORDER BY sort_order ASC, id DESC'
    );

    $rows = $stmt->fetchAll();
    $matched = null;

    foreach ($rows as $row) {
        $pages = array_map('trim', explode(',', (string) ($row['target_pages'] ?? '')));

        if (in_array($path, $pages, true)) {
            $matched = [
                'id' => (int) $row['id'],
                'title' => (string) ($row['title'] ?? ''),
                'image_path' => (string) ($row['image_path'] ?? ''),
                'link_url' => (string) ($row['link_url'] ?? ''),
                'target_pages' => (string) ($row['target_pages'] ?? ''),
                'display_delay' => (int) ($row['display_delay'] ?? 0),
                'is_active' => (int) ($row['is_active'] ?? 0),
                'sort_order' => (int) ($row['sort_order'] ?? 0),
            ];
            break;
        }
    }

    echo json_encode([
        'success' => true,
        'data' => $matched,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'DB error',
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
