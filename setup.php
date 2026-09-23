<?php
// Setup sekali sahaja: buka fail ni dalam browser untuk create semua table.
// Lepas berjaya, PADAM fail ni (atau rename) supaya orang lain tak boleh run semula.

require_once __DIR__ . '/../includes/db.php';

header('Content-Type: text/plain; charset=utf-8');

$sqlFile = __DIR__ . '/../database/schema.sql';
if (!file_exists($sqlFile)) {
    die("Fail database/schema.sql tak jumpa. Pastikan folder 'database' ada dalam repo.\n");
}

$sql = file_get_contents($sqlFile);
// buang comment line (-- ...) sebelum split ikut ';'
$sql = preg_replace('/^--.*$/m', '', $sql);
$statements = array_filter(array_map('trim', explode(';', $sql)));

$ok = 0; $fail = 0;
foreach ($statements as $stmt) {
    if (!$stmt) continue;
    try {
        $pdo->exec($stmt);
        $ok++;
        echo "OK  : " . substr($stmt, 0, 60) . "...\n";
    } catch (PDOException $e) {
        $fail++;
        echo "SKIP: " . substr($stmt, 0, 60) . "...  (" . $e->getMessage() . ")\n";
    }
}

echo "\n=====================================\n";
echo "Selesai. $ok statement berjaya, $fail di-skip (contoh: table dah wujud - OK je tu).\n";
echo "Table sekarang dalam database:\n";
foreach ($pdo->query('SHOW TABLES') as $row) {
    echo " - " . reset($row) . "\n";
}
echo "\nPADAM fail public/setup.php ni sekarang untuk keselamatan, pastu try /register.php semula.\n";
