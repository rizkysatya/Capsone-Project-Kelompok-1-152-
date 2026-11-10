<?php
// get_dokter.php
if (isset($_POST['id_poli'])) {
    $id_poli = $_POST['id_poli'];

    $cfg = require __DIR__ . '/../config/db_config.php';
    $host = $cfg['host'];
    $dbname = $cfg['name'];
    $username_db = $cfg['user'];
    $password_db = $cfg['pass'];

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username_db, $password_db);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->prepare("SELECT * FROM dokter WHERE id_poli = ?");
        $stmt->execute([$id_poli]);
        $dokters = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo '<option value="">-- Pilih Dokter --</option>';
        if (count($dokters) > 0) {
            foreach ($dokters as $d) {
                echo '<option value="' . $d['id_dokter'] . '">' . htmlspecialchars($d['nama_dokter']) . ' (' . htmlspecialchars($d['spesialisasi']) . ')</option>';
            }
        } else {
            echo '<option value="">Tidak ada dokter tersedia di poli ini</option>';
        }
    } catch (PDOException $e) {
        echo '<option value="">Gagal memuat data dokter</option>';
    }
}