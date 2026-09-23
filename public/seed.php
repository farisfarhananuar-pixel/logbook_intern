<?php
// Buka fail ni SEKALI SAHAJA dalam browser untuk:
// 1) Create semua table (kalau belum ada)
// 2) Terus daftarkan akaun: farhan/1234 (student) dan miss/1234 (supervisor)
// Lepas berjaya, PADAM fail ni dari repo untuk keselamatan.

require_once __DIR__ . '/../includes/db.php';
header('Content-Type: text/plain; charset=utf-8');

// ---- 1) Create tables ----
$sqlFile = __DIR__ . '/../database/schema.sql';
if (!file_exists($sqlFile)) {
    die("Fail database/schema.sql tak jumpa dalam repo.\n");
}
$sql = file_get_contents($sqlFile);
$sql = preg_replace('/^--.*$/m', '', $sql);
$statements = array_filter(array_map('trim', explode(';', $sql)));

echo "== Setup table ==\n";
foreach ($statements as $stmt) {
    if (!$stmt) continue;
    try {
        $pdo->exec($stmt);
        echo "OK  : " . substr($stmt, 0, 55) . "...\n";
    } catch (PDOException $e) {
        echo "SKIP: " . substr($stmt, 0, 55) . "...  (" . $e->getMessage() . ")\n";
    }
}

// ---- 2) Seed akaun ----
function seedUser($pdo, $role, $name, $username, $password) {
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$username]);
    $existing = $stmt->fetch();
    if ($existing) {
        echo "AKAUN '$username' dah wujud (id={$existing['id']}), skip create.\n";
        return $existing['id'];
    }
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('INSERT INTO users (role, name, email, password) VALUES (?,?,?,?)');
    $stmt->execute([$role, $name, $username, $hash]);
    $id = $pdo->lastInsertId();
    echo "AKAUN '$username' ($role) berjaya dicipta, id=$id.\n";
    return $id;
}

echo "\n== Seed akaun ==\n";
$studentId = seedUser($pdo, 'student', 'Farhan', 'farhan', '1234');
$chk = $pdo->prepare('SELECT id FROM student_profile WHERE user_id = ?');
$chk->execute([$studentId]);
if (!$chk->fetch()) {
    $pdo->prepare('INSERT INTO student_profile (user_id) VALUES (?)')->execute([$studentId]);
    echo "student_profile untuk Farhan dicipta.\n";
}

seedUser($pdo, 'supervisor', 'Miss', 'miss', '1234');

echo "\n=====================================\n";
echo "SIAP! Login guna:\n";
echo "  Student    -> username: farhan | password: 1234\n";
echo "  Supervisor -> username: miss   | password: 1234\n";
echo "\nPADAM fail public/seed.php ni sekarang dari GitHub (penting untuk keselamatan),\n";
echo "pastu pergi /login.php dan cuba log masuk.\n";
