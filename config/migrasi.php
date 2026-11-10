<?php
// config/migrasi.php
// Menambahkan kolom baru secara otomatis (sekali saja) jika belum ada di database.
function pastikanKolomBaru(PDO $pdo)
{
    // Samakan zona waktu MySQL dengan WIB (server hosting biasanya UTC)
    try { $pdo->exec("SET time_zone = '+07:00'"); } catch (Exception $e) {}

    static $sudah = false;
    if ($sudah) { return; }
    $sudah = true;

    $perlu = [
        ['pasien',      'jenis_kelamin', "ALTER TABLE `pasien` ADD COLUMN `jenis_kelamin` ENUM('Laki-laki','Perempuan') DEFAULT NULL AFTER `tgl_lahir`"],
        ['pendaftaran', 'keluhan',       "ALTER TABLE `pendaftaran` ADD COLUMN `keluhan` TEXT DEFAULT NULL"],
    ];

    foreach ($perlu as [$tabel, $kolom, $sql]) {
        $cek = $pdo->prepare("SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?");
        $cek->execute([$tabel, $kolom]);
        if ((int)$cek->fetchColumn() === 0) {
            $pdo->exec($sql);
        }
    }
}
