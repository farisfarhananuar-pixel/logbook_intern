<?php
// Sambungan database guna Railway MySQL environment variables
// (Sama macam yang awak nampak dalam tab Variables MySQL Railway)

$host = getenv('MYSQLHOST') ?: getenv('MYSQL_HOST') ?: null;
$port = getenv('MYSQLPORT') ?: getenv('MYSQL_PORT') ?: '3306';
$db   = getenv('MYSQLDATABASE') ?: getenv('MYSQL_DATABASE') ?: null;
$user = getenv('MYSQLUSER') ?: getenv('MYSQL_USER') ?: 'root';
$pass = getenv('MYSQLPASSWORD') ?: getenv('MYSQL_ROOT_PASSWORD') ?: '';

if (!$host || !$db) {
    die(
        'Database connection gagal: environment variable MySQL belum di-set pada service ini ' .
        '(MYSQLHOST / MYSQLDATABASE tak jumpa). Pergi Railway → service PHP awak → tab Variables → ' .
        '"Add Variable Reference" → sambungkan MYSQLHOST, MYSQLPORT, MYSQLDATABASE, MYSQLUSER, MYSQLPASSWORD ' .
        'dari service MySQL awak, then redeploy.'
    );
}

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT => 5,
        ]
    );
} catch (PDOException $e) {
    die(
        'Database connection gagal: ' . htmlspecialchars($e->getMessage()) .
        ' (host: ' . htmlspecialchars($host) . ', db: ' . htmlspecialchars($db) . '). ' .
        'Kalau baru je link variable, cuba redeploy dulu — dan pastikan database/schema.sql dah di-import.'
    );
}
