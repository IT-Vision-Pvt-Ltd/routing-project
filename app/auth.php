<?php
require_once __DIR__ . '/db.php';

function login($email, $password) {
    $stmt = db()->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1 LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user'] = [
            'id' => $user['id'],
            'email' => $user['email'],
            'full_name' => $user['full_name'],
            'role' => $user['role'],
        ];
        return true;
    }
    return false;
}

function require_login() {
    if (empty($_SESSION['user'])) {
        header('Location: /index.php');
        exit;
    }
}

function require_role($roles = []) {
    require_login();
    if (!in_array($_SESSION['user']['role'], $roles, true)) {
        http_response_code(403);
        echo "Forbidden";
        exit;
    }
}

function current_user() {
    return $_SESSION['user'] ?? null;
}

function logout_user() {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}
?>
