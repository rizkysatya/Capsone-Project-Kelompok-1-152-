<?php
// pasien_offline.php
session_start();
require_once '../config/koneksi.php';

if (!isset($_SESSION['id_petugas'])) {
    header("Location: login.php");
    exit();
}

$error = '';

try {
    // Ambil daftar poli untuk dropdown form
    $stmtPoli = $pdo->query("SELECT * FROM poli ORDER BY nama_poli ASC");
    $daftar_poli = $stmtPoli->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error = "Gagal memuat data: " . $e->getMessage();
}

// Proses form saat petugas mendaftarkan pasien offline
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama               = trim($_POST['nama'] ?? '');
    $nik                = trim($_POST['nik'] ?? '');
    $no_hp              = trim($_POST['no_hp'] ?? '');
    $jenis_kelamin      = trim($_POST['jenis_kelamin'] ?? '');
    $alamat             = trim($_POST['alamat'] ?? '');
    $id_poli            = trim($_POST['id_poli'] ?? '');
    $id_dokter          = trim($_POST['id_dokter'] ?? '');
    $tanggal_kunjungan  = date('Y-m-d'); // Otomatis hari ini untuk pasien offline
    $keluhan            = trim($_POST['keluhan'] ?? '');

    if (empty($nama) || empty($nik) || empty($id_poli) || empty($id_dokter) || !in_array($jenis_kelamin, ['Laki-laki', 'Perempuan'], true)) {
        $error = "Nama, NIK, Jenis Kelamin, Poli, dan Dokter wajib diisi!";
    } else {
        try {
            $pdo->beginTransaction();

            // 1. Cek apakah pasien dengan NIK tersebut sudah terdaftar di tabel pasien
            $stmtCekPasien = $pdo->prepare("SELECT id_pasien FROM pasien WHERE nik = ?");
            $stmtCekPasien->execute([$nik]);
            $pasien = $stmtCekPasien->fetch(PDO::FETCH_ASSOC);

            if ($pasien) {
                $id_pasien = $pasien['id_pasien'];
                // Perbarui jenis kelamin sesuai data yang diisi petugas
                $pdo->prepare("UPDATE pasien SET jenis_kelamin = ? WHERE id_pasien = ?")->execute([$jenis_kelamin, $id_pasien]);
            } else {
                // Jika belum ada, buat akun pasien baru secara otomatis (password default NIK)
                $password_default = password_hash($nik, PASSWORD_DEFAULT);
                $stmtInsPasien = $pdo->prepare("INSERT INTO pasien (nama, nik, jenis_kelamin, no_hp, alamat, username, password) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmtInsPasien->execute([$nama, $nik, $jenis_kelamin, $no_hp, $alamat, $nik, $password_default]);
                $id_pasien = $pdo->lastInsertId();
            }

            // 2. Ambil jadwal dokter berdasarkan hari ini
            require_once __DIR__ . '/../config/jadwal_helper.php';
            $nama_hari = namaHariDariTanggal($tanggal_kunjungan);
            $jadwal = $nama_hari ? jadwalDokterTunggal($pdo, $id_dokter, $tanggal_kunjungan) : null;

            if (!$jadwal) {
                throw new Exception("Dokter yang dipilih tidak memiliki jadwal praktik pada hari ini ($nama_hari).");
            }
            $id_jadwal = $jadwal['id_jadwal'];

            // 3. Cek jumlah terdaftar untuk nomor antrean
            $stmtCekKuota = $pdo->prepare("SELECT COUNT(*) as total FROM pendaftaran WHERE id_jadwal = ? AND tgl_kunjungan = ?");
            $stmtCekKuota->execute([$id_jadwal, $tanggal_kunjungan]);
            $dataAntrean = $stmtCekKuota->fetch(PDO::FETCH_ASSOC);

            $no_urut = $dataAntrean['total'] + 1;
            require_once __DIR__ . '/../config/kode_poli.php';
            $kode_poli = kodePoliDariDokter($pdo, $id_dokter);
            $nomor_antrean_str = $kode_poli . "-" . sprintf("%03d", $no_urut);

            // 4. Masukkan ke tabel pendaftaran (sumber_daftar = 'Offline')
            $stmtInsPendaftaran = $pdo->prepare("
                INSERT INTO pendaftaran (id_pasien, id_jadwal, tgl_daftar, tgl_kunjungan, sumber_daftar, bukti_pendaftaran, keluhan) 
                VALUES (?, ?, NOW(), ?, 'Offline', ?, ?)
            ");
            $stmtInsPendaftaran->execute([$id_pasien, $id_jadwal, $tanggal_kunjungan, $nomor_antrean_str, ($keluhan !== '' ? $keluhan : null)]);
            $id_pendaftaran_baru = $pdo->lastInsertId();

            // 5. Masukkan ke tabel antrean (langsung status Check-in karena pasien sudah datang langsung)
            $stmtInsAntrean = $pdo->prepare("
                INSERT INTO antrean (id_pendaftaran, id_petugas, nomor_antrean, status_antrean, id_dokter) 
                VALUES (?, ?, ?, 'Check-in', ?)
            ");
            $stmtInsAntrean->execute([$id_pendaftaran_baru, $_SESSION['id_petugas'], $nomor_antrean_str, $id_dokter]);

            $pdo->commit();

            // Langsung arahkan ke halaman cetak struk
            header("Location: cetak.php?id=" . $id_pendaftaran_baru);
            exit();

        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Gagal memproses pendaftaran: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php include __DIR__ . '/head_petugas.php'; ?>
    <title>Pasien Offline - Klinik Ratu Farma</title>
</head>
<body class="min-h-screen flex">

    <!-- SIDEBAR KIRI -->
    <?php $halaman_aktif = 'offline'; include __DIR__ . '/sidebar_petugas.php'; ?>

    <!-- KONTEN UTAMA KANAN -->
    <div class="flex-1 flex flex-col min-w-0">
        
        <?php
        $judul_halaman = 'Pendaftaran Pasien Offline';
        $sub_judul = 'Pendaftaran pasien Langsung di klinik';
        include __DIR__ . '/topbar_petugas.php';
        ?>

        <main class="flex-1 p-6 sm:p-10 space-y-8">
            
            <div class="bg-white border border-slate-200 rounded-3xl p-6 sm:p-8 max-w-2xl mx-auto shadow-xs space-y-6">
                
                <div>
                    <h2 class="text-xl font-black text-slate-800">Form Pendaftaran Offline</h2>
                    <p class="text-xs text-slate-400">Masukkan data pasien yang datang langsung ke klinik untuk kunjungan hari ini (<?= date('d-m-Y'); ?>).</p>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="bg-red-50 text-red-700 p-4 rounded-xl text-xs font-medium border border-red-200">
                        <?= htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">NIK Pasien</label>
                        <input type="text" name="nik" required placeholder="Nomor Induk Kependudukan (NIK)" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-cyan-600 bg-slate-50">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap Pasien</label>
                        <input type="text" name="nama" required placeholder="Nama lengkap sesuai KTP" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-cyan-600 bg-slate-50">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Jenis Kelamin</label>
                        <select name="jenis_kelamin" required class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-cyan-600 bg-slate-50">
                            <option value="">-- Pilih Jenis Kelamin --</option>
                            <option value="Laki-laki">Laki-laki</option>
                            <option value="Perempuan">Perempuan</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor HP / WhatsApp</label>
                            <input type="text" name="no_hp" placeholder="Contoh: 081234567890" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-cyan-600 bg-slate-50">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Poli Tujuan</label>
                            <select id="id_poli" name="id_poli" required class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-cyan-600 bg-slate-50">
                                <option value="">-- Pilih Poli --</option>
                                <?php foreach ($daftar_poli as $p): ?>
                                    <option value="<?= $p['id_poli']; ?>"><?= htmlspecialchars($p['nama_poli']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Dokter</label>
                        <select id="id_dokter" name="id_dokter" disabled required class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-cyan-600 bg-slate-100">
                            <option value="">-- Pilih Poli Terlebih Dahulu --</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Alamat</label>
                        <textarea name="alamat" rows="2" placeholder="Alamat domisili pasien" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-cyan-600 bg-slate-50"></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Keluhan Singkat (Opsional)</label>
                        <textarea name="keluhan" rows="2" placeholder="Keluhan atau gejala penyakit" class="w-full px-3.5 py-2.5 text-xs border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-cyan-600 bg-slate-50"></textarea>
                    </div>

                    <button type="submit" class="w-full brand-gradient hover:opacity-95 text-white font-bold py-3 rounded-xl text-xs transition shadow-sm">
                        Daftarkan Pasien &amp; Cetak Struk Antrean
                    </button>
                </form>

            </div>
        </main>
    </div>

    <!-- SCRIPT AJAX DOKTER BY POLI HARI INI -->
    <script>

        $(document).ready(function() {
            $('#id_poli').change(function() {
                var idPoli = $(this).val();
                var tanggalHariIni = '<?= date('Y-m-d'); ?>';

                $('#id_dokter').html('<option value="">-- Tidak Ada Dokter Praktik Hari Ini... --</option>').prop('disabled', true).addClass('bg-slate-100');

                if (idPoli) {
                    $.ajax({
                        url: 'get_dokter_by_date.php',
                        type: 'POST',
                        data: { id_poli: idPoli, tanggal: tanggalHariIni },
                        success: function(response) {
                            $('#id_dokter').html(response).prop('disabled', false).removeClass('bg-slate-100');
                        }
                    });
                } else {
                    $('#id_dokter').html('<option value="">-- Pilih Poli Terlebih Dahulu --</option>');
                }
            });
        });
    </script>
</body>
</html>