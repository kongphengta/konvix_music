import './stimulus_bootstrap.js';
import './styles/app.css';
import './styles/song-show.css';
import './js/song-show.js';
import 'bootstrap';
import 'bootstrap/dist/css/bootstrap.min.css';

// ...
console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');

document.querySelectorAll('.play-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const id = btn.dataset.id;
        const audio = document.getElementById('audio-' + id);

        if (audio.paused) {
            audio.play();
            btn.textContent = '⏸️';
        } else {
            audio.pause();
            btn.textContent = '▶️';
        }

        audio.addEventListener('loadedmetadata', () => {
            document.querySelector('.duration[data-id="' + id + '"]').textContent =
                formatTime(audio.duration);
        });

        audio.addEventListener('timeupdate', () => {
            const progress = (audio.currentTime / audio.duration) * 100;
            document.querySelector('.progress-bar[data-id="' + id + '"]').style.width = progress + '%';

            document.querySelector('.current-time[data-id="' + id + '"]').textContent =
                formatTime(audio.currentTime);
        });
    });
});

function formatTime(seconds) {
    const minutes = Math.floor(seconds / 60);
    const secs = Math.floor(seconds % 60).toString().padStart(2, '0');
    return `${minutes}:${secs}`;
}
