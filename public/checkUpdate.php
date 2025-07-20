<?php
session_start();
require_once __DIR__.'/../includes/db.php';
require_once __DIR__.'/../includes/functions.php';
require_login();
verify_csrf();

$id = trim($_POST['id'] ?? '');
$isDone = trim($_POST['is_done'] ?? '');

if ($id !== '') {
    if ($isDone == 0) {
      $isDone = 1;
    }elseif($isDone == 1) {
      $isDone = 0;
    }
    $pdo = db();
    $stm = $pdo->prepare('UPDATE tasks SET is_done = :isDone, updated_at = CURRENT_TIMESTAMP WHERE id = :id AND user_id = :user_id');
    $stm->execute([':id' => $id, ':isDone' => $isDone, ':user_id' => $_SESSION['user_id']]);
}
header('Location: index.php');         