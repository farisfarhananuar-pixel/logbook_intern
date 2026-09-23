<?php
require_once __DIR__ . '/../../includes/auth.php';
$u = require_login('student');

$profile = $pdo->prepare('SELECT * FROM student_profile WHERE user_id = ?');
$profile->execute([$u['id']]);
$profile = $profile->fetch();

$totalEntries = $pdo->prepare('SELECT COUNT(*) c FROM logbook_entries WHERE user_id = ?');
$totalEntries->execute([$u['id']]);
$totalEntries = $totalEntries->fetch()['c'];

$totalWeeks = $pdo->prepare('SELECT COUNT(*) c, SUM(supervisor_signature IS NOT NULL) signed FROM weekly_comments WHERE user_id = ?');
$totalWeeks->execute([$u['id']]);
$totalWeeks = $totalWeeks->fetch();

$pageTitle = 'Dashboard';
$active = 'dashboard';
include __DIR__ . '/../../includes/header.php';
?>

<div class="card">
  <h2>Selamat Datang, <?= htmlspecialchars($u['name']) ?> 👋</h2>
  <p style="color:var(--muted)">Ini ringkasan progress logbook praktikal anda.</p>
  <?php if (empty($profile['company_name'])): ?>
    <div class="alert alert-error">Anda belum isi <b>Detail of Student</b>. <a href="/student/profile.php" style="color:#b3261e;text-decoration:underline">Isi sekarang</a>.</div>
  <?php endif; ?>
  <div class="grid-2" style="margin-top:10px">
    <div class="field-group"><legend>📔 Jumlah Entri Logbook</legend><div style="font-size:28px;font-weight:800;color:var(--emerald-700)"><?= (int)$totalEntries ?></div></div>
    <div class="field-group"><legend>✍️ Minggu Ditandatangani</legend><div style="font-size:28px;font-weight:800;color:var(--emerald-700)"><?= (int)($totalWeeks['signed'] ?? 0) ?> / <?= (int)($totalWeeks['c'] ?? 0) ?></div></div>
  </div>
</div>

<div class="card">
  <h3>Tindakan Pantas</h3>
  <div style="display:flex;gap:10px;flex-wrap:wrap">
    <a class="btn btn-primary" href="/student/entries.php">➕ Tambah Entri Minggu Ini</a>
    <a class="btn btn-gold" href="/student/profile.php">🧾 Kemaskini Detail Student</a>
    <a class="btn btn-blue" href="/student/print.php">🖨️ Print / Export PDF</a>
  </div>
</div>

<?php if (!empty($profile['company_name'])): ?>
<div class="card">
  <h3>Maklumat Syarikat</h3>
  <table class="print-table">
    <tr><th>Company</th><td><?= htmlspecialchars($profile['company_name']) ?></td></tr>
    <tr><th>Tempoh Praktikal</th><td><?= htmlspecialchars($profile['practicum_start']) ?> — <?= htmlspecialchars($profile['practicum_end']) ?></td></tr>
    <tr><th>Company Supervisor</th><td><?= htmlspecialchars($profile['company_supervisor_name']) ?></td></tr>
  </table>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
