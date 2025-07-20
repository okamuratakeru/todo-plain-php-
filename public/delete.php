<?php
require_once __DIR__.'/../includes/db.php';
require_once __DIR__.'/../includes/functions.php';
init_session();
require_login();
verify_csrf();

$id = trim($_POST['id'] ?? '');
if ($title !== '') {
  $pdo = db();
  $stm = $pdo->prepare('DELETE FROM tasks WHERE id = :id AND user_id = :user_id');
  $stm->execute([':id' => $id, 'user_id' => $_SESSION['user_id']]);
}
header('Location: index.php');