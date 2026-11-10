<?php
// login.php (Petugas)
session_start();
require_once '../config/koneksi.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = "Username dan password wajib diisi!";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM petugas WHERE username = ?");
            $stmt->execute([$username]);
            $petugas = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($petugas && ($password === $petugas['password'] || password_verify($password, $petugas['password']))) {
                $_SESSION['id_petugas'] = $petugas['id_petugas'];
                $_SESSION['nama_petugas'] = $petugas['nama_petugas'];
                header("Location: dashboard_petugas.php");
                exit();
            } else {
                $error = "Username atau password petugas salah!";
            }
        } catch (PDOException $e) {
            $error = "Terjadi kesalahan sistem: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Petugas / Admin - Klinik Ratu Farma Aceh</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .password-container { position: relative; }
        .toggle-password {
            position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
            background: none; border: none; cursor: pointer; font-size: 1.1rem; color: #6b7280;
        }
    </style>
</head>
<body class="bg-gradient-to-b from-[#e6fcf5] to-[#f0fdf4] text-gray-800 font-sans antialiased min-h-screen flex flex-col justify-between">

    <div class="flex flex-col min-h-screen">
        
        <!-- MEMANGGIL HEADER DARI FOLDER PASIEN -->
        <?php 
            // Karena file ini ada di folder petugas/, kita keluar dulu ke root lalu masuk ke folder pasien
            include 'header_petugas.php'; 
        ?>

        <!-- Main Card Login -->
        <main class="flex-1 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-md w-full bg-white border border-emerald-200 rounded-2xl shadow-xl p-8 space-y-6">
                
                <div class="text-center space-y-2">
                    <div class="mx-auto w-12 h-12 rounded-full bg-emerald-50 flex items-center justify-center text-xl">🔐</div>
                    <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Login Petugas / Admin</h1>
                    <p class="text-xs text-gray-500">Klinik Ratu Farma Aceh • Panel Kontrol Antrean</p>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="bg-red-50 text-red-700 p-3 rounded-lg text-xs text-center font-medium border border-red-200">
                        <?= htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Username Petugas</label>
                        <input type="text" name="username" required placeholder="Masukkan username" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0b4f6c]">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Kata Sandi</label>
                        <div class="password-container">
                            <input type="password" id="password" name="password" required placeholder="Masukkan kata sandi" class="w-full px-3 py-2.5 pr-10 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0b4f6c]">
                            <button type="button" class="toggle-password" onclick="togglePassword()">👁️</button>
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-[#0b4f6c] hover:bg-[#083d53] text-white font-semibold py-2.5 rounded-lg text-sm transition shadow-sm mt-4 cursor-pointer">
                        Masuk ke Dashboard Petugas
                    </button>
                </form>

            </div>
        </main>
    </div>

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

    <footer class="bg-white border-t py-6 text-center text-xs text-gray-500">
        &copy; 2026 Klinik Ratu Farma Aceh. All rights reserved.
    </footer>
</body>
</html>