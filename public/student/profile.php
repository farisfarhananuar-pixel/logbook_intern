<?php
require_once __DIR__ . '/../../includes/auth.php';
$u = require_login('student');

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields = ['matric_no','telephone','home_address','practicum_start','practicum_end',
        'company_name','company_address','company_supervisor_name','company_supervisor_designation',
        'company_supervisor_telephone','company_supervisor_email','company_supervisor_fax',
        'uum_report_supervisor','uum_visiting_supervisor'];
    $vals = [];
    foreach ($fields as $f) { $vals[$f] = trim($_POST[$f] ?? ''); }

    $sql = 'UPDATE student_profile SET ' . implode(',', array_map(fn($f) => "$f = :$f", $fields)) . ' WHERE user_id = :uid';
    $stmt = $pdo->prepare($sql);
    $vals['uid'] = $u['id'];
    $stmt->execute($vals);
    $msg = 'Detail student berjaya disimpan.';
}

$profile = $pdo->prepare('SELECT * FROM student_profile WHERE user_id = ?');
$profile->execute([$u['id']]);
$profile = $profile->fetch() ?: [];
function v($p, $k) { return htmlspecialchars($p[$k] ?? ''); }

$pageTitle = 'Detail Student';
$active = 'profile';
include __DIR__ . '/../../includes/header.php';
?>

<div class="card">
  <h2>🧾 Detail of Student</h2>
  <p style="color:var(--muted)">Maklumat ini akan muncul di muka surat "Detail of Students" dalam PDF logbook anda. Isi & kemaskini bila-bila masa.</p>
  <?php if ($msg): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>

  <form method="post">
    <fieldset class="field-group">
      <legend>👤 Maklumat Diri</legend>
      <div class="grid-2">
        <div><label>No. Matrik</label><input type="text" name="matric_no" value="<?= v($profile,'matric_no') ?>"></div>
        <div><label>Telefon</label><input type="text" name="telephone" value="<?= v($profile,'telephone') ?>"></div>
      </div>
      <label>Home Address</label>
      <textarea name="home_address"><?= v($profile,'home_address') ?></textarea>
    </fieldset>

    <fieldset class="field-group">
      <legend>📅 Practicum Duration (isi sendiri)</legend>
      <div class="grid-2">
        <div><label>Tarikh Mula</label><input type="date" name="practicum_start" value="<?= v($profile,'practicum_start') ?>"></div>
        <div><label>Tarikh Tamat</label><input type="date" name="practicum_end" value="<?= v($profile,'practicum_end') ?>"></div>
      </div>
    </fieldset>

    <fieldset class="field-group">
      <legend>🏢 Company</legend>
      <label>Company Name</label>
      <input type="text" name="company_name" value="<?= v($profile,'company_name') ?>">
      <label>Company Address</label>
      <textarea name="company_address"><?= v($profile,'company_address') ?></textarea>
    </fieldset>

    <fieldset class="field-group">
      <legend>🧑‍💼 Company Supervisor</legend>
      <div class="grid-2">
        <div><label>Nama</label><input type="text" name="company_supervisor_name" value="<?= v($profile,'company_supervisor_name') ?>"></div>
        <div><label>Designation</label><input type="text" name="company_supervisor_designation" value="<?= v($profile,'company_supervisor_designation') ?>"></div>
        <div><label>Telefon</label><input type="text" name="company_supervisor_telephone" value="<?= v($profile,'company_supervisor_telephone') ?>"></div>
        <div><label>Email</label><input type="text" name="company_supervisor_email" value="<?= v($profile,'company_supervisor_email') ?>"></div>
        <div><label>Fax</label><input type="text" name="company_supervisor_fax" value="<?= v($profile,'company_supervisor_fax') ?>"></div>
      </div>
    </fieldset>

    <fieldset class="field-group">
      <legend>🎓 UUM Supervisor</legend>
      <div class="grid-2">
        <div><label>UUM Report Supervisor</label><input type="text" name="uum_report_supervisor" value="<?= v($profile,'uum_report_supervisor') ?>"></div>
        <div><label>UUM Visiting Supervisor</label><input type="text" name="uum_visiting_supervisor" value="<?= v($profile,'uum_visiting_supervisor') ?>"></div>
      </div>
    </fieldset>

    <button class="btn btn-primary" style="margin-top:18px" type="submit">💾 Simpan Detail</button>
  </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
