function panggilAntrean(idAntrean) {
    fetch('update_status.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id_antrean=${idAntrean}&status=Dipanggil`
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            alert('Antrean berhasil dipanggil!');
            location.reload();
        }
    });
}

function selesaiAntrean(idAntrean) {
    fetch('update_status.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id_antrean=${idAntrean}&status=Selesai`
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            alert('Antrean selesai!');
            location.reload();
        }
    });
}