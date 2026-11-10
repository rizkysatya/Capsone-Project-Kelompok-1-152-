<?php
session_start();
require_once '../config/koneksi.php';

// Pastikan hanya petugas yang dapat mengakses endpoint data ini
if (!isset($_SESSION['id_petugas'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Akses ditolak. Silakan login terlebih dahulu.']);
    exit();
}

header('Content-Type: application/json');

try {
    // Mengambil daftar antrean aktif hari ini untuk ditampilkan secara dinamis / AJAX
    $stmt = $pdo->prepare("
        SELECT a.id_antrean, a.nomor_antrean, a.status_antrean, p.nama, pd.tgl_kunjungan 
        FROM antrean a
        JOIN pendaftaran pd ON a.id_pendaftaran = pd.id_pendaftaran
        JOIN pasien p ON pd.id_pasien = p.id_pasien
        WHERE pd.tgl_kunjungan = CURDATE()
        ORDER BY a.id_antrean ASC
    ");
    $stmt->execute();
    $data_antrean = $stmt->fetchAll();

    echo json_encode([
        'status' => 'success',
        'data' => $data_antrean
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>