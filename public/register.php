<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
init_session();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo '<script>console.log("POSTメソッドです");</script>';
    error_log(print_r($_POST, true));
    verify_csrf();
    $email = trim($_POST['email'] ?? '');
    $pw    = $_POST['password'] ?? '';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'メール形式が不正です';
    }
    if (strlen($pw) < 8) {
        $errors[] = 'パスワードは8文字以上必要です';
    }
    if (empty($errors)) {
        $hash = password_hash($pw, PASSWORD_DEFAULT);
        try {
            db()->prepare('INSERT INTO users(email,password_hash) VALUES(?,?)')
                ->execute([$email, $hash]);
            header('Location: login.php?registered=1');
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $errors[] = 'このメールアドレスは既に使われています';
            } else {
                throw $e;
            }
        }
    }
}
?>
<!doctype html>
<html lang="ja"><head><meta charset="utf-8"><title>新規登録</title></head><body>
<h1>ユーザー登録</h1>
<?php foreach ($errors as $e): ?>
  <p style="color:red"><?= h($e) ?></p>
<?php endforeach; ?>
<form method="post">
  <input type="hidden" name="token" value="<?= h(csrf_token()) ?>">
  <label>メール: <input type="email" name="email" required></label><br>
  <label>パスワード: <input type="password" name="password" minlength="8" required></label><br>
  <button>登録</button>
</form>
<p><a href="login.php">ログインはこちら</a></p>
</body></html>
