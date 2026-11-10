<?php
// proses_lupa_password.php
session_start();

// Konfigurasi Database
$cfg = require __DIR__ . '/../config/db_config.php';
$host = $cfg['host'];
$dbname = $cfg['name'];
$username_db = $cfg['user'];
$password_db = $cfg['pass'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nik       = trim($_POST['nik'] ?? '');
    $no_hp     = trim($_POST['no_hp'] ?? '');
    $password  = $_POST['password'] ?? '';
    $pass_conf = $_POST['password_confirm'] ?? '';

    if (empty($nik) || empty($no_hp) || empty($password) || empty($pass_conf)) {
        $_SESSION['error'] = "Semua kolom wajib diisi!";
        header("Location: lupa_password.php");
        exit;
    }

    if ($password !== $pass_conf) {
        $_SESSION['error'] = "Konfirmasi kata sandi baru tidak cocok!";
        header("Location: lupa_password.php");
        exit;
    }

    if (strlen($password) < 6) {
        $_SESSION['error'] = "Kata sandi minimal harus 6 karakter!";
        header("Location: lupa_password.php");
        exit;
    }

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username_db, $password_db);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Cek apakah NIK dan No HP cocok dengan data di database pasien
        $stmt = $pdo->prepare("SELECT id_pasien FROM pasien WHERE nik = ? AND no_hp = ?");
        $stmt->execute([$nik, $no_hp]);
        $pasien = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($pasien) {
            // Jika cocok, update password dengan yang baru (di-hash)
            $new_hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $updateStmt = $pdo->prepare("UPDATE pasien SET password = ? WHERE id_pasien = ?");
            $updateStmt->execute([$new_hashed_password, $pasien['id_pasien']]);

            $_SESSION['success'] = "Kata sandi berhasil direset! Silakan login dengan kata sandi baru.";
            header("Location: lupa_password.php");
            exit;
        } else {
            $_SESSION['error'] = "Data NIK dan Nomor HP tidak ditemukan di sistem!";
            header("Location: lupa_password.php");
            exit;
        }

    } catch (PDOException $e) {
        $_SESSION['error'] = "Gagal terhubung ke database: " . $e->getMessage();
        header("Location: lupa_password.php");
        exit;
    }
} else {
    header("Location: lupa_password.php");
    exit;
}