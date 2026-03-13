<?php
session_start();
require_once __DIR__ . '/../config.php';

if (!empty($_SESSION[ADMIN_SESSION_KEY])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = trim($_POST['password'] ?? '');

    if ($password === '') {
        $error = 'パスワードを入力してください。';
    } elseif (password_verify($password, ADMIN_PASSWORD_HASH)) {
        $_SESSION[ADMIN_SESSION_KEY] = true;
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'パスワードが違います。';
    }
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>管理画面ログイン | ポップアップバナー管理</title>
  <link rel="stylesheet" href="./assets/css/style.css">
</head>
<body>
  <main class="login-page">
    <section class="card login-card">
      <h1>管理画面ログイン</h1>
      <p>ポップアップバナー管理システム</p>

      <?php if ($error): ?>
      <p class="message message-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
      <?php endif; ?>

      <form action="" method="post">
        <div class="field" style="margin-top: 12px;">
          <label for="password">パスワード</label>
          <input type="password" id="password" name="password" placeholder="********" required>
        </div>

        <div class="actions" style="margin-top: 18px;">
          <button type="submit" class="btn btn-primary" style="width:100%;">ログイン</button>
        </div>
      </form>
    </section>
  </main>
</body>
</html>
