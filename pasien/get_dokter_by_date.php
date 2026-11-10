<?php
// get_dokter_by_date.php
require_once __DIR__ . '/../config/jadwal_helper.php';
header('Content-Type: text/html; charset=UTF-8');

$id_poli = $_POST['id_poli'] ?? '';
$tanggal = $_POST['tanggal'] ?? '';
$nama_hari = namaHariDariTanggal($tanggal);

if ($id_poli === '' || $nama_hari === null) {
    echo '<option value="">Pilih poli dan tanggal yang valid</option>';
    exit;
}

$cfg = require __DIR__ . '/../config/db_config.php';

try {
    $pdo = new PDO("mysql:host={$cfg['host']};dbname={$cfg['name']};charset=utf8mb4", $cfg['user'], $cfg['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("
        SELECT d.id_dokter, d.nama_dokter, d.spesialisasi, j.id_jadwal, j.hari, j.jam_mulai, j.jam_selesai, j.kuota, j.kuota_default
        FROM dokter d
        JOIN jadwal j ON d.id_dokter = j.id_dokter
        WHERE d.id_poli = ?
        ORDER BY j.jam_mulai, d.nama_dokter
    ");
    $stmt->execute([$id_poli]);

    // Satu dokter hanya tampil sekali, memakai jadwal pertama yang cocok (sama dengan yang dipakai saat submit)
    $dokters = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        if (hariCocok($row['hari'], $nama_hari) && !isset($dokters[$row['id_dokter']])) {
            $dokters[$row['id_dokter']] = $row;
        }
    }

    echo '<option value="">-- Pilih Dokter & Jadwal --</option>';
    if ($dokters) {
        foreach ($dokters as $d) {
            $kuota   = kuotaEfektif($d);
            $terisi  = jumlahTerdaftarAktif($pdo, $d['id_jadwal'], $tanggal);
            $sisa    = max(0, $kuota - $terisi);
            $penuh   = $sisa <= 0;
            $label   = $d['nama_dokter'] . ' (' . $d['spesialisasi'] . ' - ' . $d['jam_mulai'] . ' s.d ' . $d['jam_selesai'] . ')'
                     . ($penuh ? ' - PENUH' : ' - sisa ' . $sisa . ' dari ' . $kuota);
            echo '<option value="' . (int)$d['id_dokter'] . '"' . ($penuh ? ' disabled' : '') . '>' . htmlspecialchars($label) . '</option>';
        }
    } else {
        echo '<option value="">Tidak ada dokter praktik di poli ini pada tanggal tersebut</option>';
    }
} catch (PDOException $e) {
    echo '<option value="">Gagal memuat data dokter</option>';
}
