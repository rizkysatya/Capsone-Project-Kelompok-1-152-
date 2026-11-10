<!-- header_pasien.php -->
<header class="bg-white border-b border-slate-200 sticky top-0 z-50 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
        
        <!-- Logo Menggunakan Gambar (Image) -->
        <a href="../index.html" class="flex items-center group focus:outline-none">
            <img src="../assets/img/logo-ratu-farma.jpeg" alt="Logo Ratu Farma" class="h-20 w-auto object-contain">
        </a>

        <!-- Menu Navigasi Kanan -->
        <nav class="flex items-center space-x-7 text-sm font-medium text-slate-700">
            <a href="../index.html" class="hover:text-[#0d5975] transition font-semibold">Beranda / Poli</a>
            

            <!-- DROPDOWN PORTAL PETUGAS & DOKTER -->
            <div class="relative" id="dropdown-container">
                <button id="dropdown-btn" type="button" class="flex items-center space-x-1 hover:text-[#0d5975] transition font-semibold focus:outline-none cursor-pointer">
                    <span>Portal Petugas</span>
                    <svg class="w-4 h-4 transition-transform duration-200" id="dropdown-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>

                <!-- Isi Dropdown -->
                <div id="dropdown-menu" class="hidden absolute right-0 mt-3 w-48 bg-white border border-slate-100 rounded-xl shadow-xl py-2 z-50">
                    <a href="login.php" class="block px-4 py-2.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-[#0d5975] transition">
                        🔐 Login Petugas
                    </a>
                    <div class="border-t border-slate-100 my-1"></div>
                    <a href="../dokter/login_dokter.php" class="block px-4 py-2.5 text-xs font-semibold text-slate-700 hover:bg-[#0d5975] hover:text-white transition">
                        🩺 Login Dokter
                    </a>
                </div>
            </div>

<span class="bg-[#0d5975] text-white px-5 py-2.5 rounded-xl text-xs font-bold shadow-sm inline-block">
    Sistem Antrean
</span>
        </nav>
    </div>
</header>

<!-- Script Interaktif Dropdown -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const dropdownBtn = document.getElementById('dropdown-btn');
        const dropdownMenu = document.getElementById('dropdown-menu');
        const dropdownIcon = document.getElementById('dropdown-icon');

        if (dropdownBtn && dropdownMenu && dropdownIcon) {
            dropdownBtn.addEventListener('click', function(event) {
                event.stopPropagation();
                dropdownMenu.classList.toggle('hidden');
                dropdownIcon.classList.toggle('rotate-180');
            });

            dropdownMenu.addEventListener('click', function(event) {
                event.stopPropagation();
            });

            window.addEventListener('click', function() {
                if (!dropdownMenu.classList.contains('hidden')) {
                    dropdownMenu.classList.add('hidden');
                    dropdownIcon.classList.remove('rotate-180');
                }
            });
        }
    });
</script>