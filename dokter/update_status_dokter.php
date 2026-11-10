<?php
// dokter/update_status_dokter.php
session_start();
require_once '../config/koneksi.php';

header('Content-Type: application/json');

if (!isset($_SESSION['id_dokter'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_antrean = $_POST['id_antrean'] ?? '';
    $status     = $_POST['status'] ?? ''; // Menangkap status langsung dari parameter AJAX (Dipanggil, Lewati, Batal)

    if (empty($id_antrean) || empty($status)) {
        echo json_encode(['success' => false, 'message' => 'Data tidak lengkap.']);
        exit();
    }

    try {
        $stmt = $pdo->prepare("UPDATE antrean SET status_antrean = ? WHERE id_antrean = ?");
        $stmt->execute([$status, $id_antrean]);

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}