<?php
require_once __DIR__.'/../includes/db.php';
require_once __DIR__.'/../includes/functions.php';
init_session();
require_login();
verify_csrf();

$id = trim($_POST['id'] ?? 0);
$title = trim($_POST['title'] ?? '');
if ($id !== 0 && $title !== '') {
  $pdo = db();
  $stm = $pdo->prepare('UPDATE tasks SET title = :title WHERE id = :id AND user_id = :user_id');
  $stm->execute([
    ':id' => $id, 
    ':title' => $title, 
    ':user_id' => $_SESSION['user_id'],
  ]);
}
header('Location: index.php');
?>