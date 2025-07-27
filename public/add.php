<?php
require_once __DIR__.'/../includes/db.php';
require_once __DIR__.'/../includes/functions.php';
init_session();
require_login();
verify_csrf();

$title = trim($_POST['title'] ?? '');
$tags = trim($_POST['tags'] ?? '');
$tagNames = array_filter(array_map('trim', explode(',', $tags)));

if ($title === '') { header('Location: index.php'); exit; }

$pdo = db();
$pdo->beginTransaction();
try {
    /* ① tasks に挿入 */
    $pdo->prepare('INSERT INTO tasks(user_id,title) VALUES(?,?)')
        ->execute([$_SESSION['user_id'], $title]);
    $taskId = $pdo->lastInsertId();

    /* ② タグごとに INSERT OR IGNORE */
    $insTag = $pdo->prepare('INSERT OR IGNORE INTO tags(name) VALUES(?)');
    $selTag = $pdo->prepare('SELECT id FROM tags WHERE name = ?');
    $map    = $pdo->prepare('INSERT INTO tasks_tags(task_id,tag_id) VALUES(?,?)');

    foreach ($tagNames as $name) {
        if ($name === '') continue;
        $insTag->execute([$name]);          // 新規なら挿入
        $selTag->execute([$name]);
        $tagId = $selTag->fetchColumn();
        $map->execute([$taskId, $tagId]);   // 中間テーブルへ
    }
    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack(); throw $e;
}
header('Location: index.php');
exit;