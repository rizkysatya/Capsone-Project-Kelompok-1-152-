<?php
// pendaftaran.php

// Konfigurasi Database
$cfg = require __DIR__ . '/../config/db_config.php';
$host = $cfg['host'];
$dbname = $cfg['name'];
$username_db = $cfg['user'];
$password_db = $cfg['pass'];

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $nama      = trim($_POST['nama'] ?? '');
    $nik       = trim($_POST['nik'] ?? '');
    $tgl_lahir = trim($_POST['tgl_lahir'] ?? '');
    $no_hp     = trim($_POST['no_hp'] ?? '');
    $alamat    = trim($_POST['alamat'] ?? '');
    $username  = trim($_POST['username'] ?? '');
    $password  = $_POST['password'] ?? '';
    $pass_conf = $_POST['password_confirm'] ?? '';

    // Validasi sederhana
    if (empty($nama) || empty($nik) || empty($tgl_lahir) || empty($no_hp) || empty($username) || empty($password)) {
        $error = "Semua kolom wajib diisi!";
    } elseif ($password !== $pass_conf) {
        $error = "Konfirmasi kata sandi tidak cocok!";
    } elseif (strlen($password) < 6) {
        $error = "Kata sandi minimal harus 6 karakter!";
    } else {
        try {
            // Buat koneksi PDO
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username_db, $password_db);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Cek apakah NIK atau Username sudah terdaftar
            $stmtCek = $pdo->prepare("SELECT id_pasien FROM pasien WHERE nik = ? OR username = ?");
            $stmtCek->execute([$nik, $username]);
            if ($stmtCek->rowCount() > 0) {
                $error = "NIK atau Username sudah terdaftar di sistem!";
            } else {
                // Hash password agar aman
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Query Insert ke tabel pasien sesuai struktur database
                $sql = "INSERT INTO pasien (nama, nik, tgl_lahir, no_hp, alamat, username, password) VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nama, $nik, $tgl_lahir, $no_hp, $alamat, $username, $hashed_password]);

                $message = "Pendaftaran akun berhasil! Silakan login.";
                // Reset variabel setelah sukses
                $nama = $nik = $tgl_lahir = $no_hp = $alamat = $username = '';
            }
        } catch (PDOException $e) {
            $error = "Gagal terhubung ke database: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun Pasien - Klinik Ratu Farma Aceh</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/pendaftaran.css">
    <style>
        /* Styling tambahan untuk tombol intip kata sandi */
        .password-container {
            position: relative;
            display: flex;
            align-items: center;
        }
        .password-container input {
            width: 100%;
            padding-right: 45px;
        }
        .toggle-password {
            position: absolute;
            right: 12px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1.1rem;
            color: #6b7280;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .toggle-password:hover {
            color: #0b4f6c;
        }
    </style>
</head>
<body data-page="signup" data-root="../">

    <!-- MEMANGGIL HEADER PASIEN SECARA DINAMIS -->
    <?php include 'header_pasien.php'; ?>

    <!-- MAIN -->
    <main class="login-wrapper">
        <div class="login-card" style="max-width: 600px;">
            <div class="login-header">
                <div class="login-icon">📝</div>
                <h1>Daftar Akun Pasien</h1>
                <p>Klinik Ratu Farma Aceh • Lengkapi data sesuai identitas Anda</p>
            </div>

            <!-- Pesan Notifikasi Sukses / Error -->
            <?php if (!empty($error)): ?>
                <div style="background-color: #fee2e2; color: #991b1b; padding: 0.75rem; border-radius: 0.375rem; margin-bottom: 1.25rem; font-size: 0.875rem; text-align: center;">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($message)): ?>
                <div style="background-color: #d1fae5; color: #065f46; padding: 0.75rem; border-radius: 0.375rem; margin-bottom: 1.25rem; font-size: 0.875rem; text-align: center;">
                    <?= htmlspecialchars($message) ?> <a href="login.php" style="font-weight: bold; color: #0b4f6c;">Masuk di sini</a>
                </div>
            <?php endif; ?>

            <form action="" method="POST" class="login-form">
                <div class="form-group">
                    <label for="nama">Nama Lengkap Pasien</label>
                    <input type="text" id="nama" name="nama" maxlength="100" placeholder="Masukkan nama lengkap sesuai KTP" value="<?= htmlspecialchars($nama ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label for="nik">NIK (Nomor Induk Kependudukan)</label>
                    <input type="text" id="nik" name="nik" maxlength="20" inputmode="numeric" placeholder="16 digit NIK" value="<?= htmlspecialchars($nik ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label for="tgl_lahir">Tanggal Lahir</label>
                    <input type="date" id="tgl_lahir" name="tgl_lahir" value="<?= htmlspecialchars($tgl_lahir ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label for="no_hp">Nomor HP / WhatsApp</label>
                    <input type="tel" id="no_hp" name="no_hp" maxlength="15" placeholder="Contoh: 081234567890" value="<?= htmlspecialchars($no_hp ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label for="alamat">Alamat Lengkap</label>
                    <textarea id="alamat" name="alamat" rows="3" placeholder="Masukkan alamat domisili Anda" style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem; font-size: 1rem; outline: none;" required><?= htmlspecialchars($alamat ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label for="username">Username (untuk Login)</label>
                    <input type="text" id="username" name="username" maxlength="50" placeholder="Buat username unik" value="<?= htmlspecialchars($username ?? '') ?>" required>
                </div>

                <!-- Input Kata Sandi dengan Tombol Intip -->
                <div class="form-group">
                    <label for="password">Kata Sandi (Minimal 6 Karakter)</label>
                    <div class="password-container">
                        <input type="password" id="password" name="password" minlength="6" placeholder="Buat kata sandi akun" required>
                        <button type="button" class="toggle-password" onclick="togglePassword('password', this)">👁️</button>
                    </div>
                </div>

                <!-- Input Konfirmasi Kata Sandi dengan Tombol Intip -->
                <div class="form-group">
                    <label for="password_confirm">Konfirmasi Kata Sandi</label>
                    <div class="password-container">
                        <input type="password" id="password_confirm" name="password_confirm" minlength="6" placeholder="Ulangi kata sandi" required>
                        <button type="button" class="toggle-password" onclick="togglePassword('password_confirm', this)">👁️</button>
                    </div>
                </div>

                <button type="submit" class="login-button">
                    Daftar &amp; Simpan ke Database
                </button>
            </form>

            <div class="login-footer">
                <p>Sudah memiliki akun pasien?</p>
                <a href="login.php">Masuk ke Akun Pasien</a>
            </div>
        </div>
    </main>
    
    <!-- Script untuk Toggle Intip Password -->
    <script>
        function togglePassword(fieldId, btn) {
            const passwordField = document.getElementById(fieldId);
            if (passwordField.type === "password") {
                passwordField.type = "text";
                btn.textContent = "👁️‍🗨️";
            } else {
                passwordField.type = "password";
                btn.textContent = "👁️";
            }
        }
    </script>

    <script src="../assets/js/local.js"></script>

    <footer class="footer">
        <p>Sistem Antrian Online Klinik Ratu Farma Aceh</p>
        <span>Melayani dengan Ramah dan Profesional</span>
    </footer>

</body>
</html>