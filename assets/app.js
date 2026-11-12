import './stimulus_bootstrap.js';
import 'bootstrap';
import 'bootstrap/dist/css/bootstrap.min.css';
import './styles/app.css';

/*
 * Bootstrap est intégré via AssetMapper.
 * On place ici les scripts globaux de l’application.
 * Chaque page ou fonctionnalité doit être commentée clairement.
 */

const initHomePlayer = () => {
    const playlistRoot = document.querySelector('[data-player-playlist]');

    if (!playlistRoot) {
        return;
    }

    const rows = [...playlistRoot.querySelectorAll('.track-row')];

    if (rows.length === 0) {
        return;
    }

    const titleEl = document.getElementById('player-title');
    const artistEl = document.getElementById('player-artist');
    const playToggle = document.getElementById('play-toggle');
    const prevTrackButton = document.getElementById('prev-track');
    const nextTrackButton = document.getElementById('next-track');
    const progressBar = document.getElementById('progress-bar');
    const currentTimeEl = document.getElementById('current-time');
    const durationTimeEl = document.getElementById('duration-time');
    const volumeControl = document.getElementById('volume-control');
    const playerArt = document.getElementById('player-art');
    const audio = new Audio();

    let currentIndex = 0;
    let isDraggingProgress = false;

    const formatTime = (seconds) => {
        if (!Number.isFinite(seconds) || seconds < 0) {
            return '0:00';
        }

        const wholeSeconds = Math.floor(seconds);
        const minutes = Math.floor(wholeSeconds / 60);
        const secs = wholeSeconds % 60;

        return `${minutes}:${String(secs).padStart(2, '0')}`;
    };

    const setPlayerFallback = (label = 'Aucun morceau', artist = 'Choisissez une piste') => {
        titleEl.textContent = label;
        artistEl.textContent = artist;
        playerArt.textContent = '♪';
        playerArt.style.background = 'linear-gradient(135deg, rgba(139, 92, 246, 0.85), rgba(96, 165, 250, 0.85))';
        playerArt.style.backgroundImage = 'linear-gradient(135deg, rgba(139, 92, 246, 0.85), rgba(96, 165, 250, 0.85))';
        progressBar.style.width = '0%';
        currentTimeEl.textContent = '0:00';
        durationTimeEl.textContent = '0:00';
        playToggle.textContent = '▶';
        playToggle.setAttribute('aria-label', 'Lecture');
        audio.pause();
        audio.removeAttribute('src');
        audio.load();
    };

    const updateActiveRow = () => {
        rows.forEach((row, index) => {
            const isActive = index === currentIndex;
            row.classList.toggle('is-active', isActive);
            row.setAttribute('aria-pressed', isActive ? 'true' : 'false');
        });
    };

    const setCoverArt = (coverUrl) => {
        if (!coverUrl) {
            playerArt.textContent = '♪';
            playerArt.style.background = 'linear-gradient(135deg, rgba(139, 92, 246, 0.85), rgba(96, 165, 250, 0.85))';
            playerArt.style.backgroundImage = 'linear-gradient(135deg, rgba(139, 92, 246, 0.85), rgba(96, 165, 250, 0.85))';
            return;
        }

        playerArt.textContent = '';
        playerArt.style.backgroundImage = `url('${coverUrl}')`;
        playerArt.style.backgroundSize = 'cover';
        playerArt.style.backgroundPosition = 'center';
    };

    const setTrack = (index, shouldAutoplay = true) => {
        const row = rows[index];

        if (!row) {
            return;
        }

        currentIndex = index;
        updateActiveRow();

        const title = row.dataset.trackTitle || 'Morceau';
        const artist = row.dataset.trackArtist || 'Artiste';
        const url = row.dataset.trackUrl || '';
        const cover = row.dataset.trackCover || '';

        titleEl.textContent = title;
        artistEl.textContent = artist;
        setCoverArt(cover);

        if (!url) {
            setPlayerFallback(title, artist);
            return;
        }

        audio.src = url;
        audio.load();
        audio.volume = Number(volumeControl.value) / 100;

        if (shouldAutoplay) {
            audio.play().catch(() => {
                playToggle.textContent = '▶';
                playToggle.setAttribute('aria-label', 'Lecture');
            });
        }
    };

    rows.forEach((row, index) => {
        row.addEventListener('click', (event) => {
            if (event.target.closest('button')) {
                return;
            }

            setTrack(index, true);
        });

        const playButton = row.querySelector('.track-play-btn');

        if (playButton) {
            playButton.addEventListener('click', (event) => {
                event.stopPropagation();
                setTrack(index, true);
            });
        }
    });

    playToggle.addEventListener('click', () => {
        if (!audio.src) {
            const row = rows[currentIndex];
            if (row && row.dataset.trackUrl) {
                setTrack(currentIndex, true);
                return;
            }

            return;
        }

        if (audio.paused) {
            audio.play().catch(() => {});
            return;
        }

        audio.pause();
    });

    prevTrackButton.addEventListener('click', () => {
        const previousIndex = (currentIndex - 1 + rows.length) % rows.length;
        setTrack(previousIndex, true);
    });

    nextTrackButton.addEventListener('click', () => {
        const nextIndex = (currentIndex + 1) % rows.length;
        setTrack(nextIndex, true);
    });

    volumeControl.addEventListener('input', (event) => {
        const value = Number(event.target.value);
        audio.volume = value / 100;
    });

    audio.addEventListener('play', () => {
        playToggle.textContent = '❚❚';
        playToggle.setAttribute('aria-label', 'Pause');
    });

    audio.addEventListener('pause', () => {
        playToggle.textContent = '▶';
        playToggle.setAttribute('aria-label', 'Lecture');
    });

    audio.addEventListener('loadedmetadata', () => {
        durationTimeEl.textContent = formatTime(audio.duration || 0);
    });

    audio.addEventListener('timeupdate', () => {
        if (!isDraggingProgress && Number.isFinite(audio.duration) && audio.duration > 0) {
            const percentage = (audio.currentTime / audio.duration) * 100;
            progressBar.style.width = `${percentage}%`;
            progressBar.setAttribute('aria-valuenow', String(Math.round(percentage)));
            currentTimeEl.textContent = formatTime(audio.currentTime);
        }
    });

    audio.addEventListener('ended', () => {
        const nextIndex = (currentIndex + 1) % rows.length;
        setTrack(nextIndex, true);
    });

    audio.volume = Number(volumeControl.value) / 100;

    const initialIndex = rows.findIndex((row) => row.dataset.trackUrl);
    if (initialIndex !== -1) {
        setTrack(initialIndex, false);
    } else {
        setPlayerFallback('Aucun morceau', 'Choisissez une piste');
        updateActiveRow();
    }
};

document.addEventListener('DOMContentLoaded', initHomePlayer);
console.log('Konvix Music - bootstrap chargé via AssetMapper.');
