<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/review_helpers.php';

$pdo = getPdo();
ensureReviewsTable($pdo);
$errors = [];

$formData = [
    'posted_date' => date('Y-m-d'),
    'age_group' => '30代',
    'gender' => 'male',
    'rating' => 5,
    'comment' => '',
    'target_page' => '/kampo',
];

$ageOptions = reviewAgeOptions();
$genderOptions = reviewGenderOptions();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData['posted_date'] = trim((string)($_POST['posted_date'] ?? ''));
    $formData['age_group'] = trim((string)($_POST['age_group'] ?? ''));
    $formData['gender'] = trim((string)($_POST['gender'] ?? 'male'));
    $formData['rating'] = (int)($_POST['rating'] ?? 0);
    $formData['comment'] = trim((string)($_POST['comment'] ?? ''));
    $formData['target_page'] = trim((string)($_POST['target_page'] ?? ''));

    if ($formData['posted_date'] !== '' && DateTime::createFromFormat('Y-m-d', $formData['posted_date']) === false) {
        $errors[] = '投稿日の形式が不正です。';
    }

    if (!in_array($formData['age_group'], $ageOptions, true)) {
        $errors[] = '年代を選択してください。';
    }

    if (!array_key_exists($formData['gender'], $genderOptions)) {
        $errors[] = '性別を選択してください。';
    }

    if ($formData['rating'] < 1 || $formData['rating'] > 5) {
        $errors[] = '評価は1〜5で選択してください。';
    }

    if ($formData['comment'] === '') {
        $errors[] = 'コメントは必須です。';
    } elseif (mb_strlen($formData['comment']) > 120) {
        $errors[] = 'コメントは120文字以内で入力してください。';
    }

    if ($formData['target_page'] === '') {
        $errors[] = '表示対象ページは必須です。';
    }

    if (!$errors) {
        try {
            $stmt = $pdo->prepare('INSERT INTO lp_reviews (posted_date, age_group, gender, rating, comment, target_page, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())');
            $stmt->execute([
                $formData['posted_date'] !== '' ? $formData['posted_date'] : null,
                $formData['age_group'],
                $formData['gender'],
                $formData['rating'],
                $formData['comment'],
                $formData['target_page'],
            ]);
            $_SESSION['flash_message'] = 'レビューを登録しました。';
            header('Location: review_dashboard.php');
            exit;
        } catch (Throwable $e) {
            $errors[] = 'レビューの登録に失敗しました。';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>レビュー新規作成 | 管理画面</title>
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
        <a href="./review_dashboard.php">一覧</a>
        <a href="./review_create.php" class="active">新規作成</a>
        <a href="./logout.php">ログアウト</a>
      </nav>
    </aside>

    <main class="main">
      <header class="page-head">
        <h2 class="page-title">レビュー新規作成</h2>
      </header>

      <?php foreach ($errors as $error): ?>
      <p class="message message-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
      <?php endforeach; ?>

      <section class="card">
        <form method="post">
          <div class="form-grid">
            <div class="field">
              <label for="posted_date">投稿日（任意）</label>
              <input id="posted_date" name="posted_date" type="date" value="<?= htmlspecialchars($formData['posted_date'], ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="field">
              <label for="age_group">年代</label>
              <select id="age_group" name="age_group" required>
                <?php foreach ($ageOptions as $age): ?>
                  <option value="<?= htmlspecialchars($age, ENT_QUOTES, 'UTF-8') ?>" <?= $formData['age_group'] === $age ? 'selected' : '' ?>><?= htmlspecialchars($age, ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="field full">
              <label>性別</label>
              <div class="radio-row">
                <?php foreach ($genderOptions as $genderValue => $genderLabel): ?>
                  <label class="radio-item">
                    <input type="radio" name="gender" value="<?= htmlspecialchars($genderValue, ENT_QUOTES, 'UTF-8') ?>" <?= $formData['gender'] === $genderValue ? 'checked' : '' ?>>
                    <span><?= htmlspecialchars($genderLabel, ENT_QUOTES, 'UTF-8') ?></span>
                  </label>
                <?php endforeach; ?>
              </div>
            </div>

            <div class="field">
              <label for="rating">評価（1〜5）</label>
              <select id="rating" name="rating" required>
                <?php for ($score = 5; $score >= 1; $score--): ?>
                  <option value="<?= $score ?>" <?= (int)$formData['rating'] === $score ? 'selected' : '' ?>><?= str_repeat('★', $score) ?>（<?= $score ?>）</option>
                <?php endfor; ?>
              </select>
            </div>

            <div class="field">
              <label for="target_page">表示対象ページ</label>
              <input id="target_page" name="target_page" type="text" value="<?= htmlspecialchars($formData['target_page'], ENT_QUOTES, 'UTF-8') ?>" placeholder="例: /kampo" required>
            </div>

            <div class="field full">
              <label for="comment">コメント（全角60文字程度）</label>
              <textarea id="comment" name="comment" rows="4" maxlength="120" required><?= htmlspecialchars($formData['comment'], ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>
          </div>

          <div class="actions" style="margin-top: 20px;">
            <button type="submit" class="btn btn-primary">登録する</button>
            <a href="./review_dashboard.php" class="btn btn-secondary">一覧へ戻る</a>
          </div>
        </form>
      </section>
    </main>
  </div>
</body>
</html>
