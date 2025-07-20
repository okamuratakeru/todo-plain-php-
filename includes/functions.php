<?php
// includes/functions.php

// HTML エスケープ
function h(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

// CSRF
function csrf_token(): string {
    if (!isset($_SESSION['token'])) {
        $_SESSION['token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['token'];
}

function verify_csrf(): void {
    if (($_POST['token'] ?? '') !== ($_SESSION['token'] ?? '')) {
        http_response_code(400);
        exit('Invalid CSRF token');
    }
}

// ——— ここからセッション／認証 ———

// セッション開始（呼び出し前に必ず session_start() を）
function init_session(): void {
    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
        // 'cookie_secure' => true, // HTTPS 環境なら有効化
    ]);
}

// ログイン済みかチェック
function require_login(): void {
    if (empty($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

// ログイン処理
function login(string $email, string $password): bool {
    $pdo = db();
    $stmt = $pdo->prepare('SELECT id, password_hash FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    error_log(print_r($user, true));
    if ($user && password_verify($password, $user['password_hash'])) {
        error_log('ログイン前のセッションID: ' . session_id());
        session_regenerate_id(true);
        error_log('ログイン後のセッションID: ' . session_id());
        $_SESSION['user_id'] = $user['id'];
        return true;
    }
    return false;
}

// ログアウト処理
function logout(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 3600,
            $p['path'], $p['domain'], $p['secure'], $p['httponly']
        );
    }
    session_destroy();
}
