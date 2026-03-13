<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/review_helpers.php';

$reviews = [];
$error = '';
$flash = $_SESSION['flash_message'] ?? '';
unset($_SESSION['flash_message']);

try {
    $pdo = getPdo();
    ensureReviewsTable($pdo);
    $stmt = $pdo->query('SELECT * FROM lp_reviews ORDER BY updated_at DESC, id DESC');
    $reviews = $stmt->fetchAll();
} catch (Throwable $e) {
    $error = 'レビュー一覧の取得に失敗しました。';
}

$genderOptions = reviewGenderOptions();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>レビュー一覧 | 管理画面</title>
  <link rel="stylesheet" href="./assets/css/style.css">
</head>
<body>
  <div class="admin-layout">
    <aside class="sidebar">
      <h1 class="brand">Banner Admin</h1>
      <nav class="nav">
        <span class="nav-label">バナー管理</span>
        <a href="./dashboard.php">一覧</a>
        <a href="./create.php">新規作成</a>
        <span class="nav-label">レビュー管理</span>
        <a href="./review_dashboard.php" class="active">一覧</a>
        <a href="./review_create.php">新規作成</a>
        <a href="./logout.php">ログアウト</a>
      </nav>
    </aside>

    <main class="main">
      <header class="page-head">
        <h2 class="page-title">受講者レビュー一覧</h2>
        <a href="./review_create.php" class="btn btn-primary">+ 新規作成</a>
      </header>

      <?php if ($flash): ?>
      <p class="message message-success"><?= htmlspecialchars($flash, ENT_QUOTES, 'UTF-8') ?></p>
      <?php endif; ?>

      <?php if ($error): ?>
      <p class="message message-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
      <?php endif; ?>

      <section class="card table-wrap">
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>属性</th>
              <th>評価</th>
              <th>コメント</th>
              <th>表示対象ページ</th>
              <th>更新日時</th>
              <th>編集</th>
              <th>削除</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!$reviews): ?>
            <tr>
              <td colspan="8">データがありません。</td>
            </tr>
            <?php else: ?>
              <?php foreach ($reviews as $review): ?>
              <?php $gender = (string)($review['gender'] ?? 'male'); ?>
              <tr>
                <td><?= (int)$review['id'] ?></td>
                <td>
                  <div class="review-person">
                    <span class="review-avatar <?= $gender === 'female' ? 'is-female' : 'is-male' ?>"><?= htmlspecialchars(reviewAvatarLabel($gender), ENT_QUOTES, 'UTF-8') ?></span>
                    <span><?= htmlspecialchars((string)$review['age_group'] . '・' . ($genderOptions[$gender] ?? '男性'), ENT_QUOTES, 'UTF-8') ?></span>
                  </div>
                </td>
                <td>
                  <span class="review-stars" aria-label="<?= (int)$review['rating'] ?>点"><?= str_repeat('★', (int)$review['rating']) ?></span>
                  <span class="review-score"><?= (int)$review['rating'] ?></span>
                </td>
                <td class="review-comment-cell"><?= htmlspecialchars((string)$review['comment'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string)$review['target_page'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string)$review['updated_at'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><a href="./review_edit.php?id=<?= (int)$review['id'] ?>" class="btn btn-secondary">編集</a></td>
                <td><button class="btn btn-danger" data-open-delete data-id="<?= (int)$review['id'] ?>">削除</button></td>
              </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </section>
    </main>
  </div>

  <div class="modal-backdrop" data-delete-modal data-delete-url="./review_delete.php">
    <div class="modal">
      <h2>削除の確認</h2>
      <p>このレビューを削除しますか？この操作は取り消せません。</p>
      <div class="actions">
        <button class="btn btn-secondary" data-close-delete>キャンセル</button>
        <button class="btn btn-danger" data-confirm-delete>削除する</button>
      </div>
    </div>
  </div>

  <script src="./assets/js/main.js"></script>
</body>
</html>
