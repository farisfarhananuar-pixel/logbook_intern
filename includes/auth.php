<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/db.php';

function current_user() {
    return $_SESSION['user'] ?? null;
}

function require_login($role = null) {
    $u = current_user();
    if (!$u) {
        header('Location: /login.php');
        exit;
    }
    if ($role && $u['role'] !== $role) {
        header('Location: /login.php');
        exit;
    }
    return $u;
}

function base_path($path = '') {
    // helper untuk generate link relatif dari root /public
    return $path;
}
