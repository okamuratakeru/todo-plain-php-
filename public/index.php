<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
init_session();
require_login();

$userid = $_SESSION['user_id'];
$email = db()->query("SELECT email FROM users WHERE id = $userid")->fetchColumn();
$stmt = db()->prepare('SELECT * FROM tasks WHERE user_id = ? ORDER BY id DESC');
$stmt->execute([$userid]);
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
    <li data-id="<?= h($t['id']) ?>">
      <form action="checkUpdate.php" method="post" style="display:inline">
        <input type="hidden" name="id" value="<?= h($t['id']) ?>">
        <input type="hidden" name="is_done" value="<?= h($t['is_done']) ?>">
        <input type="hidden" name="token" value="<?= h(csrf_token()) ?>">
        <button><?= $t['is_done'] ? '☑' : '☐' ?></button>
      </form>
      <span class="title <?= $t['is_done'] ? 'done' : '' ?>"><?= h($t['title']) ?></span>
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
</body></html>

<script>
const csrf = '<?= csrf_token() ?>';

document.querySelectorAll('.title').forEach(title => {
  title.addEventListener('dblclick', () => {
    const span  = title;
    const li    = span.closest('li');
    const id    = li.dataset.id;
    const input = document.createElement('input');
    input.value = span.textContent;
    span.replaceWith(input);
    input.focus();

    let finished = false;
    const finish = async () => {
      if (finished) return;
      finished = true;
      if (!input.isConnected) return; // すでにDOMから削除されていたら何もしない

      const newTxt = input.value.trim();
      if (!newTxt) { input.replaceWith(span); return; }   // 空ならキャンセル

      // 楽観的 UI：先に画面更新
      span.textContent = newTxt;
      input.replaceWith(span);

      // DB 更新
      await fetch('update.php', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: `id=${id}&title=${encodeURIComponent(newTxt)}&token=${csrf}`
      }).catch(() => location.reload());   // エラー時はリロードで整合
    };

    input.addEventListener('blur', () => finish());
    input.addEventListener('keydown', e => e.key === 'Enter' && finish());
  });
});

document.querySelectorAll('.btn-delete').forEach(btn => {
  btn.addEventListener('click', (e) => {
    e.preventDefault();
    const li = btn.closest('li');
    const id = li.dataset.id;
    document.getElementById('del-id').value = id;
    document.getElementById('confirm-modal').removeAttribute('hidden');
  });
});

document.getElementById('confirm-yes').onclick = () => {
  document.getElementById('del-form').submit();
};
document.getElementById('confirm-no').onclick = () => {
  document.getElementById('confirm-modal').setAttribute('hidden', '');
};

</script>