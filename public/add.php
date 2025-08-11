<?php
require_once __DIR__.'/../includes/db.php';
require_once __DIR__.'/../includes/functions.php';
init_session();
require_login();
verify_csrf();

$title = trim($_POST['title'] ?? '');
$tagIds  = array_map('intval', $_POST['tag_ids'] ?? []);

error_log(print_r($tagIds, true));

if ($title === '') { header('Location: index.php'); exit; }

/**
 * 既存の色一覧と重複しないパステルカラー (#RRGGBB) を返す
 *
 * @param PDO $pdo       DB 接続
 * @param int $retryMax  試行回数（安全に 20 回くらいあれば十分）
 */
function uniqueRandomColor(PDO $pdo, int $retryMax = 20): string
{
    // 既存色を配列に
    static $cache = null;
    if ($cache === null) {
        $cache = $pdo->query('SELECT color_hex FROM tags')
                    ->fetchAll(PDO::FETCH_COLUMN);
    }

    for ($i = 0; $i < $retryMax; $i++) {
        // 0x70–0xFF のパステルレンジ
        $r = random_int(0x70, 0xFF);
        $g = random_int(0x70, 0xFF);
        $b = random_int(0x70, 0xFF);
        $hex = sprintf('#%02X%02X%02X', $r, $g, $b);

        if (!in_array($hex, $cache, true)) {
            $cache[] = $hex;          // キャッシュにも入れる
            return $hex;
        }
    }
    // 万一重複しまくったら Fallback
    return '#888888';
}

$pdo = db();
$pdo->beginTransaction();
try {
    /* ① tasks に挿入 */
    $pdo->prepare('INSERT INTO tasks(user_id,title) VALUES(?,?)')
        ->execute([$_SESSION['user_id'], $title]);
    $taskId = $pdo->lastInsertId();

    /* ② タグごとに INSERT OR IGNORE */
    $map = $pdo->prepare(
        'INSERT OR IGNORE INTO tasks_tags(task_id, tag_id) VALUES (?, ?)'
    );

    foreach (array_unique($tagIds) as $tgId) {
        if ($tgId > 0) {            // 念のため 0 やマイナスを除外
            $map->execute([$taskId, $tgId]);
        }
    }

    $pdo->commit();

} catch (Throwable $e) {
    $pdo->rollBack(); throw $e;
}
header('Location: index.php');
exit;