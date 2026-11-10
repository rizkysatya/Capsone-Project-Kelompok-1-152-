<?php
// config/db_config.example.php
// SALIN file ini menjadi config/db_config.php lalu isi sesuai server Anda.
// db_config.php sengaja TIDAK diupload ke GitHub (lihat .gitignore).
// SATU-SATUNYA file yang perlu diubah saat pindah ke hosting.
// Otomatis memakai pengaturan XAMPP saat dibuka lewat localhost,
// dan pengaturan InfinityFree saat dibuka lewat domain hosting.

date_default_timezone_set('Asia/Jakarta'); // agar "hari ini" sesuai WIB di server hosting

$server = $_SERVER['SERVER_NAME'] ?? 'localhost';

if (in_array($server, ['localhost', '127.0.0.1', '::1'], true)) {
    // === LOCALHOST (XAMPP) ===
    return [
        'host' => 'localhost',
        'name' => 'antrian_klinikrf',
        'user' => 'root',
        'pass' => '',
    ];
}

// === INFINITYFREE ===
return [
    'host' => 'ISI_MYSQL_HOSTNAME',   // MySQL Hostname
    'name' => 'ISI_NAMA_DATABASE',       // MySQL DB Name
    'user' => 'ISI_USERNAME_DB',              // MySQL User Name
    'pass' => 'ISI_PASSWORD_DB',                // password akun hosting
];