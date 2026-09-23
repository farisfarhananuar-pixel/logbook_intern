<?php
require_once __DIR__ . '/../../includes/auth.php';
$u = require_login('supervisor');

$studentId = (int)($_GET['id'] ?? 0);
$stStmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'student'");
$stStmt->execute([$studentId]);
$student = $stStmt->fetch();
if (!$student) { header('Location: /supervisor/dashboard.php'); exit; }

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $text = trim($_POST['summary_text'] ?? '');
    $signature = $_POST['signature_data'] ?? '';
    $existing = $pdo->prepare('SELECT id FROM employer_summary WHERE user_id = ?');
    $existing->execute([$studentId]);
    $row = $existing->fetch();
    if ($row) {
        $sql = 'UPDATE employer_summary SET summary_text = ?' . ($signature ? ', signature = ?, signed_by = ?, signed_at = NOW()' : '') . ' WHERE id = ?';
        $params = [$text];
        if ($signature) { $params[] = $signature; $params[] = $u['id']; }
        $params[] = $row['id'];
        $pdo->prepare($sql)->execute($params);
    } else {
        $stmt = $pdo->prepare('INSERT INTO employer_summary (user_id, summary_text, signature, signed_by, signed_at) VALUES (?,?,?,?,' . ($signature ? 'NOW()' : 'NULL') . ')');
        $stmt->execute([$studentId, $text, $signature ?: null, $signature ? $u['id'] : null]);
    }
    $msg = 'Employer\'s summary berjaya disimpan.';
}

$summary = $pdo->prepare('SELECT * FROM employer_summary WHERE user_id = ?');
$summary->execute([$studentId]);
$summary = $summary->fetch() ?: [];

$pageTitle = "Employer's Summary";
$active = 'dashboard';
include __DIR__ . '/../../includes/header.php';
?>

<div class="card">
  <a href="/supervisor/student_view.php?id=<?= $studentId ?>" style="font-size:13px;color:var(--emerald-700)">← Kembali</a>
  <h2 style="margin-top:10px">📝 Employer's Summary — <?= htmlspecialchars($student['name']) ?></h2>
  <?php if ($msg): ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>

  <form method="post">
    <label>Ringkasan Penilaian Keseluruhan</label>
    <textarea name="summary_text" style="min-height:180px" required><?= htmlspecialchars($summary['summary_text'] ?? '') ?></textarea>

    <label>Tandatangan / Cop Syarikat</label>
    <div class="sig-wrap"><canvas id="sigPad" class="sig-pad"></canvas></div>
    <input type="hidden" name="signature_data" id="sigData">
    <div class="sig-actions"><button type="button" id="sigClear" class="btn btn-outline btn-sm">🧹 Padam Tandatangan</button></div>

    <?php if (!empty($summary['signature'])): ?>
      <p style="margin-top:10px;font-size:13px;color:var(--muted)">Tandatangan sedia ada:</p>
      <img src="<?= $summary['signature'] ?>" style="max-width:200px">
    <?php endif; ?>

    <button class="btn btn-primary" style="margin-top:16px" type="submit">💾 Simpan Summary</button>
  </form>
</div>

<script src="/assets/js/signature.js"></script>
<script>initSignaturePad('sigPad','sigData','sigClear');</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
