<?php
// Mengambil dokter yang memiliki jadwal pada tanggal yang dipilih untuk form pasien offline.
require_once '../config/koneksi.php';
header('Content-Type: text/html; charset=UTF-8');

$id_poli = filter_input(INPUT_POST, 'id_poli', FILTER_VALIDATE_INT);
$tanggal = $_POST['tanggal'] ?? date('Y-m-d');

$hariValid = DateTime::createFromFormat('!Y-m-d', $tanggal);
if (!$id_poli || !$hariValid || $hariValid->format('Y-m-d') !== $tanggal) {
    echo '<option value="">Pilih poli dan tanggal yang valid</option>';
    exit;
}

require_once __DIR__ . '/../config/jadwal_helper.php';
$namaHari = namaHariDariTanggal($tanggal);

try {
    $stmt = $pdo->prepare("SELECT d.id_dokter, d.nama_dokter, d.spesialisasi, j.hari,
                                  j.jam_mulai, j.jam_selesai
                           FROM dokter d
                           INNER JOIN jadwal j ON j.id_dokter = d.id_dokter
                           WHERE d.id_poli = ?
                           ORDER BY j.jam_mulai, d.nama_dokter");
    $stmt->execute([$id_poli]);
    $dokter = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        if (hariCocok($row['hari'], $namaHari) && !isset($dokter[$row['id_dokter']])) {
            $dokter[$row['id_dokter']] = $row;
        }
    }

    echo '<option value="">-- Pilih Dokter & Jadwal --</option>';
    if (!$dokter) {
        echo '<option value="">Tidak ada dokter praktik di poli ini pada hari ' . htmlspecialchars($namaHari, ENT_QUOTES, 'UTF-8') . '</option>';
    } else {
        foreach ($dokter as $d) {
            $label = $d['nama_dokter'] . ' (' . $d['jam_mulai'] . ' - ' . $d['jam_selesai'] . ')';
            echo '<option value="' . (int)$d['id_dokter'] . '">' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</option>';
        }
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo '<option value="">Data dokter gagal dimuat. Coba muat ulang.</option>';
}
