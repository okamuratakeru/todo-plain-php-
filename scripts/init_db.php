// scripts/init_db.php
<?php
$dbFile = __DIR__ . '/../database/todo.sqlite';
if (file_exists($dbFile)) unlink($dbFile);

$pdo = new PDO('sqlite:' . $dbFile);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$pdo->exec(<<<'SQL'
PRAGMA foreign_keys = ON;
CREATE TABLE IF NOT EXISTS users (
  id            INTEGER PRIMARY KEY AUTOINCREMENT,
  email         TEXT    NOT NULL UNIQUE,
  password_hash TEXT    NOT NULL,
  created_at    TEXT    NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS tasks (
  id          INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id     INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  title       TEXT    NOT NULL,
  is_done     INTEGER NOT NULL DEFAULT 0,
  created_at  TEXT    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  TEXT    NOT NULL DEFAULT CURRENT_TIMESTAMP
);
SQL);

echo "✅ database initialized at {$dbFile}\n";
