<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../db.php';

$pdo = getPdo();
$errors = [];

$formData = [
    'title' => '',
    'link_url' => '',
    'target_pages' => '',
    'display_delay' => 5,
    'is_active' => 1,
    'sort_order' => 1,
];

function uploadBannerImage(array $file, array &$errors): ?string
{
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

    if (!isset($_FILES['image']) || ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        $errors[] = '画像は必須です。';
    }

    $imagePath = null;
    if (!$errors) {
        $imagePath = uploadBannerImage($_FILES['image'], $errors);
    }

    if (!$errors && $imagePath !== null) {
        try {
            $stmt = $pdo->prepare('INSERT INTO popup_banners (title, image_path, link_url, target_pages, display_delay, is_active, sort_order, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())');
            $stmt->execute([
                $formData['title'],
                $imagePath,
                $formData['link_url'],
                $formData['target_pages'],
                $formData['display_delay'],
                $formData['is_active'],
                $formData['sort_order'],
            ]);

            $_SESSION['flash_message'] = 'バナーを登録しました。';
            header('Location: dashboard.php');
            exit;
        } catch (Throwable $e) {
            $errors[] = '登録に失敗しました。';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>新規作成 | 管理画面</title>
  <link rel="stylesheet" href="./assets/css/style.css">
</head>
<body>
  <div class="admin-layout">
    <aside class="sidebar">
      <h1 class="brand">Banner Admin</h1>
      <nav class="nav">
        <a href="./dashboard.php">一覧</a>
        <a href="./create.php" class="active">新規作成</a>
        <a href="./logout.php">ログアウト</a>
      </nav>
    </aside>

    <main class="main">
      <header class="page-head">
        <h2 class="page-title">バナー新規作成</h2>
      </header>

      <?php foreach ($errors as $error): ?>
      <p class="message message-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
      <?php endforeach; ?>

      <section class="card">
        <form method="post" enctype="multipart/form-data">
          <div class="form-grid">
            <div class="field full">
              <label for="title">タイトル</label>
              <input id="title" name="title" type="text" value="<?= htmlspecialchars($formData['title'], ENT_QUOTES, 'UTF-8') ?>" placeholder="例: 新生活キャンペーン" required>
            </div>

            <div class="field">
              <label for="image">画像アップロード</label>
              <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp" data-image-input data-preview-target="#createPreview" required>
            </div>

            <div class="field">
              <label>画像プレビュー</label>
              <div id="createPreview" class="preview-box">
                <span>画像が選択されていません</span>
              </div>
            </div>

            <div class="field full">
              <label for="url">リンク先URL</label>
              <input id="url" name="link_url" type="url" value="<?= htmlspecialchars($formData['link_url'], ENT_QUOTES, 'UTF-8') ?>" placeholder="https://example.com/campaign">
            </div>

            <div class="field">
              <label for="target">表示対象ページ</label>
              <input id="target" name="target_pages" type="text" value="<?= htmlspecialchars($formData['target_pages'], ENT_QUOTES, 'UTF-8') ?>" placeholder="/about/,/service/,/lp/campaign/">
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
            <button type="submit" class="btn btn-primary">保存する</button>
            <a href="./dashboard.php" class="btn btn-secondary">一覧へ戻る</a>
          </div>
        </form>
      </section>
    </main>
  </div>

  <script src="./assets/js/main.js"></script>
</body>
</html>
