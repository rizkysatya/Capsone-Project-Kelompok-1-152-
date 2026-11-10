<?php
require_once '../config/koneksi.php';
 
/** @var PDO $pdo */
?>
 
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Display Antrean TV - Klinik Ratu Farma</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Memastikan tidak ada scrollbar & pas 100% tinggi layar */
        html, body {
            height: 100vh;
            width: 100vw;
            margin: 0;
            padding: 0;
            overflow: hidden;
        }
        /* ===== MODE TV: tampilan asli dipertahankan, hanya diskalakan memenuhi layar TV ===== */
        html { background: #e2e8f0; }
        body { transform-origin: 0 0; }
        body.idle, body.idle * { cursor: none !important; }
    </style>
</head>
<body class="bg-slate-200 h-screen w-screen flex flex-col justify-between p-2.5 box-border font-sans select-none">
 
    <!-- HEADER LAYAR -->
    <header class="bg-[#0d5975] text-white px-5 py-2.5 rounded-2xl shadow-lg flex justify-between items-center shrink-0">
        <div class="flex items-center gap-3">
            <!-- Panggil Logo Gambar -->
            <div class="bg-white p-1 rounded-xl shadow-inner flex items-center justify-center">
                <img src="../assets/img/logo-ratu-farma.jpeg" alt="Logo Klinik Ratu Farma" class="h-9 w-auto object-contain">
            </div>
            <div>
                <h1 class="text-xl font-extrabold tracking-wide uppercase leading-tight">KLINIK RATU FARMA</h1>
                <p class="text-[11px] text-cyan-100 font-medium">Display Antrean Realtime Pelayanan Poli</p>
            </div>
        </div>
 
        <div class="flex items-center gap-3">
            <!-- Jam Realtime -->
            <div class="bg-[#094156] px-4 py-1.5 rounded-xl border border-cyan-500/30 shadow-inner">
                <span id="jam-realtime" class="text-2xl font-black tracking-widest text-white">00.00.00</span>
            </div>
 
            <!-- Tombol "X" Kembali ke index.html -->
            <a href="../index.html" class="bg-red-500/90 hover:bg-red-600 text-white p-2 rounded-xl transition-all shadow-md flex items-center justify-center cursor-pointer" title="Kembali ke Beranda">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </a>
        </div>
    </header>
 
    <!-- CONTAINER GRID POLI (4 Kolom x 3 Baris - min-h-0 agar tidak terpotong) -->
    <main class="grid grid-cols-4 grid-rows-3 gap-2.5 my-2.5 flex-1 min-h-0">
        <!-- Template Item Poli akan di-generate via JavaScript -->
        <div id="grid-container" class="contents"></div>
    </main>
 
    <!-- FOOTER RUNNING TEXT / MARQUEE -->
    <footer class="bg-[#0d5975] text-white px-5 py-2 rounded-xl shadow-lg flex justify-between items-center text-xs font-semibold shrink-0">
        <div class="w-3/4 overflow-hidden truncate">
            <p class="animate-pulse">Budayakan antri demi kenyamanan bersama • Harap perhatikan nomor antrean Anda</p>
        </div>
        <div class="text-right text-[11px] text-cyan-200">
            Apotek & Praktek Bersama Dokter Spesialis
        </div>
    </footer>
 
    <!-- JAVASCRIPT FETCH DATA & REALTIME -->
    <script>
        // ===== MODE TV: bentuk proporsional 16:9, diskalakan pas ke layar TV tanpa gepeng/melar =====
        const LEBAR_DESAIN = 1600;
        const TINGGI_DESAIN = 900;
        function fitTV() {
            const skala = Math.min(window.innerWidth / LEBAR_DESAIN, window.innerHeight / TINGGI_DESAIN);
            const b = document.body;
            b.style.position = 'absolute';
            b.style.width = LEBAR_DESAIN + 'px';
            b.style.height = TINGGI_DESAIN + 'px';
            b.style.left = ((window.innerWidth - LEBAR_DESAIN * skala) / 2) + 'px';
            b.style.top = ((window.innerHeight - TINGGI_DESAIN * skala) / 2) + 'px';
            b.style.transform = 'scale(' + skala + ')';
        }
        window.addEventListener('resize', fitTV);
        fitTV();
 
        // Layar penuh: klik di mana saja (selain tombol X) atau tekan F
        function masukFullscreen() {
            const el = document.documentElement;
            if (!document.fullscreenElement && el.requestFullscreen) { el.requestFullscreen().catch(() => {}); }
        }
        document.addEventListener('click', (e) => { if (!e.target.closest('a')) masukFullscreen(); });
        document.addEventListener('keydown', (e) => { if (e.key === 'f' || e.key === 'F' || e.key === 'Enter') masukFullscreen(); });
 
        // Cegah layar TV/browser mati otomatis
        let wakeLock = null;
        async function jagaLayar() {
            try { if ('wakeLock' in navigator) { wakeLock = await navigator.wakeLock.request('screen'); } } catch (e) {}
        }
        document.addEventListener('visibilitychange', () => { if (document.visibilityState === 'visible') jagaLayar(); });
        jagaLayar();
 
        // Sembunyikan kursor mouse jika tidak digerakkan 3 detik
        let idleTimer;
        function resetIdle() { document.body.classList.remove('idle'); clearTimeout(idleTimer); idleTimer = setTimeout(() => document.body.classList.add('idle'), 3000); }
        document.addEventListener('mousemove', resetIdle);
        resetIdle();
 
        // Update Jam Digital
        function updateJam() {
            const now = new Date();
            const jam = String(now.getHours()).padStart(2, '0');
            const menit = String(now.getMinutes()).padStart(2, '0');
            const detik = String(now.getSeconds()).padStart(2, '0');
            document.getElementById('jam-realtime').innerText = `${jam}.${menit}.${detik}`;
        }
        setInterval(updateJam, 1000);
        updateJam();
 
        // Load Data Antrean dari API get_display_data.php
        async function loadDisplayData() {
            try {
                const response = await fetch('get_display_data.php?t=' + Date.now(), { cache: 'no-store' });
                const data = await response.json();
                
                const container = document.getElementById('grid-container');
                container.innerHTML = '';
 
                data.forEach(item => {
                    const antreanAda = item.antrean && item.antrean.length > 0;
                    const antreanUtama = antreanAda ? item.antrean[0] : null;
 
                    const htmlCard = `
                        <div class="bg-white rounded-2xl shadow border border-slate-300 flex flex-col overflow-hidden h-full">
                            <!-- Header Card Poli -->
                            <div class="bg-slate-100 px-3 py-2 border-b border-slate-200 text-center shrink-0">
                                <h2 class="text-[15px] font-black text-slate-800 uppercase tracking-tight truncate leading-tight">${item.nama_poli}</h2>
                                <span class="inline-block mt-1 bg-blue-100 text-blue-700 text-xs font-bold px-2.5 py-0.5 rounded-full">
                                    KODE ${item.kode_poli}
                                </span>
                            </div>

                            <!-- Body Card Antrean: hanya nomor, dibuat besar -->
                            <div class="flex-1 flex items-center justify-center px-2 text-center min-h-0">
                                ${antreanUtama ? `
                                    <span class="font-black text-[#0d5975] leading-none whitespace-nowrap" style="font-size:88px; letter-spacing:0.02em;">${antreanUtama.nomor}</span>
                                ` : `
                                    <div class="flex flex-col items-center text-slate-300">
                                        <span class="font-black leading-none whitespace-nowrap" style="font-size:88px; letter-spacing:0.02em;">-</span>
                                        <span class="text-sm font-semibold text-slate-400 mt-1">Belum ada antrean</span>
                                    </div>
                                `}
                            </div>

                            <!-- Footer Card Dokter -->
                            <div class="bg-slate-50 px-3 py-2 border-t border-slate-100 text-center text-[13px] text-slate-700 font-bold truncate shrink-0">
                                👨‍⚕️ ${item.nama_dokter}
                            </div>
                        </div>
                    `;
                    container.insertAdjacentHTML('beforeend', htmlCard);
                });
 
            } catch (error) {
                console.error("Gagal memuat data antrean:", error);
            }
        }
 
        // Refresh Data setiap 2 Detik agar pasien yang baru check-in cepat terbaca
        setInterval(loadDisplayData, 2000);
        loadDisplayData();
    </script>
</body>
</html>