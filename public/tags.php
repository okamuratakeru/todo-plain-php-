<?php
require '../includes/functions.php';
require '../includes/db.php';
init_session(); require_login();

$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id    = (int)($_POST['id'] ?? 0);
    $name  = trim($_POST['name'] ?? '');
    $color = preg_match('/^#[0-9A-Fa-f]{6}$/', $_POST['color'] ?? '')
            ? strtoupper($_POST['color'])
            : '#888888';

    // 片方だけ更新される可能性もあるので柔軟に
    $sql = 'UPDATE tags SET ';
    $sets = []; $vals = [];
    if ($name !== '') { $sets[] = 'name = ?';       $vals[] = $name; }
    if ($color)       { $sets[] = 'color_hex = ?';  $vals[] = $color; }
    $sql .= implode(', ', $sets) . ' WHERE id = ?';
    $vals[] = $id;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($vals);

    header('Content-Type: application/json');
    echo json_encode(['ok' => true]);
    exit;
}

/* 一覧取得 */
$tags = $pdo->query('SELECT * FROM tags ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>タグ管理</title>
<meta name="csrf-token" content="<?= h(csrf_token()) ?>">
<style>
.badge{display:inline-block;padding:.15em .5em;border-radius:.5em;color:#fff}
.inline-input{border:none;outline:none;padding:.2em .4em;border-radius:.4em}
td{vertical-align:middle}
</style>
</head>
<body>
<h1>タグ管理</h1>
<table border="1" cellpadding="6">
  <tbody>
  <?php foreach($tags as $tg): ?>
    <tr data-id="<?= $tg['id'] ?>">
      <td>
        <span class="badge tag-name"
              style="background:<?= h($tg['color_hex']) ?>"><?= h($tg['name']) ?></span>
      </td>
      <td>
        <input class="color-picker" type="color" value="<?= h($tg['color_hex']) ?>">
      </td>
    </tr>
  <?php endforeach ?>
  </tbody>
</table>
<p><a href="index.php">← 戻る</a></p>

<script>
// CSRF を meta から取得
const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;

// 共通の送信関数
async function saveTag(id, {name, color}) {
  const params = new URLSearchParams({ id, token: CSRF_TOKEN });
  if (name  != null) params.append('name',  name);
  if (color != null) params.append('color', color);
  const res = await fetch('tags.php', { method:'POST', body: params });
  if (!res.ok) throw new Error('save failed');
}

// ① 名前インライン編集（ダブルクリック）
document.querySelectorAll('.tag-name').forEach(span => {
  span.addEventListener('dblclick', () => {
    const tr    = span.closest('tr');
    const id    = tr.dataset.id;
    const color = tr.querySelector('.color-picker').value;

    const input = document.createElement('input');
    input.type  = 'text';
    input.value = span.textContent.trim();
    input.className = 'inline-input';
    input.style.background = span.style.background;
    input.style.color = '#fff';

    span.replaceWith(input);
    input.focus();
    input.select();

    let done = false;
    const finish = async (commit) => {
      if (done) return; done = true;
      const newName = commit ? input.value.trim() : null; // Esc時はnullで不更新
      const nameToShow = newName ?? span.textContent;
      span.textContent = nameToShow;
      input.replaceWith(span);

      if (newName && newName !== span.textContent) {
        // ここだと span.textContent が上で差し替わってるので比較用に保持した方が良いが、
        // とにかく保存だけすればOK
      }
      if (newName) {
        try { await saveTag(id, {name: newName}); }
        catch { location.reload(); }
      }
    };

    input.addEventListener('keydown', e => {
      if (e.key === 'Enter') finish(true);
      if (e.key === 'Escape') finish(false);
    });
    input.addEventListener('blur', () => finish(true));
  });
});

// ② 色は変更イベントで即保存（ボタン不要）
document.querySelectorAll('.color-picker').forEach(picker => {
  picker.addEventListener('input', async () => {
    // inputで即時、changeで確定時にしたいなら'change'に切替
    const tr   = picker.closest('tr');
    const id   = tr.dataset.id;
    const name = tr.querySelector('.tag-name').textContent.trim();
    const color = picker.value;

    // 見た目を先に更新（楽観的）
    tr.querySelector('.tag-name').style.background = color;

    try { await saveTag(id, {color}); }
    catch { location.reload(); }
  });
});
</script>
</body>
</html>
