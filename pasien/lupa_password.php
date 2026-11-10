<?php
// lupa_password.php
session_start();
$error = $_SESSION['error'] ?? '';
$success = $_SESSION['success'] ?? '';
unset($_SESSION['error'], $_SESSION['success']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Kata Sandi - Klinik Ratu Farma Aceh</title>
    <link rel="stylesheet" href="../assets/css/loginpasien.css">
</head>
<body data-page="forgot-password" data-root="../">

    <!-- NAVBAR -->
    <header class="navbar">
        <div class="navbar-container">
            <a href="../index.html" class="brand-logo">
                <img src="../assets/img/logo-ratu-farma.jpeg" alt="Logo Ratu Farma Aceh" style="height: 45px; width: auto; display: block;" onerror="this.style.display='none'; document.getElementById('fallback-brand').style.display='block';">
                <span id="fallback-brand" style="display:none; font-weight:bold; color:#0b4f6c;">Ratu Farma Aceh</span>
            </a>
            <div class="navbar-menu">
                <a href="../index.html" class="nav-link">Beranda / Poli</a>
                <a href="login.php" class="nav-link">Login Pasien</a>
                <a href="pendaftaran.php" class="btn-system">Daftar Akun</a>
            </div>
        </div>
    </header>

    <!-- MAIN -->
    <main class="login-wrapper">
        <div class="login-card">
            <div class="login-header">
                <div class="login-icon">🔐</div>
                <h1>Reset Kata Sandi</h1>
                <p>Masukkan NIK dan No HP terdaftar untuk membuat kata sandi baru</p>
            </div>

            <!-- Pesan Notifikasi Error / Sukses -->
            <?php if (!empty($error)): ?>
                <div style="background-color: #fee2e2; color: #991b1b; padding: 0.75rem; border-radius: 0.375rem; margin-bottom: 1.25rem; font-size: 0.875rem; text-align: center;">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div style="background-color: #d1fae5; color: #065f46; padding: 0.75rem; border-radius: 0.375rem; margin-bottom: 1.25rem; font-size: 0.875rem; text-align: center;">
                    <?= htmlspecialchars($success) ?> <a href="login.php" style="font-weight: bold; color: #0b4f6c;">Masuk sekarang</a>
                </div>
            <?php endif; ?>

            <form action="proses_lupa_password.php" method="POST" class="login-form">
                <div class="form-group">
                    <label for="nik">NIK (Nomor Induk Kependudukan)</label>
                    <input type="text" id="nik" name="nik" maxlength="20" placeholder="Masukkan 16 digit NIK Anda" required>
                </div>

                <div class="form-group">
                    <label for="no_hp">Nomor HP / WhatsApp Terdaftar</label>
                    <input type="tel" id="no_hp" name="no_hp" maxlength="15" placeholder="Contoh: 081234567890" required>
                </div>

                <div class="form-group">
                    <label for="password">Kata Sandi Baru (Min. 6 Karakter)</label>
                    <div class="password-container">
                        <input type="password" id="password" name="password" minlength="6" placeholder="Buat kata sandi baru" required>
                        <button type="button" class="toggle-password" onclick="togglePassword('password', this)">👁️</button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password_confirm">Konfirmasi Kata Sandi Baru</label>
                    <div class="password-container">
                        <input type="password" id="password_confirm" name="password_confirm" minlength="6" placeholder="Ulangi kata sandi baru" required>
                        <button type="button" class="toggle-password" onclick="togglePassword('password_confirm', this)">👁️</button>
                    </div>
                </div>

                <button type="submit" class="login-button">
                    Simpan Kata Sandi Baru
                </button>
            </form>

            <div class="login-footer">
                <p>Ingat kata sandi Anda?</p>
                <a href="login.php">Kembali ke Halaman Login</a>
            </div>
        </div>
    </main>
    
    <!-- Script Toggle Intip Password -->
    <script>
        function togglePassword(fieldId, btn) {
            const pwdInput = document.getElementById(fieldId);
            if (pwdInput.type === 'password') {
                pwdInput.type = 'text';
                btn.textContent = "👁️‍🗨️";
            } else {
                pwdInput.type = 'password';
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