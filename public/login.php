<?php
require_once __DIR__ . '/../includes/auth.php';
if (current_user()) {
    $u = current_user();
    header('Location: ' . ($u['role'] === 'student' ? '/student/dashboard.php' : '/supervisor/dashboard.php'));
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $row = $stmt->fetch();
    if ($row && password_verify($password, $row['password'])) {
        $_SESSION['user'] = ['id' => $row['id'], 'name' => $row['name'], 'role' => $row['role'], 'email' => $row['email']];
        header('Location: ' . ($row['role'] === 'student' ? '/student/dashboard.php' : '/supervisor/dashboard.php'));
        exit;
    } else {
        $error = 'Email atau password salah.';
    }
}
?><!DOCTYPE html>
<html lang="ms">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Log Masuk - Practicum Logbook</title>
<link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<div class="login-wrap">
  <div class="login-card">
    <div class="logo-row"><span class="emoji">🌿</span></div>
    <h1>Practicum Logbook</h1>
    <p class="sub">Log masuk untuk teruskan logbook praktikal anda</p>

    <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <form method="post">
      <label>Email</label>
      <input type="email" name="email" required placeholder="nama@email.com">
      <label>Password</label>
      <input type="password" name="password" required placeholder="••••••••">
      <button class="btn btn-primary btn-block" style="margin-top:18px" type="submit">Log Masuk</button>
    </form>
    <p style="text-align:center;font-size:13px;margin-top:16px;color:var(--muted)">
      Belum ada akaun? <a href="/register.php" style="color:var(--emerald-700);font-weight:700">Daftar di sini</a>
    </p>
  </div>
</div>
</body>
</html>
