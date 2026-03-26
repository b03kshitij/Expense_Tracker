<?php
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit;
    }
}

function setRememberCookie($userId, $email) {
    $token = bin2hex(random_bytes(32));
    setcookie('remember_token', $token, time() + (86400 * 30), "/", "", false, true);
    setcookie('remember_email', $email, time() + (86400 * 30), "/", "", false, true);
    $_SESSION['remember_token'] = $token;
}

function clearRememberCookie() {
    setcookie('remember_token', '', time() - 3600, "/");
    setcookie('remember_email', '', time() - 3600, "/");
}
