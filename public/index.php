<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
init_session();
require_login();

$allTags = db()->query('SELECT id, name, color_hex FROM tags ORDER BY name')
        ->fetchAll(PDO::FETCH_ASSOC);

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
<p><a href="tags.php">タグを編集</a></p>

<form action="add.php" method="post">
  <input name="title" placeholder="やることを書く" required>
  <!-- タグ選択エリア -->
  <div>
    <?php foreach ($allTags as $tg): ?>
      <label style="margin-right:.5em">
        <input type="checkbox" name="tag_ids[]" value="<?= $tg['id'] ?>">
        <span class="badge" style="background:<?= h($tg['color_hex']) ?>">
          <?= h($tg['name']) ?>
        </span>
      </label>
    <?php endforeach ?>
  </div>
  <input type="hidden" name="token" value="<?= h(csrf_token()) ?>">
  <button>追加</button>
</form>

<ul>
  <?php foreach ($tasks as $t): ?>
    <li data-id="<?= h($t['id']) ?>">
      <form action="checkUpdate.php" method="post" style="display:inline">
        <input type="hidden" name="id" value="<?= h($t['id']) ?>">
        <input type="hidden" name="is_done" value="<?= h($t['is_done']) ?>">
        <input type="hidden" name="token" value="<?= h(csrf_token()) ?>">
        <button><?= $t['is_done'] ? '☑' : '☐' ?></button>
      </form>
      <span class="title <?= $t['is_done'] ? 'done' : '' ?>"><?= h($t['title']) ?></span>
      <?php foreach (tags_of($t['id']) as $tag): ?>
        <a href="?tag=<?= $tag['id'] ?>"
          class="badge"
          style="background:<?= h($tag['color_hex']) ?>">
          <?= h($tag['name']) ?>
        </a>
      <?php endforeach ?>
      
      <form action="delete.php" id="del-form" method="post" style="display:inline">
        <input type="hidden" name="id" id="del-id" value="<?= h($t['id']) ?>">
        <input type="hidden" name="token" value="<?= h(csrf_token()) ?>">
        <button class="btn-delete">🗑</button>
      </form>
    </li>
  <?php endforeach; ?>
</ul>

<div id="confirm-modal" hidden>
  <div class="modal-box">
    <p>本当に削除しますか？</p>
    <button id="confirm-yes">削除</button>
    <button id="confirm-no">キャンセル</button>
  </div>
</div>
<script>
  const CSRF_TOKEN = '<?= csrf_token() ?>';
</script>
<script src="inline.js" defer></script>
<script src="deletePopup.js" defer></script>
</body></html>
