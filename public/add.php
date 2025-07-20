<?php
require_once __DIR__.'/../includes/db.php';
require_once __DIR__.'/../includes/functions.php';
init_session();
require_login();
verify_csrf();

$title = trim($_POST['title'] ?? '');
if ($title !== '') {
    $pdo = db();
    $stm = $pdo->prepare('INSERT INTO tasks (user_id, title) VALUES (:user_id, :title)');
    $stm->execute([':user_id' => $_SESSION['user_id'], ':title' => $title]);
}
header('Location: index.php');