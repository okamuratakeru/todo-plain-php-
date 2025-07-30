<?php
require '../includes/functions.php';
require '../includes/db.php';
init_session();  require_login();

$pdo = db();

/* POST: 色更新 */
if ($_SERVER['REQUEST_METHOD']==='POST') {
    verify_csrf();
    $id    = (int)($_POST['id']??0);
    $color = preg_match('/^#[0-9A-Fa-f]{6}$/', $_POST['color']??'')
            ? strtoupper($_POST['color'])
            : '#888888';
    $pdo->prepare('UPDATE tags SET color_hex=? WHERE id=?')
        ->execute([$color, $id]);
    header('Location: tags.php'); exit;
}

/* 一覧取得 */
$tags = $pdo->query('SELECT * FROM tags ORDER BY name')->fetchAll();
?>
<!doctype html><html><head><meta charset="utf-8"><title>タグ管理</title>
<style>
.badge{display:inline-block;padding:.15em .5em;border-radius:.5em;color:#fff}
</style>
</head><body>
<h1>タグ管理</h1>
<table border="1" cellpadding="6">
<?php foreach($tags as $tg): ?>
<tr>
  <td>
    <span class="badge" style="background:<?=h($tg['color_hex'])?>"><?=h($tg['name'])?></span>
  </td>
  <td>
    <form method="post" style="display:inline">
      <input type="color" name="color" value="<?=h($tg['color_hex'])?>">
      <input type="hidden" name="id"    value="<?= $tg['id'] ?>">
      <input type="hidden" name="token" value="<?=h(csrf_token())?>">
      <button>変更</button>
    </form>
  </td>
</tr>
<?php endforeach ?>
</table>
<p><a href="index.php">← 戻る</a></p>
</body></html>
