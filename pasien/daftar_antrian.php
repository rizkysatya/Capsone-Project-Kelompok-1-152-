<?php
// daftar_antrian.php
session_start();

if (!isset($_SESSION['id_pasien'])) {
    $_SESSION['error'] = "Silakan login terlebih dahulu untuk mengambil nomor antrean.";
    header("Location: login.php");
    exit;
}

$cfg = require __DIR__ . '/../config/db_config.php';
$host = $cfg['host'];
$dbname = $cfg['name'];
$username_db = $cfg['user'];
$password_db = $cfg['pass'];

$error = '';
$success = '';
$id_pendaftaran_baru = null;

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username_db, $password_db);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    require_once __DIR__ . '/../config/migrasi.php';
    pastikanKolomBaru($pdo);

    $stmt = $pdo->prepare("SELECT * FROM pasien WHERE id_pasien = ?");
    $stmt->execute([$_SESSION['id_pasien']]);
    $pasien = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$pasien) {
        session_destroy();
        header("Location: login.php");
        exit;
    }
} catch (PDOException $e) {
    $error = "Gagal terhubung ke database: " . $e->getMessage();
}

// Proses saat form dikirim
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_poli           = trim($_POST['id_poli'] ?? '');
    $id_dokter         = trim($_POST['id_dokter'] ?? '');
    $tanggal_kunjungan = trim($_POST['tanggal_kunjungan'] ?? '');
    $keluhan           = trim($_POST['keluhan'] ?? '');
    $jenis_kelamin_form = trim($_POST['jenis_kelamin'] ?? '');

    if (empty($id_poli) || empty($id_dokter) || empty($tanggal_kunjungan)) {
        $error = "Tanggal, Poli, dan Dokter wajib dipilih!";
    } elseif (empty($pasien['jenis_kelamin']) && !in_array($jenis_kelamin_form, ['Laki-laki', 'Perempuan'], true)) {
        $error = "Jenis kelamin wajib dipilih!";
    } else {
        require_once __DIR__ . '/../config/jadwal_helper.php';
        $nama_hari = namaHariDariTanggal($tanggal_kunjungan);
        $jadwal = $nama_hari ? jadwalDokterTunggal($pdo, $id_dokter, $tanggal_kunjungan) : null;

        if (!$jadwal) {
            $error = "Mohon maaf, dokter yang Anda pilih tidak praktik pada hari " . ($nama_hari ?: 'tersebut') . ". Silakan pilih tanggal lain.";
        } else {
            $id_jadwal = $jadwal['id_jadwal'];

            $jumlahTerdaftar = jumlahTerdaftarAktif($pdo, $id_jadwal, $tanggal_kunjungan);
            $kuotaMaksimal = kuotaEfektif($jadwal);

            if ($jumlahTerdaftar >= $kuotaMaksimal) {
                $error = "Mohon maaf, kuota antrean untuk dokter ini pada tanggal $tanggal_kunjungan sudah penuh ($jumlahTerdaftar/$kuotaMaksimal). Silakan pilih tanggal lain.";
            } else {
                // Pasien bebas mendaftar ke poli mana pun, termasuk poli/tanggal yang antreannya masih aktif (hanya dibatasi kuota dokter).
                {
                    $no_urut = nomorUrutBerikutnya($pdo, $id_jadwal, $tanggal_kunjungan);
                    require_once __DIR__ . '/../config/kode_poli.php';
                    $kode_poli = kodePoliDariDokter($pdo, $id_dokter);
                    $nomor_antrean_str = $kode_poli . "-" . sprintf("%03d", $no_urut);

                    $stmtInsPendaftaran = $pdo->prepare("
                        INSERT INTO pendaftaran (id_pasien, id_jadwal, tgl_daftar, tgl_kunjungan, sumber_daftar, bukti_pendaftaran, keluhan) 
                        VALUES (?, ?, NOW(), ?, 'Online', ?, ?)
                    ");
                    $stmtInsPendaftaran->execute([$pasien['id_pasien'], $id_jadwal, $tanggal_kunjungan, $nomor_antrean_str, ($keluhan !== '' ? $keluhan : null)]);

                    // Simpan jenis kelamin jika sebelumnya belum terisi pada akun pasien
                    if (empty($pasien['jenis_kelamin'])) {
                        $pdo->prepare("UPDATE pasien SET jenis_kelamin = ? WHERE id_pasien = ?")->execute([$jenis_kelamin_form, $pasien['id_pasien']]);
                    }
                    $id_pendaftaran_baru = $pdo->lastInsertId();

                    $id_petugas_default = 1; 
                    $stmtInsAntrean = $pdo->prepare("
                        INSERT INTO antrean (id_pendaftaran, id_petugas, nomor_antrean, status_antrean, id_dokter) 
                        VALUES (?, ?, ?, 'Menunggu', ?)
                    ");
                    $stmtInsAntrean->execute([$id_pendaftaran_baru, $id_petugas_default, $nomor_antrean_str, $id_dokter]);

                    // Redirect otomatis ke halaman cetak.php dengan membawa id_pendaftaran
                    header("Location: cetak.php?id=" . $id_pendaftaran_baru);
                    exit;
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Antrean Online - Klinik Ratu Farma Aceh</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-gradient-to-b from-[#e6fcf5] to-[#f0fdf4] text-gray-800 font-sans antialiased min-h-screen flex flex-col justify-between">

    <div class="flex flex-col min-h-screen">
        
    <!-- MEMANGGIL HEADER PASIEN -->
        <?php include 'header_pasien.php'; ?>
        
        <main class="flex-1 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-xl w-full bg-white border border-[#d1fae5] rounded-2xl shadow-lg p-8 space-y-6">
                
                <div class="text-center space-y-2">
                    <div class="mx-auto w-12 h-12 rounded-full bg-sky-50 flex items-center justify-center text-xl">🏥</div>
                    <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Ambil Nomor Antrean Online</h1>
                    <p class="text-xs text-gray-500">Klinik Ratu Farma Aceh • Pilih tanggal kunjungan terlebih dahulu.</p>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="bg-red-50 text-red-700 p-3 rounded-lg text-xs text-center font-medium border border-red-200">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form action="" method="POST" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Pasien</label>
                        <input type="text" value="<?= htmlspecialchars($pasien['nama']) ?>" disabled class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg bg-gray-100 cursor-not-allowed">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Jenis Kelamin</label>
<?php if (!empty($pasien['jenis_kelamin'])): ?>
                        <input type="text" value="<?= htmlspecialchars($pasien['jenis_kelamin']) ?>" disabled class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg bg-gray-100 cursor-not-allowed">
<?php else: ?>
                        <select name="jenis_kelamin" required class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0d5975]">
                            <option value="">-- Pilih Jenis Kelamin --</option>
                            <option value="Laki-laki">Laki-laki</option>
                            <option value="Perempuan">Perempuan</option>
                        </select>
<?php endif; ?>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Tanggal Kunjungan</label>
                        <input type="date" id="tanggal_kunjungan" name="tanggal_kunjungan" min="<?= date('Y-m-d') ?>" required class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0b4f6c]">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Pilih Poli Tujuan</label>
                        <select id="id_poli" name="id_poli" disabled required class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg bg-gray-100 focus:outline-none focus:ring-2 focus:ring-[#0b4f6c]">
                            <option value="">-- Pilih Tanggal Kunjungan Terlebih Dahulu --</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Pilih Dokter</label>
                        <select id="id_dokter" name="id_dokter" disabled required class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg bg-gray-100 focus:outline-none focus:ring-2 focus:ring-[#0b4f6c]">
                            <option value="">-- Pilih Poli Terlebih Dahulu --</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Keluhan Singkat (Opsional)</label>
                        <textarea id="keluhan" name="keluhan" rows="2" placeholder="Tuliskan keluhan atau gejala yang dirasakan" class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0b4f6c]"></textarea>
                    </div>

                    <button type="submit" class="w-full bg-[#0b4f6c] hover:bg-[#083d53] text-white font-semibold py-2.5 rounded-lg text-sm transition shadow-sm mt-2">
                        Cek Kuota &amp; Ambil Antrean
                    </button>
                </form>

            </div>
        </main>
    </div>

    <script>
        $(document).ready(function() {
            $('#tanggal_kunjungan').change(function() {
                var tanggal = $(this).val();
                
                $('#id_poli').html('<option value="">-- Memuat Poli Buka... --</option>').prop('disabled', true).addClass('bg-gray-100');
                $('#id_dokter').html('<option value="">-- Pilih Poli Terlebih Dahulu --</option>').prop('disabled', true).addClass('bg-gray-100');

                if (tanggal) {
                    $.ajax({
                        url: 'get_poli_by_date.php',
                        type: 'POST',
                        data: {tanggal: tanggal},
                        success: function(response) {
                            $('#id_poli').html(response).prop('disabled', false).removeClass('bg-gray-100');
                        }
                    });
                } else {
                    $('#id_poli').html('<option value="">-- Pilih Tanggal Kunjungan Terlebih Dahulu --</option>');
                }
            });

            $('#id_poli').change(function() {
                var idPoli = $(this).val();
                var tanggal = $('#tanggal_kunjungan').val();
                
                $('#id_dokter').html('<option value="">-- Memuat Dokter... --</option>').prop('disabled', true).addClass('bg-gray-100');

                if (idPoli && tanggal) {
                    $.ajax({
                        url: 'get_dokter_by_date.php',
                        type: 'POST',
                        data: {id_poli: idPoli, tanggal: tanggal},
                        success: function(response) {
                            $('#id_dokter').html(response).prop('disabled', false).removeClass('bg-gray-100');
                        }
                    });
                } else {
                    $('#id_dokter').html('<option value="">-- Pilih Poli Terlebih Dahulu --</option>');
                }
            });
        });
    </script>

    <footer class="bg-white border-t py-6 text-center text-xs text-gray-500">
        &copy; 2026 Klinik Ratu Farma Aceh. All rights reserved.
    </footer>
</body>
</html>