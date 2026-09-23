<?php
// $active = nama tab semasa (set dalam setiap page sebelum include header.php)
$u = current_user();
?><!DOCTYPE html>
<html lang="ms">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' : '' ?>Practicum Logbook</title>
<link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<header class="app-header no-print">
  <div class="brand">🌿
    <div>Practicum Logbook<small><?= htmlspecialchars($u['name'] ?? '') ?> · <?= $u['role'] === 'student' ? 'Student' : 'Company Supervisor' ?></small></div>
  </div>
  <div style="display:flex;align-items:center;gap:10px">
    <span class="user-chip"><?= htmlspecialchars($u['name'] ?? '') ?></span>
    <form method="post" action="/logout.php" style="margin:0">
      <button class="logout-btn" type="submit">Log Keluar</button>
    </form>
  </div>
</header>
<div class="container">
<?php if ($u['role'] === 'student'): ?>
<nav class="tabs no-print">
  <a href="/student/dashboard.php" class="<?= ($active ?? '') === 'dashboard' ? 'active' : '' ?>">🏠 Dashboard</a>
  <a href="/student/profile.php" class="<?= ($active ?? '') === 'profile' ? 'active' : '' ?>">🧾 Detail Student</a>
  <a href="/student/entries.php" class="<?= ($active ?? '') === 'entries' ? 'active' : '' ?>">📔 Logbook Entries</a>
  <a href="/student/print.php" class="<?= ($active ?? '') === 'print' ? 'active' : '' ?>">🖨️ Print PDF</a>
</nav>
<?php else: ?>
<nav class="tabs no-print">
  <a href="/supervisor/dashboard.php" class="<?= ($active ?? '') === 'dashboard' ? 'active' : '' ?>">🏠 Senarai Student</a>
</nav>
<?php endif; ?>
