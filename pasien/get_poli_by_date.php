<?php
// get_poli_by_date.php
require_once __DIR__ . '/../config/jadwal_helper.php';
header('Content-Type: text/html; charset=UTF-8');

$tanggal = $_POST['tanggal'] ?? '';
$nama_hari = namaHariDariTanggal($tanggal);

if ($nama_hari === null) {
    echo '<option value="">Tanggal tidak valid</option>';
    exit;
}

$cfg = require __DIR__ . '/../config/db_config.php';

try {
    $pdo = new PDO("mysql:host={$cfg['host']};dbname={$cfg['name']};charset=utf8mb4", $cfg['user'], $cfg['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Pencocokan hari dilakukan di PHP supaya format "Senin - Sabtu", "Senin & Kamis", dll. ikut terbaca
    $stmt = $pdo->query("
        SELECT p.id_poli, p.nama_poli, j.hari
        FROM poli p
        JOIN dokter d ON p.id_poli = d.id_poli
        JOIN jadwal j ON d.id_dokter = j.id_dokter
        ORDER BY p.nama_poli
    ");
    $polis = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        if (hariCocok($row['hari'], $nama_hari)) {
            $polis[$row['id_poli']] = $row['nama_poli'];
        }
    }

    echo '<option value="">-- Pilih Poli yang Buka --</option>';
    if ($polis) {
        foreach ($polis as $id => $nama) {
            echo '<option value="' . (int)$id . '">' . htmlspecialchars($nama) . '</option>';
        }
    } else {
        echo '<option value="">Tidak ada poli yang praktik pada tanggal ini</option>';
    }
} catch (PDOException $e) {
    echo '<option value="">Gagal memuat data poli</option>';
}
