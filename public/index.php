<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
init_session();
require_login();

$userid = $_SESSION['user_id'];
$email = db()->query("SELECT email FROM users WHERE id = $userid")->fetchColumn();


$tagFilter = (int)($_GET['tag'] ?? 0);         //  ?tag=3 が来ていれば 3
$sql  = 'SELECT t.* FROM tasks t';
$vals = [$_SESSION['user_id']];

if ($tagFilter) {
  $sql .= ' JOIN tasks_tags tt ON t.id = tt.task_id
            WHERE t.user_id = ? AND tt.tag_id = ?';
  $vals[] = $tagFilter;                        // バインド配列に追加
} else {
  $sql .= ' WHERE t.user_id = ?';
}
$sql .= ' ORDER BY t.id DESC';

$stmt = db()->prepare($sql);
$stmt->execute($vals);
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

function tags_of(int $taskId): array {
  static $stmt = null;
  if (!$stmt) {
      $stmt = db()->prepare(
        'SELECT tags.id, name, color_hex
          FROM tags
          JOIN tasks_tags ON tags.id = tasks_tags.tag_id
          WHERE task_id = ?'
      );
  }
  $stmt->execute([$taskId]);
  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="ja"><head><meta charset="UTF-8"><title>Todo</title>
<link rel="stylesheet" href="style.css">
</head><body>
<h1><?= h($email) ?>の Todo</h1>
<p><a href="logout.php">ログアウト</a></p>

<form action="add.php" method="post">
  <input name="title" placeholder="やることを書く" required>
  <input name="tags"  placeholder="タグ(カンマ区切り)" style="width:14rem">
  <input type="hidden" name="token" value="<?= h(csrf_token()) ?>">
  <button>追加</button>
</form>

<ul>
<?php foreach ($tasks as $t): ?>
  <li>
    <form action="checkUpdate.php" method="post" style="display:inline">
      <input type="hidden" name="id" value="<?= h($t['id']) ?>">
      <input type="hidden" name="is_done" value="<?= h($t['is_done']) ?>">
      <input type="hidden" name="token" value="<?= h(csrf_token()) ?>">
      <button><?= $t['is_done'] ? '☑' : '☐' ?></button>
    </form>
    <span class="<?= $t['is_done'] ? 'done' : '' ?>"><?= h($t['title']) ?></span>
    <form action="edit.php" method="post" style="display:inline">
      <input type="hidden" name="id" value="<?= h($t['id']) ?>">
      <input type="hidden" name="token" value="<?= h(csrf_token()) ?>">
      <button>編集</button>
    </form>
    <form action="delete.php" method="post" style="display:inline">
      <input type="hidden" name="id" value="<?= h($t['id']) ?>">
      <input type="hidden" name="token" value="<?= h(csrf_token()) ?>">
      <button>🗑</button>
    </form>
  </li>
<?php endforeach; ?>
</ul>
</body></html>
