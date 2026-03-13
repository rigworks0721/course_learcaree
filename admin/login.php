<?php
session_start();
require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $password = $_POST['password'] ?? '';

    if (password_verify($password, ADMIN_PASSWORD_HASH)) {

        $_SESSION[ADMIN_SESSION] = true;
        header("Location: dashboard.php");
        exit;

    } else {
        $error = "パスワードが違います";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Admin Login</title>
</head>

<body>

<h2>管理画面ログイン</h2>

<?php if ($error): ?>
<p style="color:red;"><?php echo $error; ?></p>
<?php endif; ?>

<form method="post">
<input type="password" name="password" placeholder="パスワード">
<button type="submit">ログイン</button>
</form>

</body>
</html>