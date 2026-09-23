<?php
require_once __DIR__ . '/../includes/auth.php';
if (current_user()) {
    $u = current_user();
    header('Location: ' . ($u['role'] === 'student' ? '/student/dashboard.php' : '/supervisor/dashboard.php'));
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] === 'supervisor' ? 'supervisor' : 'student';

    if (!$name || !$email || strlen($password) < 4) {
        $error = 'Sila lengkapkan semua field. Password minimum 4 aksara.';
    } else {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Email sudah didaftarkan. Sila log masuk.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO users (role, name, email, password) VALUES (?,?,?,?)');
            $stmt->execute([$role, $name, $email, $hash]);
            $uid = $pdo->lastInsertId();
            if ($role === 'student') {
                $pdo->prepare('INSERT INTO student_profile (user_id) VALUES (?)')->execute([$uid]);
            }
            $_SESSION['user'] = ['id' => $uid, 'name' => $name, 'role' => $role, 'email' => $email];
            header('Location: ' . ($role === 'student' ? '/student/dashboard.php' : '/supervisor/dashboard.php'));
            exit;
        }
    }
}
?><!DOCTYPE html>
<html lang="ms">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Daftar - Practicum Logbook</title>
<link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<div class="login-wrap">
  <div class="login-card">
    <div class="logo-row"><span class="emoji">🌿</span></div>
    <h1>Daftar Akaun</h1>
    <p class="sub">Pilih peranan anda untuk mula guna sistem</p>

    <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <form method="post">
      <div class="role-toggle">
        <label><input type="radio" name="role" value="student" checked onclick="this.closest('form')"><span>🎓 Student</span></label>
        <label><input type="radio" name="role" value="supervisor"><span>🧑‍💼 Company Supervisor</span></label>
      </div>
      <label>Nama Penuh</label>
      <input type="text" name="name" required placeholder="Nama anda">
      <label>Username / Email</label>
      <input type="text" name="email" required placeholder="cth: farhan">
      <label>Password</label>
      <input type="password" name="password" required placeholder="Minimum 4 aksara">
      <button class="btn btn-primary btn-block" style="margin-top:18px" type="submit">Daftar & Log Masuk</button>
    </form>
    <p style="text-align:center;font-size:13px;margin-top:16px;color:var(--muted)">
      Dah ada akaun? <a href="/login.php" style="color:var(--emerald-700);font-weight:700">Log masuk</a>
    </p>
  </div>
</div>
<style>
.role-toggle label span{display:block;padding:8px 0;border-radius:8px}
.role-toggle input:checked + span{background:var(--emerald-600);color:#fff}
</style>
</body>
</html>
