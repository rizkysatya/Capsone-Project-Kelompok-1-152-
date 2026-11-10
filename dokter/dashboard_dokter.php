<?php
// dokter/dashboard_dokter.php
session_start();
require_once '../config/koneksi.php';
 
// Wajib login dokter
if (!isset($_SESSION['id_dokter'])) {
    header("Location: login_dokter.php");
    exit();
}

$id_dokter = $_SESSION['id_dokter'];
$nama_dokter = $_SESSION['nama_dokter'] ?? 'Dokter';
$nama_poli   = $_SESSION['nama_poli'] ?? 'Klinik Ratu Farma';
 
$tanggal_aktif = isset($_GET['tanggal']) && !empty($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d');
 
// Nilai awal, supaya halaman tidak fatal error jika query gagal
$stat = [];
$antrean_list = [];
 
try {
    // Statistik dipisah menjadi 4 kategori: Hadir, Dipanggil, Selesai, dan Batal
    $stmtStat = $pdo->prepare("SELECT 
        COUNT(CASE WHEN a.status_antrean = 'Check-in' THEN 1 END) as total_hadir,
        COUNT(CASE WHEN a.status_antrean = 'Dipanggil' THEN 1 END) as total_dipanggil,
        COUNT(CASE WHEN a.status_antrean = 'Selesai' THEN 1 END) as total_selesai,
        COUNT(CASE WHEN a.status_antrean = 'Batal' THEN 1 END) as total_batal
        FROM antrean a
        JOIN pendaftaran pd ON a.id_pendaftaran = pd.id_pendaftaran
        JOIN jadwal j ON pd.id_jadwal = j.id_jadwal
        WHERE j.id_dokter = ? AND DATE(pd.tgl_kunjungan) = ?");
    $stmtStat->execute([$id_dokter, $tanggal_aktif]);
    $stat = $stmtStat->fetch(PDO::FETCH_ASSOC) ?: [];
 
    // pd.* dan p.* = semua kolom pendaftaran & pasien (nik, alamat, keluhan, dll. jika ada)
    $stmtAntrean = $pdo->prepare("SELECT pd.*, p.*, a.id_antrean, a.nomor_antrean, a.status_antrean, p.nama AS nama_pasien
        FROM antrean a
        JOIN pendaftaran pd ON a.id_pendaftaran = pd.id_pendaftaran
        JOIN pasien p ON pd.id_pasien = p.id_pasien
        JOIN jadwal j ON pd.id_jadwal = j.id_jadwal
        WHERE j.id_dokter = ? AND DATE(pd.tgl_kunjungan) = ? AND a.status_antrean IN ('Check-in', 'Dipanggil', 'Lewati')
        ORDER BY FIELD(a.status_antrean, 'Dipanggil', 'Check-in', 'Lewati'), a.id_antrean ASC");
    $stmtAntrean->execute([$id_dokter, $tanggal_aktif]);
    $antrean_list = $stmtAntrean->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Gagal memuat data: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard Dokter - Klinik Ratu Farma</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="../assets/css/dashboard-petugas.css?v=<?= time(); ?>">
</head>
<body class="min-h-screen flex bg-slate-50">
 
    <!-- SIDEBAR KIRI -->
    <aside class="w-64 bg-white border-r border-slate-200 flex flex-col justify-between hidden md:flex sticky top-0 h-screen shadow-xs z-50">
        <div>
            <div class="p-6 border-b border-slate-100 flex items-center space-x-3">
                <img src="../assets/img/logo-ratu-farma.jpeg" alt="Logo Ratu Farma" class="h-10 w-auto object-contain" onerror="this.style.display='none';">
                <div>
                    <h1 class="font-black text-slate-800 text-sm leading-tight">Ratu Farma</h1>
                    <span class="text-[11px] font-semibold text-cyan-600">Portal Dokter</span>
                </div>
            </div>
 
            <div class="p-4 space-y-1.5">
                <a href="dashboard_dokter.php" class="flex items-center space-x-3 px-3.5 py-3 rounded-2xl brand-gradient text-white font-semibold text-sm shadow-sm transition">
                    <span>📊</span><span>Dashboard</span>
                </a>
                <a href="antrean.php" class="flex items-center space-x-3 px-3.5 py-3 rounded-2xl text-slate-600 hover:bg-slate-50 font-medium text-sm transition">
                    <span>☷</span><span>Antrean Pasien</span>
                </a>
                <a href="riwayat.php" class="flex items-center space-x-3 px-3.5 py-3 rounded-2xl text-slate-600 hover:bg-slate-50 font-medium text-sm transition">
                    <span>◷</span><span>Riwayat Pelayanan</span>
                </a>
                <a href="jadwal.php" class="flex items-center space-x-3 px-3.5 py-3 rounded-2xl text-slate-600 hover:bg-slate-50 font-medium text-sm transition">
                    <span>📅</span><span>Jadwal Praktik</span>
                </a>
                <a href="profil.php" class="flex items-center space-x-3 px-3.5 py-3 rounded-2xl text-slate-600 hover:bg-slate-50 font-medium text-sm transition">
                    <span>♙</span><span>Profil Dokter</span>
                </a>
            </div>
        </div>
 
        <div class="p-5 border-t border-slate-100">
            <span class="text-xs font-bold text-slate-700">Portal Dokter</span>
        </div>
    </aside>
 
    <!-- KONTEN UTAMA KANAN -->
    <div class="flex-1 flex flex-col min-w-0">
        <header class="bg-white border-b border-slate-200 h-20 px-6 sm:px-10 flex items-center justify-between sticky top-0 z-30 shadow-2xs">
            <div>
                <h1 class="text-2xl font-black text-slate-800 tracking-tight">Dashboard Dokter</h1>
                <p class="text-xs font-medium text-slate-400">Login sebagai <strong class="text-cyan-800"><?= htmlspecialchars($nama_dokter); ?></strong></p>
            </div>
            <a href="logout.php" class="border border-red-200 bg-red-50 hover:bg-red-100 text-red-600 px-4 py-2 rounded-xl text-xs font-bold transition">
                Keluar
            </a>
        </header>
 
        <main class="flex-1 p-6 sm:p-10 space-y-8">
            <div class="brand-gradient rounded-3xl p-8 text-white shadow-md space-y-2">
                <h2 class="text-2xl font-black tracking-tight">Selamat bertugas, <?= htmlspecialchars($nama_dokter); ?></h2>
                <p class="text-xs text-cyan-100 font-medium">Periksa dan berikan pelayanan terbaik untuk pasien di <?= htmlspecialchars($nama_poli); ?>.</p>
            </div>
 
            <?php if (isset($error)): ?>
                <div class="bg-red-50 text-red-700 p-4 rounded-2xl text-xs font-medium border border-red-200 shadow-2xs">
                    <?= htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
 
            <!-- STATISTIK (DIPISAH MENJADI 4 KARTU) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="border border-slate-200 rounded-2xl p-5 space-y-1 bg-white shadow-2xs">
                    <span class="text-xs text-slate-400 font-semibold">Pasien Hadir (Check-in)</span>
                    <div class="text-2xl font-black text-indigo-600"><?= $stat['total_hadir'] ?? 0; ?></div>
                </div>
                <div class="border border-slate-200 rounded-2xl p-5 space-y-1 bg-white shadow-2xs">
                    <span class="text-xs text-slate-400 font-semibold">Sedang Dipanggil</span>
                    <div class="text-2xl font-black text-cyan-600"><?= $stat['total_dipanggil'] ?? 0; ?></div>
                </div>
                <div class="border border-slate-200 rounded-2xl p-5 space-y-1 bg-white shadow-2xs">
                    <span class="text-xs text-slate-400 font-semibold">Selesai Diperiksa</span>
                    <div class="text-2xl font-black text-emerald-600"><?= $stat['total_selesai'] ?? 0; ?></div>
                </div>
                <div class="border border-slate-200 rounded-2xl p-5 space-y-1 bg-white shadow-2xs">
                    <span class="text-xs text-slate-400 font-semibold">Antrean Dibatalkan</span>
                    <div class="text-2xl font-black text-red-600"><?= $stat['total_batal'] ?? 0; ?></div>
                </div>
            </div>
 
            <!-- TABEL ANTREAN & FILTER TANGGAL -->
            <div class="bg-white border border-slate-200 rounded-3xl p-6 sm:p-8 space-y-6 shadow-xs">
                <form method="GET" action="dashboard_dokter.php" class="flex flex-col sm:flex-row justify-between items-start sm:items-center border-b border-slate-100 pb-6 gap-4">
                    <div>
                        <h3 class="text-xl font-black text-slate-800">Antrean Pasien</h3>
                        <p class="text-xs text-slate-400">Daftar pasien yang sudah check-in tanggal <?= date('d/m/Y', strtotime($tanggal_aktif)); ?></p>
                    </div>
 
                    <div class="flex items-center space-x-3 w-full sm:w-auto">
                        <span class="text-xs font-semibold text-slate-600 whitespace-nowrap">Pilih Tanggal:</span>
                        <input type="date" name="tanggal" value="<?= htmlspecialchars($tanggal_aktif); ?>" class="px-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:outline-none focus:ring-2 focus:ring-cyan-600">
                        <button type="submit" class="bg-cyan-700 hover:bg-cyan-800 text-white px-4 py-2 rounded-xl text-xs font-semibold transition">
                            Tampilkan
                        </button>
                    </div>
                </form>
 
                <div class="overflow-x-auto pt-2">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-xs uppercase tracking-wider text-slate-400 border-b border-slate-100">
                                <th class="py-3 px-4 font-semibold">No Antrean</th>
                                <th class="py-3 px-4 font-semibold">Nama Pasien</th>
                                <th class="py-3 px-4 font-semibold">Status</th>
                                <th class="py-3 px-4 font-semibold text-center">Aksi Pelayanan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            <?php if (count($antrean_list) > 0): ?>
                                <?php foreach ($antrean_list as $row): ?>
                                <?php
                                    // Cari kolom keluhan dari beberapa kemungkinan nama
                                    $keluhan = '-';
                                    foreach (['keluhan', 'keluhan_pasien', 'keluhan_utama', 'catatan', 'catatan_keluhan'] as $kol) {
                                        if (!empty($row[$kol])) { $keluhan = $row[$kol]; break; }
                                    }
                                    // Data yang ditampilkan di popup "Data Pasien"
                                    $detail = [
                                        'nama'          => $row['nama_pasien'] ?? '-',
                                        'nomor_antrean' => $row['nomor_antrean'] ?? '-',
                                        'no_hp'         => $row['no_hp'] ?? '-',
                                        'nik'           => $row['nik'] ?? '-',
                                        'jenis_kelamin' => $row['jenis_kelamin'] ?? '-',
                                        'tgl_lahir'     => $row['tgl_lahir'] ?? '-',
                                        'alamat'        => $row['alamat'] ?? '-',
                                        'dokter'        => $nama_dokter,
                                        'tgl_kunjungan' => $row['tgl_kunjungan'] ?? '-',
                                        'status'        => $row['status_antrean'] ?? '-',
                                        'keluhan'       => $keluhan,
                                    ];
                                ?>
                                <tr class="hover:bg-slate-50/80 transition" id="row-<?= $row['id_antrean']; ?>">
                                    <td class="py-4 px-4 font-bold text-cyan-700"><?= htmlspecialchars($row['nomor_antrean']); ?></td>
                                    <td class="py-4 px-4 font-semibold text-slate-800">
                                        <?= htmlspecialchars($row['nama_pasien']); ?>
                                        <?php if (!empty($row['jenis_kelamin'])): ?>
                                        <div class="text-xs font-normal text-slate-500">👤 <?= htmlspecialchars($row['jenis_kelamin']); ?></div>
                                        <?php endif; ?>
                                        <?php if ($keluhan !== '-'): ?>
                                        <div class="text-xs font-normal text-amber-700 mt-1 max-w-xs whitespace-pre-line">🩺 Keluhan: <?= htmlspecialchars($keluhan); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-4 px-4">
                                        <span id="badge-<?= $row['id_antrean']; ?>" class="px-3 py-1 rounded-full text-xs font-bold 
                                            <?= $row['status_antrean'] == 'Dipanggil' ? 'bg-cyan-100 text-cyan-800' : ($row['status_antrean'] == 'Lewati' ? 'bg-amber-100 text-amber-800' : 'bg-indigo-100 text-indigo-800'); ?>">
                                            <?= htmlspecialchars($row['status_antrean']); ?>
                                        </span>
                                    </td>
                                    <td class="py-4 px-4 text-center">
                                        <div class="flex flex-wrap items-center justify-center gap-1.5">
                                            <button type="button"
                                                    data-pasien="<?= htmlspecialchars(json_encode($detail, JSON_UNESCAPED_UNICODE), ENT_QUOTES); ?>"
                                                    onclick="bukaDataPasien(this)"
                                                    class="bg-cyan-50 hover:bg-cyan-100 text-cyan-800 border border-cyan-200 px-3 py-1.5 rounded-xl text-xs font-semibold transition shadow-2xs whitespace-nowrap cursor-pointer">
                                                👤 Data Pasien
                                            </button>
                                            <button onclick="ubahStatus(<?= $row['id_antrean']; ?>, 'Dipanggil')" class="bg-cyan-600 text-white px-3 py-1.5 rounded-xl text-xs font-semibold cursor-pointer whitespace-nowrap">📢 Panggil</button>
                                            <button onclick="ubahStatus(<?= $row['id_antrean']; ?>, 'Lewati')" class="bg-amber-500 text-white px-3 py-1.5 rounded-xl text-xs font-semibold cursor-pointer whitespace-nowrap">⏭ Lewati</button>
                                            <button onclick="ubahStatus(<?= $row['id_antrean']; ?>, 'Batal')" class="bg-red-500 text-white px-3 py-1.5 rounded-xl text-xs font-semibold cursor-pointer whitespace-nowrap">❌ Batal</button>
                                            <button onclick="ubahStatus(<?= $row['id_antrean']; ?>, 'Selesai')" class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 rounded-xl text-xs font-semibold transition shadow-2xs cursor-pointer whitespace-nowrap">🩺 Selesai / Periksa</button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="4" class="py-12 text-center text-slate-400 text-sm">Tidak ada pasien dalam antrean aktif pada tanggal ini.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
 
    <!-- MODAL DATA PASIEN -->
    <div id="modal-pasien" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4" onclick="if(event.target===this) tutupDataPasien()">
        <div class="bg-white rounded-3xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                <div>
                    <h3 class="text-base font-black text-slate-800">Data Pasien</h3>
                    <p class="text-xs font-bold text-cyan-700" id="mp-nomor">-</p>
                </div>
                <button type="button" onclick="tutupDataPasien()" class="text-slate-400 hover:text-slate-700 text-xl leading-none px-2">&times;</button>
            </div>
 
            <div class="px-6 py-5 space-y-4 text-sm">
                <dl class="grid grid-cols-3 gap-x-3 gap-y-3">
                    <dt class="text-xs font-semibold text-slate-400">Nama</dt>
                    <dd class="col-span-2 font-semibold text-slate-800" id="mp-nama">-</dd>
 
                    <dt class="text-xs font-semibold text-slate-400">No. Telepon</dt>
                    <dd class="col-span-2 text-slate-700" id="mp-no_hp">-</dd>
 
                    <dt class="text-xs font-semibold text-slate-400">NIK</dt>
                    <dd class="col-span-2 text-slate-700" id="mp-nik">-</dd>
 
                    <dt class="text-xs font-semibold text-slate-400">Jenis Kelamin</dt>
                    <dd class="col-span-2 text-slate-700" id="mp-jenis_kelamin">-</dd>
 
                    <dt class="text-xs font-semibold text-slate-400">Tanggal Lahir</dt>
                    <dd class="col-span-2 text-slate-700" id="mp-tgl_lahir">-</dd>
 
                    <dt class="text-xs font-semibold text-slate-400">Alamat</dt>
                    <dd class="col-span-2 text-slate-700" id="mp-alamat">-</dd>
 
                    <dt class="text-xs font-semibold text-slate-400">Dokter</dt>
                    <dd class="col-span-2 text-slate-700" id="mp-dokter">-</dd>
 
                    <dt class="text-xs font-semibold text-slate-400">Tgl. Kunjungan</dt>
                    <dd class="col-span-2 text-slate-700" id="mp-tgl_kunjungan">-</dd>
 
                    <dt class="text-xs font-semibold text-slate-400">Status</dt>
                    <dd class="col-span-2 text-slate-700" id="mp-status">-</dd>
                </dl>
 
                <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4">
                    <span class="block text-xs font-bold text-amber-800 mb-1">🩺 Keluhan Pasien</span>
                    <p class="text-sm text-slate-700 whitespace-pre-line" id="mp-keluhan">-</p>
                </div>
            </div>
 
            <div class="px-6 py-4 border-t border-slate-100 text-right">
                <button type="button" onclick="tutupDataPasien()" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-5 py-2 rounded-xl text-xs font-semibold transition">Tutup</button>
            </div>
        </div>
    </div>
 
    <script>
        function bukaDataPasien(btn) {
            const data = JSON.parse(btn.getAttribute('data-pasien'));
            ['nomor', 'nama', 'no_hp', 'nik', 'jenis_kelamin', 'tgl_lahir', 'alamat', 'dokter', 'tgl_kunjungan', 'status', 'keluhan'].forEach(function (k) {
                const el = document.getElementById('mp-' + k);
                const val = (k === 'nomor') ? data.nomor_antrean : data[k];
                if (el) el.textContent = (val !== null && val !== '') ? val : '-';
            });
            document.getElementById('modal-pasien').classList.remove('hidden');
        }
 
        function tutupDataPasien() {
            document.getElementById('modal-pasien').classList.add('hidden');
        }
 
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') tutupDataPasien();
        });
 
        function ubahStatus(idAntrean, status) {
            $.ajax({
                url: 'update_status_dokter.php',
                type: 'POST',
                data: { id_antrean: idAntrean, status: status },
                dataType: 'json',
                success: function(res) {
                    if(res.success) {
                        location.reload();
                    } else {
                        alert(res.message);
                    }
                },
                error: function() {
                    alert('Terjadi kesalahan saat menghubungkan ke server.');
                }
            });
        }
    </script>
</body>
</html>