<?php
// dashboard_petugas.php
session_start();
require_once '../config/koneksi.php';

if (!isset($_SESSION['id_petugas'])) {
    header("Location: login.php");
    exit();
}

// Nilai awal, supaya halaman tidak fatal error jika query gagal
$daftar_poli  = [];
$antrean_list = [];
$stat         = ['Menunggu' => 0, 'Check-in' => 0, 'Dipanggil' => 0, 'Lewati' => 0, 'Selesai' => 0, 'Batal' => 0];
$id_poli_aktif  = $_GET['id_poli'] ?? 1;
$tanggal_aktif  = $_GET['tanggal'] ?? date('Y-m-d');
$nama_poli_aktif = 'Pilih Poli';

try {
    $stmtPoli = $pdo->query("SELECT * FROM poli ORDER BY nama_poli ASC");
    $daftar_poli = $stmtPoli->fetchAll(PDO::FETCH_ASSOC);

    $id_poli_aktif = isset($_GET['id_poli']) ? $_GET['id_poli'] : (count($daftar_poli) > 0 ? $daftar_poli[0]['id_poli'] : 1);
    $tanggal_aktif = isset($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d');

    $stmtPoliAktif = $pdo->prepare("SELECT nama_poli FROM poli WHERE id_poli = ?");
    $stmtPoliAktif->execute([$id_poli_aktif]);
    $poli_data = $stmtPoliAktif->fetch(PDO::FETCH_ASSOC);
    $nama_poli_aktif = $poli_data['nama_poli'] ?? 'Pilih Poli';

    // pd.* dan p.* = semua kolom pendaftaran & pasien (tidak bergantung nama kolom tertentu)
    $sqlAntrean = "SELECT pd.*, p.*, a.id_antrean, a.nomor_antrean, a.status_antrean, d.nama_dokter
                   FROM antrean a
                   JOIN pendaftaran pd ON a.id_pendaftaran = pd.id_pendaftaran
                   JOIN pasien p ON pd.id_pasien = p.id_pasien
                   JOIN jadwal j ON pd.id_jadwal = j.id_jadwal
                   JOIN dokter d ON j.id_dokter = d.id_dokter
                   WHERE d.id_poli = ? AND pd.tgl_kunjungan = ?
                   ORDER BY a.id_antrean ASC";
    $stmtAntrean = $pdo->prepare($sqlAntrean);
    $stmtAntrean->execute([$id_poli_aktif, $tanggal_aktif]);
    $antrean_list = $stmtAntrean->fetchAll(PDO::FETCH_ASSOC);

    $stat = ['Menunggu' => 0, 'Check-in' => 0, 'Dipanggil' => 0, 'Lewati' => 0, 'Selesai' => 0, 'Batal' => 0];
    foreach ($antrean_list as $row) {
        $st = $row['status_antrean'];
        if (isset($stat[$st])) {
            $stat[$st]++;
        } else {
            $stat['Menunggu']++;
        }
    }

} catch (PDOException $e) {
    $error = "Gagal memuat data: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php include __DIR__ . '/head_petugas.php'; ?>
    <title>Dashboard Petugas - Klinik Ratu Farma</title>
</head>
<body class="min-h-screen flex">

    <!-- SIDEBAR KIRI -->
    <?php $halaman_aktif = 'dashboard'; include __DIR__ . '/sidebar_petugas.php'; ?>

    <!-- KONTEN UTAMA KANAN -->
    <div class="flex-1 flex flex-col min-w-0">
        
        <?php
        $judul_halaman = 'Dashboard Petugas (Check-in)';
        $sub_judul = 'Login sebagai <strong class="text-cyan-800">' . htmlspecialchars($_SESSION['nama_petugas']) . '</strong> • ' . date('Y-m-d');
        include __DIR__ . '/topbar_petugas.php';
        ?>

        <main class="flex-1 p-6 sm:p-10 space-y-8">
            
            <div class="brand-gradient rounded-3xl p-8 text-white shadow-md space-y-2">
                <h2 class="text-2xl font-black tracking-tight">Selamat bertugas, <?= htmlspecialchars($_SESSION['nama_petugas']); ?></h2>
                <p class="text-xs text-cyan-100 font-medium">Lakukan proses check-in kehadiran pasien yang datang ke klinik.</p>
            </div>

            <?php if (isset($error)): ?>
                <div class="bg-red-50 text-red-700 p-4 rounded-2xl text-xs font-medium border border-red-200 shadow-2xs">
                    <?= htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <!-- PILIH POLI -->
            <div class="bg-white border border-slate-200 rounded-3xl p-6 sm:p-8 space-y-4 shadow-xs">
                <div>
                    <h3 class="text-base font-bold text-slate-800">Pilih Poli</h3>
                    <p class="text-xs text-slate-400">Pilih poli untuk melihat antrean pasien.</p>
                </div>

                <div class="flex flex-wrap gap-2.5 pt-2">
                    <?php if (count($daftar_poli) > 0): ?>
                        <?php foreach ($daftar_poli as $poli): ?>
                            <a href="dashboard_petugas.php?id_poli=<?= $poli['id_poli']; ?>&tanggal=<?= $tanggal_aktif; ?>" 
                               class="px-4 py-2.5 rounded-xl text-xs font-semibold transition border shadow-2xs card-hover
                               <?= $id_poli_aktif == $poli['id_poli'] ? 'bg-cyan-700 text-white border-cyan-700 shadow-sm' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50'; ?>">
                                <?= htmlspecialchars($poli['nama_poli']); ?>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-xs text-slate-400">Belum ada data poli di database.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- DETAIL POLI AKTIF & KARTU STATISTIK -->
            <div class="bg-white border border-slate-200 rounded-3xl p-6 sm:p-8 space-y-6 shadow-xs">
                
                <form method="GET" action="dashboard_petugas.php" class="flex flex-col sm:flex-row justify-between items-start sm:items-center border-b border-slate-100 pb-6 gap-4">
                    <input type="hidden" name="id_poli" value="<?= htmlspecialchars($id_poli_aktif); ?>">
                    <div>
                        <h3 class="text-xl font-black text-slate-800"><?= htmlspecialchars($nama_poli_aktif); ?></h3>
                        <p class="text-xs text-slate-400">Daftar antrean pasien tanggal <?= date('d/m/Y', strtotime($tanggal_aktif)); ?></p>
                    </div>

                    <div class="flex items-center space-x-3 w-full sm:w-auto">
                        <span class="text-xs font-medium text-slate-500 whitespace-nowrap">Tanggal antrean</span>
                        <input type="date" name="tanggal" value="<?= htmlspecialchars($tanggal_aktif); ?>" class="px-3 py-2 text-xs border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-cyan-600 bg-slate-50">
                        <button type="submit" class="bg-cyan-700 hover:bg-cyan-800 text-white px-4 py-2 rounded-xl text-xs font-semibold transition shadow-2xs">
                            Tampilkan
                        </button>
                    </div>
                </form>

                <!-- KARTU STATISTIK (sinkron dengan portal dokter, diperbarui otomatis) -->
                <div id="kartu-statistik" class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                    <div class="border border-slate-200 rounded-2xl p-5 space-y-1 bg-white shadow-2xs">
                        <span class="text-xs text-slate-400 font-semibold">Menunggu</span>
                        <div class="text-2xl font-black text-amber-600"><?= $stat['Menunggu']; ?></div>
                    </div>
                    <div class="border border-slate-200 rounded-2xl p-5 space-y-1 bg-white shadow-2xs">
                        <span class="text-xs text-slate-400 font-semibold">Check-in / Hadir</span>
                        <div class="text-2xl font-black text-indigo-600"><?= $stat['Check-in']; ?></div>
                    </div>
                    <div class="border border-slate-200 rounded-2xl p-5 space-y-1 bg-white shadow-2xs">
                        <span class="text-xs text-slate-400 font-semibold">Sedang Dipanggil</span>
                        <div class="text-2xl font-black text-cyan-600"><?= $stat['Dipanggil']; ?></div>
                    </div>
                    <div class="border border-slate-200 rounded-2xl p-5 space-y-1 bg-white shadow-2xs">
                        <span class="text-xs text-slate-400 font-semibold">Dilewati</span>
                        <div class="text-2xl font-black text-orange-500"><?= $stat['Lewati']; ?></div>
                    </div>
                    <div class="border border-slate-200 rounded-2xl p-5 space-y-1 bg-white shadow-2xs">
                        <span class="text-xs text-slate-400 font-semibold">Selesai (sudah ditangani dokter)</span>
                        <div class="text-2xl font-black text-emerald-600"><?= $stat['Selesai']; ?></div>
                    </div>
                    <div class="border border-slate-200 rounded-2xl p-5 space-y-1 bg-white shadow-2xs">
                        <span class="text-xs text-slate-400 font-semibold">Batal</span>
                        <div class="text-2xl font-black text-red-600"><?= $stat['Batal']; ?></div>
                    </div>
                </div>

                <!-- TABEL DAFTAR ANTREAN -->
                <div class="overflow-x-auto pt-2">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-xs uppercase tracking-wider text-slate-400 border-b border-slate-100">
                                <th class="py-3 px-4 font-semibold">No</th>
                                <th class="py-3 px-4 font-semibold">Pasien</th>
                                <th class="py-3 px-4 font-semibold">Dokter</th>
                                <th class="py-3 px-4 font-semibold">Status</th>
                                <th class="py-3 px-4 font-semibold text-center">Aksi Check-in</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-antrean" class="divide-y divide-slate-100 text-sm">
                            <?php if (count($antrean_list) > 0): ?>
                                <?php $no = 1; foreach ($antrean_list as $row): ?>
                                <tr class="hover:bg-slate-50/80 transition" id="row-<?= $row['id_antrean']; ?>">
                                    <td class="py-4 px-4 font-bold text-slate-700"><?= $no++; ?></td>

                                    <td class="py-4 px-4 font-medium text-slate-800">
                                        <?= htmlspecialchars($row['nama']); ?>
                                        <div class="text-xs font-bold text-cyan-700"><?= htmlspecialchars($row['nomor_antrean']); ?></div>
                                        <?php if (!empty($row['jenis_kelamin'])): ?>
                                        <div class="text-xs text-slate-500">👤 <?= htmlspecialchars($row['jenis_kelamin']); ?></div>
                                        <?php endif; ?>
                                        <?php if (!empty($row['keluhan'])): ?>
                                        <div class="text-xs text-amber-700 mt-1 max-w-xs whitespace-pre-line">🩺 Keluhan: <?= htmlspecialchars($row['keluhan']); ?></div>
                                        <?php endif; ?>
                                    </td>

                                    <td class="py-4 px-4 text-xs text-slate-500"><?= htmlspecialchars($row['nama_dokter']); ?></td>

                                    <td class="py-4 px-4">
                                        <span id="status-badge-<?= $row['id_antrean']; ?>" class="px-3 py-1 rounded-full text-xs font-bold 
                                            <?= ['Menunggu' => 'bg-amber-100 text-amber-800', 'Check-in' => 'bg-indigo-100 text-indigo-800', 'Dipanggil' => 'bg-cyan-100 text-cyan-800', 'Lewati' => 'bg-orange-100 text-orange-800', 'Selesai' => 'bg-emerald-100 text-emerald-800', 'Batal' => 'bg-red-100 text-red-800'][$row['status_antrean']] ?? 'bg-slate-100 text-slate-800'; ?>">
                                            <?= htmlspecialchars($row['status_antrean']); ?>
                                        </span>
                                    </td>

                                    <td class="py-4 px-4 text-center">
                                        <?php
                                            // Cari kolom keluhan dari beberapa kemungkinan nama
                                            $keluhan = '-';
                                            foreach (['keluhan', 'keluhan_pasien', 'keluhan_utama', 'catatan', 'catatan_keluhan'] as $kol) {
                                                if (!empty($row[$kol])) { $keluhan = $row[$kol]; break; }
                                            }
                                            // Data yang ditampilkan di popup "Data Pasien"
                                            $detail = [
                                                'id_antrean'    => $row['id_antrean'],
                                                'nama'          => $row['nama'] ?? '-',
                                                'nomor_antrean' => $row['nomor_antrean'] ?? '-',
                                                'no_hp'         => $row['no_hp'] ?? '-',
                                                'nik'           => $row['nik'] ?? '-',
                                                'jenis_kelamin' => $row['jenis_kelamin'] ?? '-',
                                                'tgl_lahir'     => $row['tgl_lahir'] ?? '-',
                                                'alamat'        => $row['alamat'] ?? '-',
                                                'dokter'        => $row['nama_dokter'] ?? '-',
                                                'tgl_kunjungan' => $row['tgl_kunjungan'] ?? '-',
                                                'status'        => $row['status_antrean'] ?? '-',
                                                'keluhan'       => $keluhan,
                                            ];
                                        ?>
                                        <div class="flex items-center justify-center gap-2">
                                            <?php if ($row['status_antrean'] === 'Selesai'): ?>
                                                <span class="bg-emerald-50 text-emerald-700 border border-emerald-200 px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap">✅ Selesai — sudah ditangani dokter</span>
                                            <?php elseif ($row['status_antrean'] === 'Batal'): ?>
                                                <span class="bg-red-50 text-red-700 border border-red-200 px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap">❌ Dibatalkan</span>
                                            <?php elseif ($row['status_antrean'] === 'Dipanggil'): ?>
                                                <span class="bg-cyan-50 text-cyan-700 border border-cyan-200 px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap">📢 Sedang dipanggil dokter</span>
                                            <?php else: ?>
                                            <button onclick="ubahStatus(<?= $row['id_antrean']; ?>, 'Check-in')" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl text-xs font-semibold transition shadow-2xs whitespace-nowrap">
                                                📥 Check-in
                                            </button>
                                            <?php endif; ?>
                                            <button type="button"
                                                    data-pasien="<?= htmlspecialchars(json_encode($detail, JSON_UNESCAPED_UNICODE), ENT_QUOTES); ?>"
                                                    onclick="bukaDataPasien(this)"
                                                    class="bg-cyan-50 hover:bg-cyan-100 text-cyan-800 border border-cyan-200 px-4 py-2 rounded-xl text-xs font-semibold transition shadow-2xs whitespace-nowrap">
                                                👤 Data Pasien
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="py-12 text-center text-slate-400 text-sm">
                                        Tidak ada antrean terdaftar untuk poli ini pada tanggal tersebut.
                                    </td>
                                </tr>
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

            <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-between gap-3">
                <!-- Khusus petugas: batalkan pasien yang tidak jadi berobat -->
                <button type="button" id="mp-btn-batal" onclick="batalkanPasien()" class="hidden bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-xl text-xs font-bold transition shadow-2xs">Batal</button>
                <button type="button" onclick="tutupDataPasien()" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-5 py-2 rounded-xl text-xs font-semibold transition">Tutup</button>
            </div>
        </div>
    </div>

    <!-- SCRIPT DROPDOWN, MODAL & AJAX STATUS -->
    <script>
        function bukaDataPasien(btn) {
            const data = JSON.parse(btn.getAttribute('data-pasien'));
            ['nomor', 'nama', 'no_hp', 'nik', 'jenis_kelamin', 'tgl_lahir', 'alamat', 'dokter', 'tgl_kunjungan', 'status', 'keluhan'].forEach(function (k) {
                const el = document.getElementById('mp-' + k);
                const val = (k === 'nomor') ? data.nomor_antrean : data[k];
                if (el) el.textContent = (val !== null && val !== '') ? val : '-';
            });
            // Tombol Batal hanya muncul untuk pasien yang belum dipanggil/selesai/batal
            const btnBatal = document.getElementById('mp-btn-batal');
            btnBatal.dataset.id = data.id_antrean;
            btnBatal.dataset.nama = data.nama;
            btnBatal.classList.toggle('hidden', ['Menunggu', 'Check-in', 'Lewati'].indexOf(data.status) === -1);
            document.getElementById('modal-pasien').classList.remove('hidden');
        }

        function batalkanPasien() {
            const btn = document.getElementById('mp-btn-batal');
            if (!confirm('Batalkan antrean ' + btn.dataset.nama + '?\nGunakan ini jika pasien batal berobat.')) return;
            $.ajax({
                url: 'update_status.php',
                type: 'POST',
                data: { id_antrean: btn.dataset.id, status: 'Batal' },
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        tutupDataPasien();
                        segarkan(true);
                    } else {
                        alert('Gagal membatalkan: ' + response.message);
                    }
                },
                error: function () { alert('Terjadi kesalahan koneksi ke server.'); }
            });
        }

        function tutupDataPasien() {
            document.getElementById('modal-pasien').classList.add('hidden');
        }

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') tutupDataPasien();
        });

        // Sinkron dengan portal dokter: statistik & tabel diperbarui otomatis tiap 15 detik
        async function segarkan(paksa) {
            if (!paksa && (document.hidden || !document.getElementById('modal-pasien').classList.contains('hidden'))) return;
            try {
                const r = await fetch(location.href, { credentials: 'same-origin', cache: 'no-store' });
                const doc = new DOMParser().parseFromString(await r.text(), 'text/html');
                const kartuBaru = doc.getElementById('kartu-statistik');
                const tabelBaru = doc.getElementById('tbody-antrean');
                if (kartuBaru && tabelBaru) {
                    document.getElementById('kartu-statistik').innerHTML = kartuBaru.innerHTML;
                    document.getElementById('tbody-antrean').innerHTML = tabelBaru.innerHTML;
                }
            } catch (e) {}
        }
        setInterval(segarkan, 15000);

        function ubahStatus(idAntrean, statusBaru) {
            $.ajax({
                url: 'update_status.php',
                type: 'POST',
                data: { id_antrean: idAntrean, status: statusBaru },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        segarkan(true);
                    } else {
                        alert('Gagal memperbarui status: ' + response.message);
                    }
                },
                error: function() {
                    alert('Terjadi kesalahan koneksi ke server.');
                }
            });
        }
    </script>
</body>
</html>