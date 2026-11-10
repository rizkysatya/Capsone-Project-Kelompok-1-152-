<?php
// jadwal_praktik.php
session_start();
require_once '../config/koneksi.php';

if (!isset($_SESSION['id_petugas'])) {
    header("Location: login.php");
    exit();
}

$error = '';
$success = '';

try {
    // Ambil daftar dokter untuk pilihan form
    $stmtDokter = $pdo->query("SELECT d.id_dokter, d.nama_dokter, p.nama_poli FROM dokter d JOIN poli p ON d.id_poli = p.id_poli ORDER BY d.nama_dokter ASC");
    $daftar_dokter = $stmtDokter->fetchAll(PDO::FETCH_ASSOC);

    // Ambil daftar jadwal yang sudah ada beserta nama dokter dan polinya
    $sqlJadwal = "SELECT j.*, d.nama_dokter, p.nama_poli 
                  FROM jadwal j 
                  JOIN dokter d ON j.id_dokter = d.id_dokter 
                  JOIN poli p ON d.id_poli = p.id_poli 
                  ORDER BY FIELD(j.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'), j.jam_mulai ASC";
    $stmtJadwalList = $pdo->query($sqlJadwal);
    $jadwal_list = $stmtJadwalList->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error = "Gagal memuat data: " . $e->getMessage();
}

// Proses form tambah jadwal
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_dokter    = trim($_POST['id_dokter'] ?? '');
    $hari         = trim($_POST['hari'] ?? '');
    $jam_mulai    = trim($_POST['jam_mulai'] ?? '');
    $jam_selesai  = trim($_POST['jam_selesai'] ?? '');
    $kuota        = trim($_POST['kuota'] ?? 20);

    if (empty($id_dokter) || empty($hari) || empty($jam_mulai) || empty($jam_selesai)) {
        $error = "Dokter, Hari, Jam Mulai, dan Jam Selesai wajib diisi!";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO jadwal (id_dokter, hari, jam_mulai, jam_selesai, kuota, kuota_default) VALUES (?, ?, ?, ?, ?, ?)");
            // Menyetorkan nilai kuota sekaligus kuota_default sesuai struktur tabel
            $stmt->execute([$id_dokter, $hari, $jam_mulai, $jam_selesai, $kuota, $kuota]);
            
            header("Location: jadwal_praktik.php?success=1");
            exit();
        } catch (PDOException $e) {
            $error = "Gagal menyimpan jadwal praktik: " . $e->getMessage();
        }
    }
}

if (isset($_GET['success'])) {
    $success = "Jadwal praktik berhasil ditambahkan!";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Praktik - Klinik Ratu Farma</title>
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
                        <a href="tambah_dokter.php" class="block px-3 py-2 rounded-xl text-xs text-slate-600 hover:bg-slate-100 font-medium transition">Tambah Dokter</a>
                        <a href="jadwal_praktik.php" class="block px-3 py-2 rounded-xl text-xs text-cyan-700 font-bold bg-cyan-50 transition">Jadwal Praktik</a>
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
                        <a href="laporan/harian.php" class="block px-3 py-2 rounded-xl text-xs text-slate-600 hover:bg-slate-100 font-medium transition">Harian</a>
                        <a href="laporan/mingguan.php" class="block px-3 py-2 rounded-xl text-xs text-slate-600 hover:bg-slate-100 font-medium transition">Mingguan</a>
                        <a href="laporan/bulanan.php" class="block px-3 py-2 rounded-xl text-xs text-slate-600 hover:bg-slate-100 font-medium transition">Bulanan</a>
                        <a href="laporan/tahunan.php" class="block px-3 py-2 rounded-xl text-xs text-slate-600 hover:bg-slate-100 font-medium transition">Tahunan</a>
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
                <h1 class="text-2xl font-black text-slate-800 tracking-tight">Jadwal Praktik Dokter</h1>
                <p class="text-xs font-medium text-slate-400">Kelola hari dan jam praktik dokter</p>
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
            
            <!-- FORM TAMBAH JADWAL -->
            <div class="bg-white border border-slate-200 rounded-3xl p-6 sm:p-8 max-w-2xl mx-auto shadow-xs space-y-6">
                <div>
                    <h2 class="text-xl font-black text-slate-800">Tambah Jadwal Praktik Baru</h2>
                    <p class="text-xs text-slate-400">Tentukan hari, jam operasional, dan kuota pasien untuk dokter.</p>
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
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Dokter</label>
                        <select name="id_dokter" required class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-cyan-600 bg-slate-50">
                            <option value="">-- Pilih Dokter --</option>
                            <?php foreach ($daftar_dokter as $d): ?>
                                <option value="<?= $d['id_dokter']; ?>"><?= htmlspecialchars($d['nama_dokter']); ?> (<?= htmlspecialchars($d['nama_poli']); ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Hari Praktik</label>
                        <select name="hari" required class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-cyan-600 bg-slate-50">
                            <option value="">-- Pilih Hari --</option>
                            <option value="Senin">Senin</option>
                            <option value="Selasa">Selasa</option>
                            <option value="Rabu">Rabu</option>
                            <option value="Kamis">Kamis</option>
                            <option value="Jumat">Jumat</option>
                            <option value="Sabtu">Sabtu</option>
                            <option value="Minggu">Minggu</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Jam Mulai</label>
                            <input type="time" name="jam_mulai" required class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-cyan-600 bg-slate-50">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Jam Selesai</label>
                            <input type="time" name="jam_selesai" required class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-cyan-600 bg-slate-50">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Kuota Pasien</label>
                        <input type="number" name="kuota" value="20" min="1" required class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-cyan-600 bg-slate-50">
                    </div>

                    <button type="submit" class="w-full brand-gradient hover:opacity-95 text-white font-bold py-3 rounded-xl text-xs transition shadow-sm">
                        Simpan Jadwal Praktik
                    </button>
                </form>
            </div>

            <!-- TABEL DAFTAR JADWAL PRAKTIK -->
            <div class="bg-white border border-slate-200 rounded-3xl p-6 sm:p-8 shadow-xs space-y-4">
                <div>
                    <h3 class="text-xl font-black text-slate-800">Daftar Jadwal Praktik Aktif</h3>
                    <p class="text-xs text-slate-400">Seluruh jadwal praktik dokter yang terdaftar di klinik.</p>
                </div>

                <div class="overflow-x-auto pt-2">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-xs uppercase tracking-wider text-slate-400 border-b border-slate-100">
                                <th class="py-3 px-4 font-semibold">No</th>
                                <th class="py-3 px-4 font-semibold">Dokter</th>
                                <th class="py-3 px-4 font-semibold">Poli</th>
                                <th class="py-3 px-4 font-semibold">Hari</th>
                                <th class="py-3 px-4 font-semibold">Jam Praktik</th>
                                <th class="py-3 px-4 font-semibold text-center">Kuota</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            <?php if (count($jadwal_list) > 0): ?>
                                <?php $no = 1; foreach ($jadwal_list as $row): ?>
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-4 px-4 font-bold text-slate-700"><?= $no++; ?></td>
                                    <td class="py-4 px-4 font-medium text-slate-800"><?= htmlspecialchars($row['nama_dokter']); ?></td>
                                    <td class="py-4 px-4 text-xs text-cyan-700 font-semibold"><?= htmlspecialchars($row['nama_poli']); ?></td>
                                    <td class="py-4 px-4 font-semibold text-slate-700"><?= htmlspecialchars($row['hari']); ?></td>
                                    <td class="py-4 px-4 text-xs text-slate-600"><?= date('H:i', strtotime($row['jam_mulai'])); ?> - <?= date('H:i', strtotime($row['jam_selesai'])); ?> WIB</td>
                                    <td class="py-4 px-4 text-center font-bold text-slate-800"><?= htmlspecialchars($row['kuota']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="py-12 text-center text-slate-400 text-sm">
                                        Belum ada jadwal praktik dokter yang terdaftar.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
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