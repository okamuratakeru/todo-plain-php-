<?php
// includes/db.php
function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'sqlite:' . __DIR__ . '/../database/todo.sqlite';
        $pdo = new PDO($dsn);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    return $pdo;
}
