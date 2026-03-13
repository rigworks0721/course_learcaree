<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../db.php';

$pdo = getPdo();
$errors = [];
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

if ($id <= 0) {
    $_SESSION['flash_message'] = '不正なIDです。';
    header('Location: dashboard.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM popup_banners WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$banner = $stmt->fetch();

if (!$banner) {
    $_SESSION['flash_message'] = '対象データが見つかりません。';
    header('Location: dashboard.php');
    exit;
}

$formData = [
    'title' => $banner['title'],
    'link_url' => $banner['link_url'],
    'target_pages' => $banner['target_pages'],
    'display_delay' => (int)$banner['display_delay'],
    'is_active' => (int)$banner['is_active'],
    'sort_order' => (int)$banner['sort_order'],
    'image_path' => $banner['image_path'],
];

function uploadReplacementImage(array $file, array &$errors): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        $errors[] = '画像アップロードに失敗しました。';
        return null;
    }

    if (($file['size'] ?? 0) <= 0) {
        $errors[] = '空ファイルはアップロードできません。';
        return null;
    }

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    $mime = mime_content_type($file['tmp_name']);
    if (!isset($allowed[$mime])) {
        $errors[] = '画像は jpg / jpeg / png / webp のみ対応です。';
        return null;
    }

    $uploadDir = __DIR__ . '/../uploads/banners';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
        $errors[] = 'アップロード先ディレクトリの作成に失敗しました。';
        return null;
    }

    $fileName = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
    $destination = $uploadDir . '/' . $fileName;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        $errors[] = '画像保存に失敗しました。';
        return null;
    }

    return '/uploads/banners/' . $fileName;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData['title'] = trim($_POST['title'] ?? '');
    $formData['link_url'] = trim($_POST['link_url'] ?? '');
    $formData['target_pages'] = trim($_POST['target_pages'] ?? '');
    // 将来的には target_pages を正規化して中間テーブルで管理する想定。
    $formData['display_delay'] = max(1, (int)($_POST['display_delay'] ?? 1));
    $formData['is_active'] = (int)($_POST['is_active'] ?? 0) === 1 ? 1 : 0;
    $formData['sort_order'] = max(1, (int)($_POST['sort_order'] ?? 1));

    if ($formData['title'] === '') {
        $errors[] = 'タイトルは必須です。';
    }

    if (!empty($formData['link_url']) && !filter_var($formData['link_url'], FILTER_VALIDATE_URL)) {
        $errors[] = 'リンク先URLの形式が不正です。';
    }

    $newImagePath = null;
    if (isset($_FILES['image'])) {
        $newImagePath = uploadReplacementImage($_FILES['image'], $errors);
    }

    if (!$errors) {
        $finalImagePath = $newImagePath ?? $formData['image_path'];

        try {
            $updateStmt = $pdo->prepare('UPDATE popup_banners SET title = ?, image_path = ?, link_url = ?, target_pages = ?, display_delay = ?, is_active = ?, sort_order = ?, updated_at = NOW() WHERE id = ?');
            $updateStmt->execute([
                $formData['title'],
                $finalImagePath,
                $formData['link_url'],
                $formData['target_pages'],
                $formData['display_delay'],
                $formData['is_active'],
                $formData['sort_order'],
                $id,
            ]);

            if ($newImagePath !== null && !empty($formData['image_path'])) {
                $oldPath = __DIR__ . '/..' . $formData['image_path'];
                if (is_file($oldPath)) {
                    unlink($oldPath);
                }
            }

            $_SESSION['flash_message'] = 'バナーを更新しました。';
            header('Location: dashboard.php');
            exit;
        } catch (Throwable $e) {
            $errors[] = '更新に失敗しました。';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>編集 | 管理画面</title>
  <link rel="stylesheet" href="./assets/css/style.css">
</head>
<body>
  <div class="admin-layout">
    <aside class="sidebar">
      <h1 class="brand">Banner Admin</h1>
      <nav class="nav">
        <a href="./dashboard.php" class="active">一覧</a>
        <a href="./create.php">新規作成</a>
        <a href="./logout.php">ログアウト</a>
      </nav>
    </aside>

    <main class="main">
      <header class="page-head">
        <h2 class="page-title">バナー編集（ID: <?= $id ?>）</h2>
      </header>

      <?php foreach ($errors as $error): ?>
      <p class="message message-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
      <?php endforeach; ?>

      <section class="card">
        <form method="post" enctype="multipart/form-data">
          <input type="hidden" name="id" value="<?= $id ?>">
          <div class="form-grid">
            <div class="field full">
              <label for="title">タイトル</label>
              <input id="title" name="title" type="text" value="<?= htmlspecialchars($formData['title'], ENT_QUOTES, 'UTF-8') ?>" required>
            </div>

            <div class="field">
              <label for="image">画像アップロード</label>
              <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp" data-image-input data-preview-target="#editPreview">
            </div>

            <div class="field">
              <label>画像プレビュー</label>
              <div id="editPreview" class="preview-box">
                <?php if (!empty($formData['image_path'])): ?>
                  <img src="<?= htmlspecialchars($formData['image_path'], ENT_QUOTES, 'UTF-8') ?>" alt="現在の登録画像">
                <?php else: ?>
                  <span>画像が選択されていません</span>
                <?php endif; ?>
              </div>
            </div>

            <div class="field full">
              <label for="url">リンク先URL</label>
              <input id="url" name="link_url" type="url" value="<?= htmlspecialchars($formData['link_url'], ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="field">
              <label for="target">表示対象ページ</label>
              <input id="target" name="target_pages" type="text" value="<?= htmlspecialchars($formData['target_pages'], ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="field">
              <label for="seconds">表示秒数</label>
              <input id="seconds" name="display_delay" type="number" min="1" value="<?= (int)$formData['display_delay'] ?>">
            </div>

            <div class="field">
              <label for="status">公開/非公開</label>
              <select id="status" name="is_active">
                <option value="1" <?= (int)$formData['is_active'] === 1 ? 'selected' : '' ?>>公開</option>
                <option value="0" <?= (int)$formData['is_active'] === 0 ? 'selected' : '' ?>>非公開</option>
              </select>
            </div>

            <div class="field">
              <label for="order">表示順</label>
              <input id="order" name="sort_order" type="number" min="1" value="<?= (int)$formData['sort_order'] ?>">
            </div>
          </div>

          <div class="actions" style="margin-top: 20px;">
            <button type="submit" class="btn btn-primary">更新する</button>
            <a href="./dashboard.php" class="btn btn-secondary">一覧へ戻る</a>
          </div>
        </form>
      </section>
    </main>
  </div>

  <script src="./assets/js/main.js"></script>
</body>
</html>
