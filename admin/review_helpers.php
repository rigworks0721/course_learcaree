<?php

declare(strict_types=1);

function reviewAgeOptions(): array
{
    return ['20代', '30代', '40代', '50代', '60代以上'];
}

function reviewGenderOptions(): array
{
    return [
        'male' => '男性',
        'female' => '女性',
    ];
}

function ensureReviewsTable(PDO $pdo): void
{
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
}

function reviewAvatarLabel(string $gender): string
{
    return $gender === 'female' ? '女' : '男';
}
