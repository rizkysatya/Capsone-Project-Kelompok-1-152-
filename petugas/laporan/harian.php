<?php
// petugas/laporan/harian.php
session_start();
require_once '../../config/koneksi.php';

if (!isset($_SESSION['id_petugas'])) {
    header("Location: ../login.php");
    exit();
}

$tanggal = isset($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d');
$id_poli = isset($_GET['id_poli']) ? $_GET['id_poli'] : '';
$error   = '';
$laporan_list = [];

try {
    $stmtPoli = $pdo->query("SELECT * FROM poli ORDER BY nama_poli ASC");
    $daftar_poli = $stmtPoli->fetchAll(PDO::FETCH_ASSOC);

    $sql = "SELECT pd.tgl_kunjungan, p.nama AS nama_pasien, p.nik, po.nama_poli, d.nama_dokter, pd.sumber_daftar, 
                   COALESCE(a.status_antrean, 'Menunggu') AS status_antrean
            FROM pendaftaran pd
            JOIN pasien p ON pd.id_pasien = p.id_pasien
            JOIN jadwal j ON pd.id_jadwal = j.id_jadwal
            JOIN dokter d ON j.id_dokter = d.id_dokter
            JOIN poli po ON d.id_poli = po.id_poli
            LEFT JOIN antrean a ON pd.id_pendaftaran = a.id_pendaftaran
            WHERE pd.tgl_kunjungan = ?";
    
    $params = [$tanggal];

    if (!empty($id_poli)) {
        $sql .= " AND po.id_poli = ?";
        $params[] = $id_poli;
    }

    $sql .= " ORDER BY pd.tgl_kunjungan DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $laporan_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Gagal memuat laporan: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Harian - Klinik Ratu Farma</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="../../assets/css/dashboard-petugas.css">
    <style>
        @media print {
            body { background: white !important; padding: 0 !important; }
            aside, header, .no-print { display: none !important; }
            .print-area { box-shadow: none !important; border: none !important; width: 100% !important; padding: 0 !important; }
        }
    </style>
</head>
<body class="min-h-screen flex">

    <!-- SIDEBAR KIRI -->
    <aside class="w-64 bg-white border-r border-slate-200 flex flex-col justify-between hidden md:flex sticky top-0 h-screen shadow-xs no-print">
        <div>
            <div class="p-6 border-b border-slate-100 flex items-center space-x-3">
                <img src="../../assets/img/logo-ratu-farma.jpeg" alt="Logo Ratu Farma" class="h-10 w-auto object-contain" onerror="this.style.display='none'; document.getElementById('fallback-logo').style.display='flex';">
                <div id="fallback-logo" style="display:none;" class="bg-gradient-to-tr from-cyan-600 to-blue-700 text-white p-2 rounded-xl font-bold text-sm">RF</div>
                <div>
                    <h1 class="font-black text-slate-800 text-sm leading-tight">Ratu Farma</h1>
                    <span class="text-[11px] font-semibold text-cyan-600">Portal Petugas</span>
                </div>
            </div>

            <div class="p-4 space-y-1.5">
                <span class="px-3 text-[10px] font-bold tracking-wider text-slate-400 uppercase">Menu Petugas</span>
                <a href="../dashboard_petugas.php" class="flex items-center space-x-3 px-3.5 py-3 rounded-2xl text-slate-600 hover:bg-slate-50 font-medium text-sm transition"><span>📊</span><span>Dashboard</span></a>
                <a href="../pasien_offline.php" class="flex items-center space-x-3 px-3.5 py-3 rounded-2xl text-slate-600 hover:bg-slate-50 font-medium text-sm transition"><span>🏥</span><span>Pasien Offline</span></a>

                <div class="space-y-1">
                    <button onclick="toggleDropdown('dropdown-dokter')" class="w-full flex items-center justify-between px-3.5 py-3 rounded-2xl text-slate-600 hover:bg-slate-50 font-medium text-sm transition">
                        <div class="flex items-center space-x-3"><span>👨‍⚕️</span><span>Kelola Dokter</span></div>
                        <span class="text-xs">▼</span>
                    </button>
                    <div id="dropdown-dokter" class="hidden pl-10 space-y-1 py-1">
                        <a href="../tambah_dokter.php" class="block px-3 py-2 rounded-xl text-xs text-slate-600 hover:bg-slate-100 font-medium transition">Tambah Dokter</a>
                        <a href="../jadwal_praktik.php" class="block px-3 py-2 rounded-xl text-xs text-slate-600 hover:bg-slate-100 font-medium transition">Jadwal Praktik</a>
                        <a href="../update_kuota.php" class="block px-3 py-2 rounded-xl text-xs text-slate-600 hover:bg-slate-100 font-medium transition">Update Kuota</a>
                    </div>
                </div>

                <div class="space-y-1">
                    <button onclick="toggleDropdown('dropdown-laporan')" class="w-full flex items-center justify-between px-3.5 py-3 rounded-2xl brand-gradient text-white font-semibold text-sm shadow-sm transition">
                        <div class="flex items-center space-x-3"><span>📈</span><span>Laporan</span></div>
                        <span class="text-xs">▼</span>
                    </button>
                    <div id="dropdown-laporan" class="pl-10 space-y-1 py-1">
                        <a href="harian.php" class="block px-3 py-2 rounded-xl text-xs text-cyan-700 font-bold bg-cyan-50 transition">Harian</a>
                        <a href="mingguan.php" class="block px-3 py-2 rounded-xl text-xs text-slate-600 hover:bg-slate-100 font-medium transition">Mingguan</a>
                        <a href="bulanan.php" class="block px-3 py-2 rounded-xl text-xs text-slate-600 hover:bg-slate-100 font-medium transition">Bulanan</a>
                        <a href="tahunan.php" class="block px-3 py-2 rounded-xl text-xs text-slate-600 hover:bg-slate-100 font-medium transition">Tahunan</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="p-5 border-t border-slate-100 space-y-1">
            <div class="flex items-center space-x-2"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500 inline-block animate-pulse"></span><span class="text-xs font-bold text-slate-700">Portal Petugas</span></div>
            <p class="text-[11px] text-slate-400">Sistem Antrean Klinik</p>
        </div>
    </aside>

    <!-- KONTEN UTAMA -->
    <div class="flex-1 flex flex-col min-w-0">
        <header class="bg-white border-b border-slate-200 h-20 px-6 sm:px-10 flex items-center justify-between sticky top-0 z-40 shadow-2xs no-print">
            <div><h1 class="text-2xl font-black text-slate-800 tracking-tight">Laporan Harian</h1><p class="text-xs font-medium text-slate-400">Rekapitulasi kunjungan pasien harian</p></div>
            <a href="../logout.php" class="border border-red-200 bg-red-50 hover:bg-red-100 text-red-600 px-4 py-2 rounded-xl text-xs font-bold transition">Keluar</a>
        </header>

        <main class="flex-1 p-6 sm:p-10 space-y-8">
            <div class="print-area bg-white border border-slate-200 rounded-3xl p-6 sm:p-8 shadow-xs space-y-6 max-w-6xl mx-auto w-full">
                
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 no-print">
                    <form method="GET" action="harian.php" class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Pilih Tanggal</label>
                            <input type="date" name="tanggal" value="<?= htmlspecialchars($tanggal); ?>" class="px-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50">
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Filter Poli</label>
                            <select name="id_poli" class="px-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:outline-none focus:ring-2 focus:ring-cyan-600">
                                <option value="">-- Semua Poli (Keseluruhan) --</option>
                                <?php foreach ($daftar_poli as $p): ?>
                                    <option value="<?= $p['id_poli']; ?>" <?= $id_poli == $p['id_poli'] ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($p['nama_poli']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="self-end">
                            <button type="submit" class="bg-cyan-700 hover:bg-cyan-800 text-white px-4 py-2 rounded-xl text-xs font-semibold transition">Tampilkan</button>
                        </div>
                    </form>
                    <button onclick="window.print()" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-xl text-xs font-bold transition shadow-2xs self-end sm:self-center">🖨️ Cetak Laporan</button>
                </div>

                <div class="text-center space-y-1 pb-4 border-b border-slate-100">
                    <h2 class="text-xl font-black text-slate-800 uppercase">Laporan Kunjungan Harian Klinik Ratu Farma</h2>
                    <p class="text-xs text-slate-500">Tanggal: <strong><?= date('d-m-Y', strtotime($tanggal)); ?></strong> | Total: <strong><?= count($laporan_list); ?> Pasien</strong></p>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="bg-red-50 text-red-700 p-4 rounded-xl text-xs font-medium border border-red-200"><?= htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <div class="overflow-x-auto pt-2">
                    <table class="w-full text-left border-collapse border border-slate-200">
                        <thead>
                            <tr class="bg-slate-50 text-xs uppercase tracking-wider text-slate-600 border-b border-slate-200">
                                <th class="py-3 px-4 border border-slate-200 text-center">No</th>
                                <th class="py-3 px-4 border border-slate-200">Tanggal</th>
                                <th class="py-3 px-4 border border-slate-200">Nama Pasien</th>
                                <th class="py-3 px-4 border border-slate-200">NIK</th>
                                <th class="py-3 px-4 border border-slate-200">Poli Tujuan</th>
                                <th class="py-3 px-4 border border-slate-200">Dokter</th>
                                <th class="py-3 px-4 border border-slate-200 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 text-xs text-slate-700">
                            <?php if (count($laporan_list) > 0): ?>
                                <?php $no = 1; foreach ($laporan_list as $row): ?>
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="py-3 px-4 border border-slate-200 text-center font-bold"><?= $no++; ?></td>
                                    <td class="py-3 px-4 border border-slate-200"><?= date('d-m-Y', strtotime($row['tgl_kunjungan'])); ?></td>
                                    <td class="py-3 px-4 border border-slate-200 font-semibold"><?= htmlspecialchars($row['nama_pasien']); ?></td>
                                    <td class="py-3 px-4 border border-slate-200"><?= htmlspecialchars($row['nik']); ?></td>
                                    <td class="py-3 px-4 border border-slate-200 font-semibold text-cyan-800"><?= htmlspecialchars($row['nama_poli'] ?? '-'); ?></td>
                                    <td class="py-3 px-4 border border-slate-200"><?= htmlspecialchars($row['nama_dokter'] ?? '-'); ?></td>
                                    <td class="py-3 px-4 border border-slate-200 text-center font-bold">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] <?= $row['status_antrean'] == 'Selesai' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'; ?>">
                                            <?= htmlspecialchars($row['status_antrean']); ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="7" class="py-12 text-center text-slate-400 text-sm border border-slate-200">Tidak ada data laporan untuk tanggal dan poli ini.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
    <script>function toggleDropdown(id) { document.getElementById(id).classList.toggle('hidden'); }</script>
</body>
</html>