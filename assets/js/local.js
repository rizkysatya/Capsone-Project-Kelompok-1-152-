// assets/js/local.js

document.addEventListener("DOMContentLoaded", function() {
    // Menyesuaikan atribut root atau helper halaman jika diperlukan
    const body = document.body;
    const pageType = body.getAttribute("data-page");
    
    if (pageType) {
        console.log("Klinik Ratu Farma - Halaman Aktif: " + pageType);
    }
});