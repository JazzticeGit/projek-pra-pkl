function togglePopup() {
  const popup = document.getElementById('profilePopup');
  popup.classList.toggle('hidden');
}

// Tutup pop-up jika klik di luar area
document.addEventListener('click', function(event) {
  const popup = document.getElementById('profilePopup');
  const button = document.querySelector('.profile-button');
  if (!popup.contains(event.target) && !button.contains(event.target)) {
    popup.classList.add('hidden');
  }
});