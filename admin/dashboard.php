<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../db.php';

$banners = [];
$error = '';
$flash = $_SESSION['flash_message'] ?? '';
unset($_SESSION['flash_message']);

try {
    $pdo = getPdo();
    $stmt = $pdo->query('SELECT * FROM popup_banners ORDER BY sort_order ASC, id DESC');
    $banners = $stmt->fetchAll();
} catch (Throwable $e) {
    $error = '一覧取得に失敗しました。DB設定を確認してください。';
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>バナー一覧 | 管理画面</title>
  <link rel="stylesheet" href="./assets/css/style.css">
</head>
<body>
  <div class="admin-layout">
    <aside class="sidebar">
      <h1 class="brand">Banner Admin</h1>
      <nav class="nav">
        <span class="nav-label">バナー管理</span>
        <a href="./dashboard.php" class="active">一覧</a>
        <a href="./create.php">新規作成</a>
        <span class="nav-label">レビュー管理</span>
        <a href="./review_dashboard.php">一覧</a>
        <a href="./review_create.php">新規作成</a>
        <a href="./logout.php">ログアウト</a>
      </nav>
    </aside>

    <main class="main">
      <header class="page-head">
        <h2 class="page-title">ポップアップバナー一覧</h2>
        <a href="./create.php" class="btn btn-primary">+ 新規作成</a>
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
              <th>タイトル</th>
              <th>画像</th>
              <th>リンク先URL</th>
              <th>表示対象ページ</th>
              <th>表示秒数</th>
              <th>公開状態</th>
              <th>表示順</th>
              <th>更新日時</th>
              <th>編集</th>
              <th>削除</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!$banners): ?>
            <tr>
              <td colspan="11">データがありません。</td>
            </tr>
            <?php else: ?>
              <?php foreach ($banners as $banner): ?>
              <tr>
                <td><?= (int)$banner['id'] ?></td>
                <td><?= htmlspecialchars($banner['title'], ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                  <?php if (!empty($banner['image_path'])): ?>
                    <img class="thumb" src="<?= htmlspecialchars($banner['image_path'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($banner['title'], ENT_QUOTES, 'UTF-8') ?>">
                  <?php else: ?>
                    -
                  <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($banner['link_url'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($banner['target_pages'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= (int)$banner['display_delay'] ?>秒</td>
                <td>
                  <?php if ((int)$banner['is_active'] === 1): ?>
                    <span class="badge badge-public">公開</span>
                  <?php else: ?>
                    <span class="badge badge-private">非公開</span>
                  <?php endif; ?>
                </td>
                <td><?= (int)$banner['sort_order'] ?></td>
                <td><?= htmlspecialchars($banner['updated_at'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><a href="./edit.php?id=<?= (int)$banner['id'] ?>" class="btn btn-secondary">編集</a></td>
                <td><button class="btn btn-danger" data-open-delete data-id="<?= (int)$banner['id'] ?>">削除</button></td>
              </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </section>
    </main>
  </div>

  <div class="modal-backdrop" data-delete-modal data-delete-url="./delete.php">
    <div class="modal">
      <h2>削除の確認</h2>
      <p>このバナーを削除しますか？この操作は取り消せません。</p>
      <div class="actions">
        <button class="btn btn-secondary" data-close-delete>キャンセル</button>
        <button class="btn btn-danger" data-confirm-delete>削除する</button>
      </div>
    </div>
  </div>

  <script src="./assets/js/main.js"></script>
</body>
</html>
