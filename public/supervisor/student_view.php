<?php
require_once __DIR__ . '/../../includes/auth.php';
$u = require_login('supervisor');

$studentId = (int)($_GET['id'] ?? 0);
$stStmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'student'");
$stStmt->execute([$studentId]);
$student = $stStmt->fetch();
if (!$student) { header('Location: /supervisor/dashboard.php'); exit; }

$profile = $pdo->prepare('SELECT * FROM student_profile WHERE user_id = ?');
$profile->execute([$studentId]);
$profile = $profile->fetch() ?: [];

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_week'])) {
    $weekStart = $_POST['week_start'] ?? '';
    $weekEnd = $_POST['week_end'] ?? '';
    $comment = trim($_POST['supervisor_comment'] ?? '');
    $signature = $_POST['signature_data'] ?? '';

    if (!$weekStart || !$weekEnd || !$comment) {
        $msg = ['type' => 'error', 'text' => 'Sila isi tarikh minggu dan komen.'];
    } else {
        $existing = $pdo->prepare('SELECT id FROM weekly_comments WHERE user_id = ? AND week_start = ? AND week_end = ?');
        $existing->execute([$studentId, $weekStart, $weekEnd]);
        $row = $existing->fetch();
        if ($row) {
            $sql = 'UPDATE weekly_comments SET supervisor_comment = ?' .
                ($signature ? ', supervisor_signature = ?, signed_by = ?, signed_at = NOW()' : '') .
                ' WHERE id = ?';
            $params = [$comment];
            if ($signature) { $params[] = $signature; $params[] = $u['id']; }
            $params[] = $row['id'];
            $pdo->prepare($sql)->execute($params);
        } else {
            $stmt = $pdo->prepare('INSERT INTO weekly_comments (user_id, week_start, week_end, supervisor_comment, supervisor_signature, signed_by, signed_at) VALUES (?,?,?,?,?,?,' . ($signature ? 'NOW()' : 'NULL') . ')');
            $stmt->execute([$studentId, $weekStart, $weekEnd, $comment, $signature ?: null, $signature ? $u['id'] : null]);
        }
        $msg = ['type' => 'success', 'text' => 'Komen & tandatangan minggu berjaya disimpan.'];
    }
}

$entries = $pdo->prepare('SELECT * FROM logbook_entries WHERE user_id = ? ORDER BY entry_date DESC');
$entries->execute([$studentId]);
$entries = $entries->fetchAll();

$weeks = $pdo->prepare('SELECT * FROM weekly_comments WHERE user_id = ? ORDER BY week_start DESC');
$weeks->execute([$studentId]);
$weeks = $weeks->fetchAll();

$pageTitle = 'Semak ' . $student['name'];
$active = 'dashboard';
include __DIR__ . '/../../includes/header.php';
?>

<div class="card">
  <a href="/supervisor/dashboard.php" style="font-size:13px;color:var(--emerald-700)">← Kembali ke senarai student</a>
  <h2 style="margin-top:10px">🎓 <?= htmlspecialchars($student['name']) ?></h2>
  <table class="print-table">
    <tr><th>Company</th><td><?= htmlspecialchars($profile['company_name'] ?? '—') ?></td></tr>
    <tr><th>Tempoh Praktikal</th><td><?= htmlspecialchars($profile['practicum_start'] ?? '') ?> — <?= htmlspecialchars($profile['practicum_end'] ?? '') ?></td></tr>
  </table>
  <a class="btn btn-blue btn-sm" href="/supervisor/employer_summary.php?id=<?= $studentId ?>" style="margin-top:10px;display:inline-block">📝 Isi Employer's Summary</a>
</div>

<div class="card">
  <h3>📔 Entri Logbook Student (<?= count($entries) ?>)</h3>
  <?php foreach ($entries as $e): ?>
    <div class="entry-item">
      <?php if ($e['photo']): ?><img src="<?= $e['photo'] ?>" alt="foto"><?php endif; ?>
      <div class="entry-meta">
        <div class="entry-date">📅 <?= htmlspecialchars($e['entry_date']) ?></div>
        <p style="margin:6px 0"><?= nl2br(htmlspecialchars($e['description'])) ?></p>
      </div>
    </div>
  <?php endforeach; ?>
  <?php if (!$entries): ?><p style="color:var(--muted)">Belum ada entri.</p><?php endif; ?>
</div>

<div class="card">
  <h3>✍️ Beri Komen & Tandatangan Mingguan</h3>
  <?php if ($msg): ?><div class="alert alert-<?= $msg['type']==='error'?'error':'success' ?>"><?= htmlspecialchars($msg['text']) ?></div><?php endif; ?>
  <form method="post">
    <input type="hidden" name="save_week" value="1">
    <div class="grid-2">
      <div><label>Minggu Bermula (isi sendiri)</label><input type="date" name="week_start" required></div>
      <div><label>Minggu Berakhir (isi sendiri)</label><input type="date" name="week_end" required></div>
    </div>
    <label>Komen Supervisor untuk minggu ini</label>
    <textarea name="supervisor_comment" required placeholder="Contoh: Student menunjukkan prestasi baik dalam..."></textarea>

    <label>Tandatangan Digital</label>
    <div class="sig-wrap">
      <canvas id="sigPad" class="sig-pad"></canvas>
    </div>
    <input type="hidden" name="signature_data" id="sigData">
    <div class="sig-actions">
      <button type="button" id="sigClear" class="btn btn-outline btn-sm">🧹 Padam Tandatangan</button>
    </div>

    <button class="btn btn-primary" style="margin-top:16px" type="submit">💾 Simpan Komen & Tandatangan</button>
  </form>
</div>

<div class="card">
  <h3>🗂️ Sejarah Komen Mingguan</h3>
  <?php foreach ($weeks as $w): ?>
    <div style="border:1px solid var(--emerald-200);border-radius:10px;padding:12px;margin-bottom:10px">
      <b><?= htmlspecialchars($w['week_start']) ?> — <?= htmlspecialchars($w['week_end']) ?></b>
      <span class="badge <?= $w['supervisor_signature'] ? 'badge-signed' : 'badge-pending' ?>"><?= $w['supervisor_signature'] ? '✅ Signed' : '⏳ Belum sign' ?></span>
      <p style="margin:6px 0"><?= nl2br(htmlspecialchars($w['supervisor_comment'])) ?></p>
      <?php if ($w['supervisor_signature']): ?><img src="<?= $w['supervisor_signature'] ?>" style="max-width:160px"><?php endif; ?>
    </div>
  <?php endforeach; ?>
  <?php if (!$weeks): ?><p style="color:var(--muted)">Belum ada komen mingguan.</p><?php endif; ?>
</div>

<script src="/assets/js/signature.js"></script>
<script>initSignaturePad('sigPad','sigData','sigClear');</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
