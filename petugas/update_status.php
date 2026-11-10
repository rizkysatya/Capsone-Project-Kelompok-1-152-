<?php
// update_status.php
session_start();
require_once '../config/koneksi.php';

header('Content-Type: application/json; charset=utf-8');

function respond(bool $success, string $message = '', int $code = 200): void
{
    http_response_code($code);
    echo json_encode(['success' => $success, 'message' => $message]);
    exit();
}

// Cek login
if (!isset($_SESSION['id_petugas'])) {
    respond(false, 'Unauthorized', 401);
}

// Hanya terima POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Invalid request method.', 405);
}

$id_antrean = filter_input(INPUT_POST, 'id_antrean', FILTER_VALIDATE_INT);
$status_baru = trim($_POST['status'] ?? '');

// Daftar status yang diizinkan (sesuaikan dengan data di database Anda)
$status_valid = ['Menunggu', 'Check-in', 'Batal'];

if (!$id_antrean || !in_array($status_baru, $status_valid, true)) {
    respond(false, 'Data tidak valid.', 400);
}

try {
    // Petugas tidak boleh mengubah antrean yang sudah dipanggil, selesai, atau batal
    $cek = $pdo->prepare("SELECT status_antrean FROM antrean WHERE id_antrean = :id");
    $cek->execute([':id' => $id_antrean]);
    $status_lama = $cek->fetchColumn();
    if ($status_lama === false) {
        respond(false, 'Antrean tidak ditemukan.', 404);
    }
    if (in_array($status_lama, ['Dipanggil', 'Selesai', 'Batal'], true)) {
        respond(false, 'Antrean sudah berstatus ' . $status_lama . ' dan tidak bisa diubah.', 409);
    }

    $stmt = $pdo->prepare(
        "UPDATE antrean SET status_antrean = :status WHERE id_antrean = :id"
    );
    $stmt->execute([
        ':status' => $status_baru,
        ':id'     => $id_antrean,
    ]);

    respond(true, 'Status berhasil diperbarui.');
} catch (PDOException $e) {
    error_log($e->getMessage()); // jangan tampilkan error DB ke pengguna
    respond(false, 'Terjadi kesalahan pada server.', 500);
}