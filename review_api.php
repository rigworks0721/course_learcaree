<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/db.php';

$path = $_GET['path'] ?? '';

if (!is_string($path) || $path === '') {
    echo json_encode(['success' => true, 'data' => []], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

try {
    $pdo = getPdo();
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS lp_reviews (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            posted_date DATE NULL,
            age_group VARCHAR(20) NOT NULL,
            gender VARCHAR(10) NOT NULL,
            rating TINYINT UNSIGNED NOT NULL,
            comment VARCHAR(255) NOT NULL,
            target_page VARCHAR(100) NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_target_page (target_page),
            INDEX idx_updated_at (updated_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
    );

    $stmt = $pdo->prepare('SELECT id, posted_date, age_group, gender, rating, comment, target_page, updated_at FROM lp_reviews WHERE target_page = ? ORDER BY updated_at DESC, id DESC');
    $stmt->execute([$path]);
    $rows = $stmt->fetchAll();

    $data = array_map(static function (array $row): array {
        return [
            'id' => (int)$row['id'],
            'posted_date' => (string)($row['posted_date'] ?? ''),
            'age_group' => (string)$row['age_group'],
            'gender' => (string)$row['gender'],
            'rating' => (int)$row['rating'],
            'comment' => (string)$row['comment'],
            'target_page' => (string)$row['target_page'],
            'updated_at' => (string)$row['updated_at'],
        ];
    }, $rows);

    echo json_encode(['success' => true, 'data' => $data], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'DB error'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
