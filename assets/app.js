import './stimulus_bootstrap.js';
import 'bootstrap';
import 'bootstrap/dist/css/bootstrap.min.css';
import './styles/app.css';
import './styles/song-show.css';
import './js/song-show.js';

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.play-btn').forEach((btn) => {
        const id = btn.dataset.id;
        if (!id) {
            return;
        }

        const audio = document.getElementById('audio-' + id);
        if (!audio) {
            return;
        }

        btn.addEventListener('click', () => {
            if (audio.paused) {
                audio.play();
                btn.textContent = '⏸️';
            } else {
                audio.pause();
                btn.textContent = '▶️';
            }
        });
    });
});
