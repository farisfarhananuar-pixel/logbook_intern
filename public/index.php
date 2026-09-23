<?php
require_once __DIR__ . '/../includes/auth.php';
$u = current_user();
if ($u) {
    header('Location: ' . ($u['role'] === 'student' ? '/student/dashboard.php' : '/supervisor/dashboard.php'));
} else {
    header('Location: /login.php');
}
exit;
