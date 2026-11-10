<?php
// tambah_dokter.php
session_start();
require_once '../config/koneksi.php';

if (!isset($_SESSION['id_petugas'])) {
    header("Location: login.php");
    exit();
}

$error = '';
$success = '';

try {
    // Ambil daftar poli untuk pilihan form
    $stmtPoli = $pdo->query("SELECT * FROM poli ORDER BY nama_poli ASC");
    $daftar_poli = $stmtPoli->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Gagal memuat data poli: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_dokter  = trim($_POST['nama_dokter'] ?? '');
    $id_poli      = trim($_POST['id_poli'] ?? '');
    $spesialisasi = trim($_POST['spesialisasi'] ?? '');
    $no_telepon   = trim($_POST['no_telepon'] ?? '');
    $username     = trim($_POST['username'] ?? '');
    $password     = trim($_POST['password'] ?? '');

    if (empty($nama_dokter) || empty($id_poli) || empty($spesialisasi) || empty($no_telepon) || empty($username) || empty($password)) {
        $error = "Semua kolom wajib diisi!";
    } else {
        try {
            // Cek apakah username sudah digunakan
            $stmtCek = $pdo->prepare("SELECT id_dokter FROM dokter WHERE username = ?");
            $stmtCek->execute([$username]);
            if ($stmtCek->rowCount() > 0) {
                $error = "Username sudah digunakan oleh dokter lain. Silakan gunakan username lain.";
            } else {
                // Enskripsi password agar aman
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Insert data ke tabel dokter sesuai kolom database
                $stmt = $pdo->prepare("INSERT INTO dokter (nama_dokter, id_poli, spesialisasi, no_telepon, username, password) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$nama_dokter, $id_poli, $spesialisasi, $no_telepon, $username, $hashed_password]);
                
                $success = "Data dokter baru berhasil ditambahkan ke sistem!";
            }
        } catch (PDOException $e) {
            $error = "Gagal menyimpan data dokter: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Dokter - Klinik Ratu Farma</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="../assets/css/dashboard-petugas.css">
</head>
<body class="min-h-screen flex">

    <!-- SIDEBAR KIRI -->
    <aside class="w-64 bg-white border-r border-slate-200 flex flex-col justify-between hidden md:flex sticky top-0 h-screen shadow-xs">
        <div>
            <div class="p-6 border-b border-slate-100 flex items-center space-x-3">
                <img src="../assets/img/logo-ratu-farma.jpeg" alt="Logo Ratu Farma" class="h-10 w-auto object-contain" onerror="this.style.display='none'; document.getElementById('fallback-logo').style.display='flex';">
                <div id="fallback-logo" style="display:none;" class="bg-gradient-to-tr from-cyan-600 to-blue-700 text-white p-2 rounded-xl font-bold text-sm">RF</div>
                <div>
                    <h1 class="font-black text-slate-800 text-sm leading-tight">Ratu Farma</h1>
                    <span class="text-[11px] font-semibold text-cyan-600">Portal Petugas</span>
                </div>
            </div>

            <div class="p-4 space-y-1.5">
                <span class="px-3 text-[10px] font-bold tracking-wider text-slate-400 uppercase">Menu Petugas</span>
                
                <a href="dashboard_petugas.php" class="flex items-center space-x-3 px-3.5 py-3 rounded-2xl text-slate-600 hover:bg-slate-50 font-medium text-sm transition">
                    <span>📊</span>
                    <span>Dashboard</span>
                </a>

                <a href="pasien_offline.php" class="flex items-center space-x-3 px-3.5 py-3 rounded-2xl text-slate-600 hover:bg-slate-50 font-medium text-sm transition">
                    <span>🏥</span>
                    <span>Pasien Offline</span>
                </a>

                <!-- Kelola Dokter (Aktif) -->
                <div class="space-y-1">
                    <button onclick="toggleDropdown('dropdown-dokter')" class="w-full flex items-center justify-between px-3.5 py-3 rounded-2xl brand-gradient text-white font-semibold text-sm shadow-sm transition">
                        <div class="flex items-center space-x-3">
                            <span>👨‍⚕️</span>
                            <span>Kelola Dokter</span>
                        </div>
                        <span class="text-xs">▼</span>
                    </button>
                    <div id="dropdown-dokter" class="pl-10 space-y-1 py-1">
                        <a href="tambah_dokter.php" class="block px-3 py-2 rounded-xl text-xs text-cyan-700 font-bold bg-cyan-50 transition">Tambah Dokter</a>
                        <a href="jadwal_praktik.php" class="block px-3 py-2 rounded-xl text-xs text-slate-600 hover:bg-slate-100 font-medium transition">Jadwal Praktik</a>
                        <a href="update_kuota.php" class="block px-3 py-2 rounded-xl text-xs text-slate-600 hover:bg-slate-100 font-medium transition">Update Kuota</a>
                    </div>
                </div>

                <div class="space-y-1">
                    <button onclick="toggleDropdown('dropdown-laporan')" class="w-full flex items-center justify-between px-3.5 py-3 rounded-2xl text-slate-600 hover:bg-slate-50 font-medium text-sm transition">
                        <div class="flex items-center space-x-3">
                            <span>📈</span>
                            <span>Laporan</span>
                        </div>
                        <span class="text-xs">▼</span>
                    </button>
                    <div id="dropdown-laporan" class="hidden pl-10 space-y-1 py-1">
                        <a href="laporan_harian.php" class="block px-3 py-2 rounded-xl text-xs text-slate-600 hover:bg-slate-100 font-medium transition">Harian</a>
                        <a href="laporan_mingguan.php" class="block px-3 py-2 rounded-xl text-xs text-slate-600 hover:bg-slate-100 font-medium transition">Mingguan</a>
                        <a href="laporan_bulanan.php" class="block px-3 py-2 rounded-xl text-xs text-slate-600 hover:bg-slate-100 font-medium transition">Bulanan</a>
                        <a href="laporan_tahunan.php" class="block px-3 py-2 rounded-xl text-xs text-slate-600 hover:bg-slate-100 font-medium transition">Tahunan</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="p-5 border-t border-slate-100 space-y-1">
            <div class="flex items-center space-x-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 inline-block animate-pulse"></span>
                <span class="text-xs font-bold text-slate-700">Portal Petugas</span>
            </div>
            <p class="text-[11px] text-slate-400">Sistem Antrean Klinik</p>
        </div>
    </aside>

    <!-- KONTEN UTAMA KANAN -->
    <div class="flex-1 flex flex-col min-w-0">
        
        <header class="bg-white border-b border-slate-200 h-20 px-6 sm:px-10 flex items-center justify-between sticky top-0 z-40 shadow-2xs">
            <div>
                <h1 class="text-2xl font-black text-slate-800 tracking-tight">Kelola Dokter</h1>
                <p class="text-xs font-medium text-slate-400">Tambah data dokter baru klinik</p>
            </div>

            <div class="flex items-center space-x-4">
                <div class="flex items-center space-x-3 bg-slate-50 border border-slate-200 px-4 py-2 rounded-2xl shadow-2xs">
                    <div class="bg-cyan-100 text-cyan-800 font-extrabold text-xs px-2.5 py-1 rounded-xl">PT</div>
                    <div class="text-left">
                        <span class="block text-xs font-extrabold text-slate-800"><?= htmlspecialchars($_SESSION['nama_petugas']); ?></span>
                        <span class="block text-[10px] font-semibold text-slate-400">Ratu Farma Aceh</span>
                    </div>
                </div>
                <a href="logout.php" class="border border-red-200 bg-red-50 hover:bg-red-100 text-red-600 px-4 py-2 rounded-xl text-xs font-bold transition flex items-center space-x-1.5 shadow-2xs">
                    <span>🚪</span>
                    <span>Keluar</span>
                </a>
            </div>
        </header>

        <main class="flex-1 p-6 sm:p-10 space-y-8">
            
            <div class="bg-white border border-slate-200 rounded-3xl p-6 sm:p-8 max-w-2xl mx-auto shadow-xs space-y-6">
                
                <div>
                    <h2 class="text-xl font-black text-slate-800">Form Tambah Dokter</h2>
                    <p class="text-xs text-slate-400">Masukkan informasi lengkap dokter beserta poliklinik penempatan dan akun loginnya.</p>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="bg-red-50 text-red-700 p-4 rounded-xl text-xs font-medium border border-red-200">
                        <?= htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="bg-emerald-50 text-emerald-800 p-4 rounded-xl text-xs font-medium border border-emerald-200">
                        <?= htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap Dokter &amp; Gelar</label>
                        <input type="text" name="nama_dokter" required placeholder="Contoh: dr. Budi Santoso, Sp.A" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-cyan-600 bg-slate-50">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Poli Penempatan</label>
                        <select name="id_poli" required class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-cyan-600 bg-slate-50">
                            <option value="">-- Pilih Poli --</option>
                            <?php foreach ($daftar_poli as $p): ?>
                                <option value="<?= $p['id_poli']; ?>"><?= htmlspecialchars($p['nama_poli']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Spesialisasi</label>
                        <input type="text" name="spesialisasi" required placeholder="Contoh: Spesialis Anak" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-cyan-600 bg-slate-50">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor HP / WhatsApp</label>
                        <input type="text" name="no_telepon" required placeholder="Contoh: 081234567890" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-cyan-600 bg-slate-50">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Username Login</label>
                            <input type="text" name="username" required placeholder="Username untuk dokter" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-cyan-600 bg-slate-50">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Kata Sandi (Password)</label>
                            <input type="password" name="password" required placeholder="Kata sandi akun" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-cyan-600 bg-slate-50">
                        </div>
                    </div>

                    <button type="submit" class="w-full brand-gradient hover:opacity-95 text-white font-bold py-3 rounded-xl text-xs transition shadow-sm cursor-pointer">
                        Simpan Data Dokter Baru
                    </button>
                </form>

            </div>
        </main>
    </div>

    <script>
        function toggleDropdown(id) {
            const dropdown = document.getElementById(id);
            dropdown.classList.toggle('hidden');
        }
    </script>
</body>
</html>