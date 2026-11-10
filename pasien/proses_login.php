<?php
// proses_login.php
session_start();

$cfg = require __DIR__ . '/../config/db_config.php';
$host = $cfg['host'];
$dbname = $cfg['name'];
$username_db = $cfg['user'];
$password_db = $cfg['pass'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identity = trim($_POST['identity'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($identity) || empty($password)) {
        $_SESSION['error'] = "Username/NIK/No HP dan Kata Sandi wajib diisi!";
        header("Location: login.php");
        exit;
    }

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username_db, $password_db);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Cari pasien berdasarkan username, nik, atau no_hp
        $stmt = $pdo->prepare("SELECT * FROM pasien WHERE username = ? OR nik = ? OR no_hp = ?");
        $stmt->execute([$identity, $identity, $identity]);
        $pasien = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($pasien && password_verify($password, $pasien['password'])) {
            // Login Berhasil -> Simpan session pasien
            $_SESSION['id_pasien'] = $pasien['id_pasien'];
            $_SESSION['nama_pasien'] = $pasien['nama'];

            // Arahkan ke dashboard.php
            header("Location: dashboard_pasien.php");
            exit;
        } else {
            $_SESSION['error'] = "Identitas atau kata sandi salah! Silakan periksa kembali.";
            header("Location: login.php");
            exit;
        }

    } catch (PDOException $e) {
        $_SESSION['error'] = "Terjadi kesalahan sistem: " . $e->getMessage();
        header("Location: login.php");
        exit;
    }
} else {
    header("Location: login.php");
    exit;
}