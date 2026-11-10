<?php
// config/koneksi.php
$cfg = require __DIR__ . '/db_config.php';
$host = $cfg['host'];
$dbname = $cfg['name'];
$username = $cfg['user'];
$password = $cfg['pass'];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    require_once __DIR__ . '/migrasi.php';
    pastikanKolomBaru($pdo);
} catch (PDOException $e) {
    die("Koneksi Database Gagal: " . $e->getMessage());
}
?>