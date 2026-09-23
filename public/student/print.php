<?php
require_once __DIR__ . '/../../includes/auth.php';
$u = require_login('student');

$profile = $pdo->prepare('SELECT * FROM student_profile WHERE user_id = ?');
$profile->execute([$u['id']]);
$profile = $profile->fetch() ?: [];
function v($p, $k) { return htmlspecialchars($p[$k] ?? ''); }

$entries = $pdo->prepare('SELECT * FROM logbook_entries WHERE user_id = ? ORDER BY entry_date ASC, id ASC');
$entries->execute([$u['id']]);
$entries = $entries->fetchAll();

$weeks = $pdo->prepare('SELECT * FROM weekly_comments WHERE user_id = ? ORDER BY week_start ASC');
$weeks->execute([$u['id']]);
$weeks = $weeks->fetchAll();

$summary = $pdo->prepare('SELECT * FROM employer_summary WHERE user_id = ?');
$summary->execute([$u['id']]);
$summary = $summary->fetch();

// bahagikan entries ikut minggu yang ada, & simpan baki entries yang belum masuk mana-mana minggu
$usedEntryIds = [];
function entriesInRange($entries, $start, $end, &$used) {
    $out = [];
    foreach ($entries as $e) {
        if (in_array($e['id'], $used)) continue;
        if ($e['entry_date'] >= $start && $e['entry_date'] <= $end) {
            $out[] = $e;
            $used[] = $e['id'];
        }
    }
    return $out;
}

$pageTitle = 'Print PDF';
$active = 'print';
include __DIR__ . '/../../includes/header.php';
?>

<div class="card no-print">
  <h2>🖨️ Print / Export PDF (A4)</h2>
  <p style="color:var(--muted)">Klik butang di bawah, pilih "Save as PDF" pada destinasi printer untuk export logbook lengkap dalam saiz A4.</p>
  <button class="btn btn-primary" onclick="window.print()">🖨️ Print / Save as PDF</button>
</div>

<!-- PAGE 1: Cover asal UUM - tidak diubah -->
<div class="print-page cover">
  <img src="/assets/images/cover.jpg" alt="UUM Practicum Logbook Cover">
</div>

<!-- PAGE 2: Detail of Students -->
<div class="print-page">
  <div class="print-title">DETAIL OF STUDENTS</div>
  <table class="print-table">
    <tr><th>Name</th><td><?= htmlspecialchars($u['name']) ?></td></tr>
    <tr><th>Matric No.</th><td><?= v($profile,'matric_no') ?></td></tr>
    <tr><th>Telephone</th><td><?= v($profile,'telephone') ?></td></tr>
    <tr><th>Email</th><td><?= htmlspecialchars($u['email']) ?></td></tr>
    <tr><th>Home Address</th><td><?= nl2br(v($profile,'home_address')) ?></td></tr>
    <tr><th>Practicum Duration</th><td><?= v($profile,'practicum_start') ?> — <?= v($profile,'practicum_end') ?></td></tr>
    <tr><th class="detail-yellow">Company Name</th><td><?= v($profile,'company_name') ?></td></tr>
    <tr><th class="detail-yellow">Company Address</th><td><?= nl2br(v($profile,'company_address')) ?></td></tr>
    <tr><th>Company Supervisor</th><td><?= v($profile,'company_supervisor_name') ?></td></tr>
    <tr><th>Designation</th><td><?= v($profile,'company_supervisor_designation') ?></td></tr>
    <tr><th>Telephone</th><td><?= v($profile,'company_supervisor_telephone') ?></td></tr>
    <tr><th>Email</th><td><?= v($profile,'company_supervisor_email') ?></td></tr>
    <tr><th>Fax</th><td><?= v($profile,'company_supervisor_fax') ?></td></tr>
    <tr><th>UUM Report Supervisor</th><td><?= v($profile,'uum_report_supervisor') ?></td></tr>
    <tr><th>UUM Visiting Supervisor</th><td><?= v($profile,'uum_visiting_supervisor') ?></td></tr>
  </table>
</div>

<!-- PAGES: setiap minggu -->
<?php foreach ($weeks as $w):
    $weekEntries = entriesInRange($entries, $w['week_start'], $w['week_end'], $usedEntryIds);
?>
<div class="print-page">
  <div class="print-title">WEEKLY LOG: <?= htmlspecialchars($w['week_start']) ?> &mdash; <?= htmlspecialchars($w['week_end']) ?></div>
  <?php foreach ($weekEntries as $e): ?>
    <div style="border:1px solid var(--emerald-200);border-radius:10px;padding:10px;margin-bottom:10px">
      <b>📅 <?= htmlspecialchars($e['entry_date']) ?></b>
      <?php if ($e['photo']): ?><div style="margin:6px 0"><img src="<?= $e['photo'] ?>" style="max-width:180px;border-radius:8px"></div><?php endif; ?>
      <p style="margin:4px 0"><?= nl2br(htmlspecialchars($e['description'])) ?></p>
    </div>
  <?php endforeach; ?>
  <?php if (!$weekEntries): ?><p style="color:var(--muted)">Tiada entri direkodkan minggu ini.</p><?php endif; ?>

  <table class="print-table" style="margin-top:14px">
    <tr><th>Supervisor's Comment</th><td><?= nl2br(htmlspecialchars($w['supervisor_comment'] ?: '—')) ?></td></tr>
    <tr><th>Signed At</th><td><?= htmlspecialchars($w['signed_at'] ?: 'Belum ditandatangani') ?></td></tr>
    <tr><th>Supervisor's Signature</th><td>
      <?php if ($w['supervisor_signature']): ?>
        <img src="<?= $w['supervisor_signature'] ?>" style="max-width:200px">
      <?php else: ?>
        <span style="color:var(--muted)">— belum sign —</span>
      <?php endif; ?>
    </td></tr>
  </table>
</div>
<?php endforeach; ?>

<?php
$remaining = array_filter($entries, fn($e) => !in_array($e['id'], $usedEntryIds));
if ($remaining):
?>
<div class="print-page">
  <div class="print-title">ENTRI BELUM DISAHKAN SUPERVISOR</div>
  <?php foreach ($remaining as $e): ?>
    <div style="border:1px solid var(--emerald-200);border-radius:10px;padding:10px;margin-bottom:10px">
      <b>📅 <?= htmlspecialchars($e['entry_date']) ?></b>
      <?php if ($e['photo']): ?><div style="margin:6px 0"><img src="<?= $e['photo'] ?>" style="max-width:180px;border-radius:8px"></div><?php endif; ?>
      <p style="margin:4px 0"><?= nl2br(htmlspecialchars($e['description'])) ?></p>
    </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- PAGE: Employer's Summary -->
<div class="print-page">
  <div class="print-title">EMPLOYER'S SUMMARY</div>
  <p style="white-space:pre-line;min-height:250px"><?= htmlspecialchars($summary['summary_text'] ?? '') ?: '—' ?></p>
  <table class="print-table" style="margin-top:20px">
    <tr><th>Date</th><td><?= htmlspecialchars($summary['signed_at'] ?? '' ?: 'Belum ditandatangani') ?></td></tr>
    <tr><th>Employer's Signature / Stamp</th><td>
      <?php if (!empty($summary['signature'])): ?>
        <img src="<?= $summary['signature'] ?>" style="max-width:220px">
      <?php else: ?>
        <span style="color:var(--muted)">— belum sign —</span>
      <?php endif; ?>
    </td></tr>
  </table>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
