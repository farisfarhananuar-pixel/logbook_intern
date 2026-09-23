<?php
require_once __DIR__ . '/../../includes/auth.php';
$u = require_login('student');

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_entry'])) {
    $date = $_POST['entry_date'] ?? '';
    $desc = trim($_POST['description'] ?? '');
    $photoData = null;

    if (!empty($_FILES['photo']['tmp_name']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $type = mime_content_type($_FILES['photo']['tmp_name']);
        if (strpos($type, 'image/') === 0) {
            $data = file_get_contents($_FILES['photo']['tmp_name']);
            $photoData = 'data:' . $type . ';base64,' . base64_encode($data);
        }
    }

    if (!$date || !$desc) {
        $msg = ['type' => 'error', 'text' => 'Sila isi tarikh dan cerita apa yang anda buat.'];
    } else {
        $stmt = $pdo->prepare('INSERT INTO logbook_entries (user_id, entry_date, photo, description) VALUES (?,?,?,?)');
        $stmt->execute([$u['id'], $date, $photoData, $desc]);
        $msg = ['type' => 'success', 'text' => 'Entri logbook berjaya ditambah.'];
    }
}

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare('DELETE FROM logbook_entries WHERE id = ? AND user_id = ?');
    $stmt->execute([(int)$_GET['delete'], $u['id']]);
    header('Location: /student/entries.php');
    exit;
}

$entries = $pdo->prepare('SELECT * FROM logbook_entries WHERE user_id = ? ORDER BY entry_date DESC, id DESC');
$entries->execute([$u['id']]);
$entries = $entries->fetchAll();

$pageTitle = 'Logbook Entries';
$active = 'entries';
include __DIR__ . '/../../includes/header.php';
?>

<div class="card">
  <h2>➕ Tambah Entri Logbook</h2>
  <?php if ($msg): ?><div class="alert alert-<?= $msg['type'] === 'error' ? 'error' : 'success' ?>"><?= htmlspecialchars($msg['text']) ?></div><?php endif; ?>
  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="add_entry" value="1">
    <label>Tarikh (isi sendiri)</label>
    <input type="date" name="entry_date" required>
    <label>Upload Gambar (dari gallery telefon)</label>
    <input type="file" name="photo" accept="image/*">
    <label>Cerita apa yang anda buat hari ini</label>
    <textarea name="description" required placeholder="Contoh: Hari ini saya membantu proses..."></textarea>
    <button class="btn btn-primary" style="margin-top:14px" type="submit">💾 Simpan Entri</button>
  </form>
</div>

<div class="card">
  <h3>📔 Senarai Entri (<?= count($entries) ?>)</h3>
  <?php if (!$entries): ?>
    <p style="color:var(--muted)">Belum ada entri lagi. Tambah entri pertama anda di atas.</p>
  <?php endif; ?>
  <?php foreach ($entries as $e): ?>
    <div class="entry-item">
      <?php if ($e['photo']): ?><img src="<?= $e['photo'] ?>" alt="foto"><?php endif; ?>
      <div class="entry-meta">
        <div class="entry-date">📅 <?= htmlspecialchars($e['entry_date']) ?></div>
        <p style="margin:6px 0"><?= nl2br(htmlspecialchars($e['description'])) ?></p>
        <a href="?delete=<?= $e['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Padam entri ini?')">🗑️ Padam</a>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
