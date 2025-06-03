        document.addEventListener('DOMContentLoaded', function() {
            const profileTrigger = document.getElementById('profileTrigger');
            const profileOverlay = document.getElementById('profileOverlay');
            const closeProfile = document.querySelector('.close-profile');

            // Buka overlay saat ikon user diklik
            profileTrigger.addEventListener('click', function(e) {
                e.preventDefault();
                profileOverlay.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            });

            // Tutup overlay
            closeProfile.addEventListener('click', function() {
                profileOverlay.style.display = 'none';
                document.body.style.overflow = 'auto';
            });

            // Tutup overlay saat klik di luar konten
            profileOverlay.addEventListener('click', function(e) {
                if (e.target === profileOverlay) {
                    profileOverlay.style.display = 'none';
                    document.body.style.overflow = 'auto';
                }
            });

            // Tutup dengan tombol ESC
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && profileOverlay.style.display === 'flex') {
                    profileOverlay.style.display = 'none';
                    document.body.style.overflow = 'auto';
                }
            });
        });
