<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
init_session();
require_login();

$uid = $_SESSION['user_id'];
error_log(print_r($_SESSION, true));
$email = db()->query("SELECT email FROM users WHERE id = $uid")->fetchColumn();
$stmt = db()->prepare('SELECT * FROM tasks WHERE user_id = ? ORDER BY id DESC');
$stmt->execute([$uid]);
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ja"><head><meta charset="UTF-8"><title>Todo</title>
<link rel="stylesheet" href="style.css">
</head><body>
<h1><?= h($email) ?>の Todo</h1>
<p><a href="logout.php">ログアウト</a></p>

<form action="add.php" method="post">
  <input name="title" placeholder="やることを書く" required>
  <input type="hidden" name="token" value="<?= h(csrf_token()) ?>">
  <button>追加</button>
</form>

<ul>
<?php foreach ($tasks as $t): ?>
  <li>
    <form action="update.php" method="post" style="display:inline">
      <input type="hidden" name="id" value="<?= h($t['id']) ?>">
      <input type="hidden" name="is_done" value="<?= h($t['is_done']) ?>">
      <input type="hidden" name="token" value="<?= h(csrf_token()) ?>">
      <button><?= $t['is_done'] ? '☑' : '☐' ?></button>
    </form>
    <span class="<?= $t['is_done'] ? 'done' : '' ?>"><?= h($t['title']) ?></span>
    <form action="delete.php" method="post" style="display:inline">
      <input type="hidden" name="id" value="<?= h($t['id']) ?>">
      <input type="hidden" name="token" value="<?= h(csrf_token()) ?>">
      <button>🗑</button>
    </form>
  </li>
<?php endforeach; ?>
</ul>
</body></html>
