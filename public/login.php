<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
init_session();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    if (login($_POST['email'] ?? '', $_POST['password'] ?? '')) {
        header('Location: index.php');
        exit;
    }
    $errors[] = 'メールアドレスまたはパスワードが違います';
}
?>
<!doctype html>
<html lang="ja"><head><meta charset="utf-8"><title>ログイン</title></head><body>
  <h1>ログイン</h1>
  <?php if (isset($_GET['registered'])): ?>
    <p style="color:green">登録が完了しました。ログインしてください。</p>
  <?php endif; ?>
  <?php foreach ($errors as $e): ?>
    <p style="color:red"><?= h($e) ?></p>
  <?php endforeach; ?>
  <form method="post">
    <input type="hidden" name="token" value="<?= h(csrf_token()) ?>">
    <label>メール: <input type="email" name="email" required></label><br>
    <label>パスワード: <input type="password" name="password" required></label><br>
    <button>ログイン</button>
  </form>
  <p><a href="register.php">新規登録はこちら</a></p>
</body></html>
