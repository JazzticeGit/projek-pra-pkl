function previewProduk() {
    const select = document.getElementById('produkSelect');
    const selected = select.options[select.selectedIndex];
    const nama = selected.dataset.nama || '';
    const harga = selected.dataset.harga || 0;
    const image = selected.dataset.image || '';

    document.getElementById('preview-nama').textContent = nama;
    document.getElementById('preview-gambar').src = `../../${image}`;
    document.getElementById('preview-harga-asli').textContent = `Harga Asli: Rp ${parseInt(harga).toLocaleString('id-ID')}`;
    
    const persenInput = document.querySelector('input[name="persen_diskon"]');
    if (persenInput.value) updateHarga();
}

function updateHarga() {
    const select = document.getElementById('produkSelect');
    const selected = select.options[select.selectedIndex];
    const harga = parseFloat(selected.dataset.harga || 0);
    const persen = parseFloat(document.querySelector('input[name="persen_diskon"]').value || 0);

    if (!isNaN(harga) && !isNaN(persen)) {
        const hargaDiskon = harga - (harga * persen / 100);
        document.getElementById('preview-harga-diskon').textContent = `Harga Diskon: Rp ${hargaDiskon.toLocaleString('id-ID')}`;
    }
}

function filterCards(status) {
    const cards = document.querySelectorAll('.card');
    document.querySelectorAll('.filter-tab').forEach(btn => btn.classList.remove('active'));
    document.querySelector(`.filter-tab[onclick*="${status}"]`).classList.add('active');

    cards.forEach(card => {
        const cardStatus = card.getAttribute('data-status');
        if (status === 'all' || status === cardStatus) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

function updateCountdown() {
    const timers = document.querySelectorAll('.countdown-timer');
    const now = new Date().getTime();

    timers.forEach(timer => {
        const start = new Date(timer.dataset.startTime).getTime();
        const end = new Date(timer.dataset.endTime).getTime();

        let diff;
        let label;

        if (now < start) {
            diff = start - now;
            label = 'Mulai dalam: ';
        } else if (now >= start && now <= end) {
            diff = end - now;
            label = 'Berakhir dalam: ';
        } else {
            timer.textContent = 'Diskon sudah berakhir';
            return;
        }

        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
        const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((diff % (1000 * 60)) / 1000);

        timer.textContent = `${label}${days}h ${hours}j ${minutes}m ${seconds}d`;
    });
}

// Jalankan countdown terus menerus
setInterval(updateCountdown, 1000);

// Update preview saat load jika sudah ada yang dipilih
window.onload = () => {
    previewProduk();
    updateCountdown();
};
