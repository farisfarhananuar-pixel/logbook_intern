<?php
require_once __DIR__ . '/../../includes/auth.php';
$u = require_login('supervisor');

$students = $pdo->query("
  SELECT us.id, us.name, us.email, sp.company_name, sp.practicum_start, sp.practicum_end,
    (SELECT COUNT(*) FROM logbook_entries le WHERE le.user_id = us.id) AS entry_count,
    (SELECT COUNT(*) FROM weekly_comments wc WHERE wc.user_id = us.id AND wc.supervisor_signature IS NOT NULL) AS signed_weeks
  FROM users us
  LEFT JOIN student_profile sp ON sp.user_id = us.id
  WHERE us.role = 'student'
  ORDER BY us.name ASC
")->fetchAll();

$pageTitle = 'Senarai Student';
$active = 'dashboard';
include __DIR__ . '/../../includes/header.php';
?>

<div class="card">
  <h2>🧑‍💼 Senarai Student Practicum</h2>
  <p style="color:var(--muted)">Semak logbook, beri komen mingguan dan tandatangan digital untuk setiap student.</p>
</div>

<?php foreach ($students as $s): ?>
<div class="card" style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap">
  <div>
    <h3 style="margin:0 0 4px"><?= htmlspecialchars($s['name']) ?></h3>
    <div style="color:var(--muted);font-size:13px"><?= htmlspecialchars($s['email']) ?></div>
    <div style="color:var(--muted);font-size:13px"><?= htmlspecialchars($s['company_name'] ?: 'Belum isi company') ?></div>
    <span class="badge badge-pending" style="margin-top:6px;display:inline-block">📔 <?= (int)$s['entry_count'] ?> entri</span>
    <span class="badge badge-signed" style="margin-top:6px;display:inline-block">✍️ <?= (int)$s['signed_weeks'] ?> minggu disahkan</span>
  </div>
  <a class="btn btn-primary" href="/supervisor/student_view.php?id=<?= $s['id'] ?>">Semak Logbook →</a>
</div>
<?php endforeach; ?>

<?php if (!$students): ?>
  <div class="card"><p style="color:var(--muted)">Belum ada student yang berdaftar lagi.</p></div>
<?php endif; ?>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
