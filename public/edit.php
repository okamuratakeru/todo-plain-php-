<?php
require_once __DIR__.'/../includes/db.php';
require_once __DIR__.'/../includes/functions.php';
init_session();
require_login();
verify_csrf();

$id = trim($_POST['id'] ?? '');
if ($id !== '') {
  $pdo = db();
  $stm = $pdo->prepare('SELECT * FROM tasks WHERE id = :id AND user_id = :user_id');
  $stm->execute([':id' => $id, 'user_id' => $_SESSION['user_id']]);
  $task = $stm->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>編集</title>
</head>
<body>
  <h1>編集</h1>
  <form action="update.php" method="post">
    <input type="hidden" name="id" value="<?= h($task['id']) ?>">
    <input type="hidden" name="token" value="<?= h(csrf_token()) ?>">
    <input type="text" name="title" value="<?= h($task['title']) ?>">
    <button>更新</button>
  </form>
</body>
</html>