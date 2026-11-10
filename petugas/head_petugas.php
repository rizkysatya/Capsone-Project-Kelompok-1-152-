<?php
// head_petugas.php
// Dipakai SEMUA halaman petugas di dalam <head>, supaya library, CSS, dan font sama.
// Halaman di subfolder set dulu:  $petugas_root = '../';
$petugas_root = $petugas_root ?? '';
$assets_petugas = $petugas_root . '../assets';
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= $assets_petugas; ?>/css/dashboard-petugas.css">
<style>
    /* Satu font untuk seluruh portal petugas (termasuk input, tombol, tabel) */
    html, body, button, input, select, textarea, table {
        font-family: 'Inter', 'Segoe UI', system-ui, -apple-system, sans-serif !important;
    }
    body { background-color: #f8fafc; }
</style>
