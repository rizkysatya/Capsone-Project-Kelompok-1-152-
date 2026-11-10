<?php
header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate');
require_once '../config/koneksi.php';
require_once '../config/kode_poli.php';

// Tampilan TV mengikuti hari secara realtime: hanya pasien Check-in/Dipanggil pada tanggal hari ini yang tampil.
$tanggal_hari_ini = date('Y-m-d');
$data_display = [];

// Daftar 11 Poli Default Klinik Ratu Farma
$default_poli_list = [
    ['id_poli' => 1,  'nama_poli' => 'POLI ANAK', 'kode_poli' => 'A'],
    ['id_poli' => 2,  'nama_poli' => 'POLI SARAF', 'kode_poli' => 'B'],
    ['id_poli' => 3,  'nama_poli' => 'POLI PENYAKIT DALAM', 'kode_poli' => 'C'],
    ['id_poli' => 4,  'nama_poli' => 'POLI UROLOGI', 'kode_poli' => 'D'],
    ['id_poli' => 5,  'nama_poli' => 'POLI GIGI', 'kode_poli' => 'E'],
    ['id_poli' => 6,  'nama_poli' => 'POLI AKUPUNKTUR', 'kode_poli' => 'F'],
    ['id_poli' => 7,  'nama_poli' => 'POLI PSIKIATER', 'kode_poli' => 'G'],
    ['id_poli' => 8,  'nama_poli' => 'POLI KANDUNGAN', 'kode_poli' => 'H'],
    ['id_poli' => 9,  'nama_poli' => 'POLI THT', 'kode_poli' => 'I'],
    ['id_poli' => 10, 'nama_poli' => 'POLI PARU-PARU', 'kode_poli' => 'J'],
    ['id_poli' => 11, 'nama_poli' => 'POLI PERAWAT PSIKOLOG ANAK & REMAJA', 'kode_poli' => 'K']
];

if (isset($pdo)) {
    try {
        $stmt_poli = $pdo->query("SELECT * FROM poli ORDER BY id_poli ASC");
        $poli_db = $stmt_poli->fetchAll(PDO::FETCH_ASSOC);

        if ($poli_db && count($poli_db) > 0) {
            foreach ($poli_db as $poli) {
                $poli_id = $poli['id_poli'];
                // Kode poli mengikuti config/kode_poli.php (sama dengan nomor antrean pasien)
                $poli['kode_poli'] = kodePoliDariIdPoli($poli_id);

                // Query SQL mengambil antrean dan nama dokter bertugas
                $stmt_antrean = $pdo->prepare("
                    SELECT a.nomor_antrean, p.nama, a.status_antrean, d.nama_dokter 
                    FROM antrean a
                    JOIN pendaftaran pd ON a.id_pendaftaran = pd.id_pendaftaran
                    JOIN pasien p ON pd.id_pasien = p.id_pasien
                    JOIN jadwal j ON pd.id_jadwal = j.id_jadwal
                    JOIN dokter d ON j.id_dokter = d.id_dokter
                    WHERE d.id_poli = ? 
                      AND DATE(pd.tgl_kunjungan) = ?
                      AND a.status_antrean IN ('Dipanggil', 'Check-in')
                    ORDER BY FIELD(a.status_antrean, 'Dipanggil', 'Check-in') ASC, pd.tgl_kunjungan ASC, a.id_antrean ASC
                ");
                $stmt_antrean->execute([$poli_id, $tanggal_hari_ini]);
                $rows_antrean = $stmt_antrean->fetchAll(PDO::FETCH_ASSOC);

                $list_panggilan = [];
                $nama_dokter = 'Dokter Bertugas'; // Default jika belum ada dokter yang melayani

                foreach ($rows_antrean as $row) {
                    $kode = !empty($poli['kode_poli']) ? strtoupper($poli['kode_poli']) : 'A';
                    $angka_saja = preg_replace('/[^0-9]/', '', $row['nomor_antrean']);
                    
                    if (!empty($row['nama_dokter'])) {
                        $nama_dokter = $row['nama_dokter'];
                    }

                    $list_panggilan[] = [
                        'nomor'  => $kode . '-' . str_pad($angka_saja, 3, '0', STR_PAD_LEFT),
                        'nama'   => strtoupper($row['nama']),
                        'status' => $row['status_antrean']
                    ];
                }

                // Jika antrean kosong, coba ambil nama dokter dari tabel dokter langsung
                if ($nama_dokter === 'Dokter Bertugas') {
                    $stmt_doc = $pdo->prepare("SELECT nama_dokter FROM dokter WHERE id_poli = ? LIMIT 1");
                    $stmt_doc->execute([$poli_id]);
                    $doc_data = $stmt_doc->fetch(PDO::FETCH_ASSOC);
                    if (!empty($doc_data['nama_dokter'])) {
                        $nama_dokter = $doc_data['nama_dokter'];
                    }
                }

                $data_display[] = [
                    'id_poli'     => $poli['id_poli'],
                    'kode_poli'   => !empty($poli['kode_poli']) ? strtoupper($poli['kode_poli']) : 'A',
                    'nama_poli'   => strtoupper($poli['nama_poli']),
                    'nama_dokter' => $nama_dokter,
                    'antrean'     => $list_panggilan
                ];
            }
        }
    } catch (PDOException $e) {
    // Buka get_display_data.php?debug=1 untuk melihat pesan error jika tampilan kosong
    if (isset($_GET['debug'])) { echo json_encode(['error' => $e->getMessage()]); exit(); }
}

// Urutkan A sampai K agar susunan TV tetap sama seperti desain
usort($data_display, function ($a, $b) { return strcmp($a['kode_poli'], $b['kode_poli']); });
}

if (empty($data_display)) {
    foreach ($default_poli_list as $dp) {
        $data_display[] = [
            'id_poli'     => $dp['id_poli'],
            'kode_poli'   => $dp['kode_poli'],
            'nama_poli'   => $dp['nama_poli'],
            'nama_dokter' => 'Dokter Bertugas',
            'antrean'     => []
        ];
    }
}

echo json_encode($data_display);
?>