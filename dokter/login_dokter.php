<?php
// dokter/login_dokter.php
session_start();
require_once '../config/koneksi.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($username) && !empty($password)) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM dokter WHERE username = ?");
            $stmt->execute([$username]);
            $dokter = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($dokter && (password_verify($password, $dokter['password']) || $password === $dokter['password'])) {
                $_SESSION['id_dokter']   = $dokter['id_dokter'];
                $_SESSION['nama_dokter'] = $dokter['nama_dokter'];
                
                if (!empty($dokter['id_poli'])) {
                    $stmtPoli = $pdo->prepare("SELECT nama_poli FROM poli WHERE id_poli = ?");
                    $stmtPoli->execute([$dokter['id_poli']]);
                    $poli = $stmtPoli->fetch(PDO::FETCH_ASSOC);
                    $_SESSION['nama_poli'] = $poli['nama_poli'] ?? 'Dokter Spesialis';
                } else {
                    $_SESSION['nama_poli'] = 'Klinik Ratu Farma';
                }

                header("Location: dashboard_dokter.php");
                exit();
            } else {
                $error = "Username atau kata sandi salah!";
            }
        } catch (PDOException $e) {
            $error = "Terjadi kesalahan sistem: " . $e->getMessage();
        }
    } else {
        $error = "Harap isi username dan kata sandi terlebih dahulu.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Dokter - Klinik Ratu Farma</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/dashboard-petugas.css">
</head>
<body class="bg-gradient-to-b from-[#e6fcf5] to-[#f0fdf4] text-gray-800 font-sans antialiased min-h-screen flex flex-col justify-between">

    <div class="flex flex-col min-h-screen">
        
        <!-- MEMANGGIL HEADER DARI FOLDER PASIEN -->
        <?php 
            // Karena file ini berada di folder dokter/, arahkan keluar ke root lalu ke pasien/header_pasien.php
            include 'header_dokter.php'; 
        ?>

        <!-- Main Card Login -->
        <main class="flex-1 flex items-center justify-center p-4">
            <div class="bg-white w-full max-w-md p-8 sm:p-10 rounded-3xl border border-emerald-200 shadow-xl space-y-6">
                
                <!-- Ikon Gembok Atas -->
                <div class="flex justify-center">
                    <div class="w-14 h-14 bg-emerald-50 rounded-full flex items-center justify-center border border-emerald-100 shadow-2xs">
                        <span class="text-2xl">🔐</span>
                    </div>
                </div>

                <!-- Heading -->
                <div class="text-center space-y-1">
                    <h1 class="text-2xl font-black text-slate-800 tracking-tight">Login Dokter</h1>
                    <p class="text-xs text-slate-400 font-medium">Klinik Ratu Farma Aceh • Panel Kontrol Dokter</p>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="bg-red-50 text-red-700 p-3.5 rounded-2xl text-xs font-semibold border border-red-200">
                        <?= htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <!-- Form Login -->
                <form action="login_dokter.php" method="POST" class="space-y-4">
                    <div class="space-y-1.5">
                        <label for="username" class="block text-xs font-bold text-slate-700">Username Dokter</label>
                        <input type="text" id="username" name="username" placeholder="Masukkan username" required
                            class="w-full px-4 py-3 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#0d5975] focus:bg-white transition">
                    </div>

                    <div class="space-y-1.5">
                        <label for="password" class="block text-xs font-bold text-slate-700">Kata Sandi</label>
                        <div class="relative flex items-center">
                            <input type="password" id="password" name="password" placeholder="Masukkan kata sandi" required
                                class="w-full px-4 py-3 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#0d5975] focus:bg-white transition pr-12">
                            <button type="button" id="toggle-password" class="absolute right-3.5 text-slate-400 hover:text-slate-600 text-sm focus:outline-none cursor-pointer">
                                👁️
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="w-full mt-3 bg-[#0d5975] hover:bg-[#094357] text-white py-3.5 rounded-xl text-xs font-bold transition shadow-sm cursor-pointer">
                        Masuk ke Dashboard Dokter
                    </button>
                </form>

            </div>
        </main>
    </div>

    <!-- Script Toggle Lihat/Sembunyikan Sandi -->
    <script>
        const toggleBtn = document.getElementById('toggle-password');
        const passwordInput = document.getElementById('password');

        if(toggleBtn && passwordInput) {
            toggleBtn.addEventListener('click', function() {
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    toggleBtn.textContent = '🙈';
                } else {
                    passwordInput.type = 'password';
                    toggleBtn.textContent = '👁️';
                }
            });
        }
    </script>

    <footer class="bg-white border-t py-6 text-center text-xs text-gray-500">
        &copy; 2026 Klinik Ratu Farma Aceh. All rights reserved.
    </footer>
</body>
</html>