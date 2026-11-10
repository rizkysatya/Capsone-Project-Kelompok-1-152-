<?php
// login.php
session_start();
$error = $_SESSION['error'] ?? '';
unset($_SESSION['error']); // Hapus pesan error setelah ditampilkan
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Pasien - Klinik Ratu Farma Aceh</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Memanggil CSS Terpisah -->
    <link rel="stylesheet" href="../assets/css/loginpasien.css">
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
            color: #0d5975;
        }
    </style>
</head>
<body data-page="login" data-root="../" class="bg-gray-50 text-gray-800 font-sans antialiased min-h-screen flex flex-col justify-between">

    <div class="flex flex-col min-h-screen">
        <!-- MEMANGGIL HEADER PASIEN SECARA DINAMIS -->
        <?php include 'header_pasien.php'; ?>
        
        <!-- Login Wrapper / Konten Utama -->
        <main class="login-wrapper flex-1 flex items-center justify-center py-10 px-4">
            <div class="login-card w-full" style="max-width: 440px;">
                
                <!-- Login Header -->
                <div class="login-header text-center mb-6">
                    <div class="login-icon text-4xl mb-2">
                        🏥
                    </div>
                    <h1 class="text-2xl font-bold text-gray-900">Login Pasien</h1>
                    <p class="text-xs text-gray-500 mt-1">
                        Klinik Ratu Farma Aceh &bull; Masuk untuk mengambil nomor antrean online.
                    </p>
                </div>

                <!-- Notifikasi Error dari Session -->
                <?php if (!empty($error)): ?>
                    <div style="background-color: #fee2e2; color: #991b1b; padding: 0.75rem; border-radius: 0.375rem; margin-bottom: 1.25rem; font-size: 0.875rem; text-align: center;">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <!-- Form Login mengarah ke proses_login.php -->
                <form action="proses_login.php" method="POST" class="login-form space-y-4">
                    <div class="form-group">
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Username, NIK, atau No HP</label>
                        <input type="text" name="identity" required placeholder="Masukkan username, NIK, atau No HP" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0d5975]">
                    </div>

                    <div class="form-group" style="margin-bottom: 0.5rem;">
                        <label for="password" class="block text-xs font-semibold text-gray-700 mb-1">Kata Sandi</label>
                        <div class="password-container">
                            <input type="password" id="password" name="password" placeholder="Masukkan kata sandi Anda" required class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0d5975]">
                            <button type="button" class="toggle-password" onclick="togglePassword()">👁️</button>
                        </div>
                    </div>

                    <!-- Link Lupa Kata Sandi -->
                    <div style="text-align: right; margin-bottom: 1.25rem;">
                        <a href="lupa_password.php" style="font-size: 0.8rem; color: #0d5975; text-decoration: none; font-weight: 600;">Lupa kata sandi?</a>
                    </div>

                    <button type="submit" class="login-button w-full bg-[#0d5975] hover:bg-[#094357] text-white font-semibold py-2.5 rounded-lg text-sm transition shadow-sm cursor-pointer">
                        Masuk &amp; Ambil Antrean
                    </button>
                </form>

                <!-- Footer Card -->
                <div class="login-footer text-center mt-6 text-xs text-gray-600">
                    <p>Belum memiliki akun pasien?</p> 
                    <a href="pendaftaran.php" class="text-[#0d5975] font-semibold hover:underline">Daftar Pasien Baru</a>
                </div>

            </div>
        </main>
    </div>

    <!-- Script Toggle Intip Password -->
    <script>
        function togglePassword() {
            const pwdInput = document.getElementById('password');
            const btn = document.querySelector('.toggle-password');
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

    <!-- Footer Bawah -->
    <footer class="footer bg-white border-t py-6 text-center text-xs text-gray-500 mt-10">
        <p>Sistem Antrian Online Klinik Ratu Farma Aceh</p>
        <span>Melayani dengan Ramah dan Profesional</span>
    </footer>

</body>
</html>