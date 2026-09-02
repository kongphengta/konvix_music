document.addEventListener('DOMContentLoaded', () => {
    const audio = document.getElementById('mainPlayer');

    if (!audio) {
        return;
    }

    const playPauseBtn = document.getElementById('playPauseBtn');
    const rewindBtn = document.getElementById('rewindBtn');
    const forwardBtn = document.getElementById('forwardBtn');
    const loopBtn = document.getElementById('loopBtn');
    const muteBtn = document.getElementById('muteBtn');
    const progressBar = document.getElementById('progressBar');
    const currentTimeEl = document.getElementById('currentTime');
    const totalTimeEl = document.getElementById('totalTime');

    const formatTime = (seconds) => {
        if (!Number.isFinite(seconds)) {
            return '0:00';
        }

        const mins = Math.floor(seconds / 60);
        const secs = Math.floor(seconds % 60);
        return `${mins}:${String(secs).padStart(2, '0')}`;
    };

    const updateControls = () => {
        playPauseBtn.textContent = audio.paused ? '▶' : '❚❚';
        const current = Number.isFinite(audio.currentTime) ? audio.currentTime : 0;
        const duration = Number.isFinite(audio.duration) && audio.duration > 0 ? audio.duration : 0;
        const progress = duration > 0 ? (current / duration) * 100 : 0;
        progressBar.value = String(progress);
        currentTimeEl.textContent = formatTime(current);
        totalTimeEl.textContent = formatTime(duration);
    };

    playPauseBtn.addEventListener('click', () => {
        if (audio.paused) {
            audio.play();
        } else {
            audio.pause();
        }
    });

    rewindBtn.addEventListener('click', () => {
        audio.currentTime = Math.max(0, audio.currentTime - 10);
    });

    forwardBtn.addEventListener('click', () => {
        audio.currentTime = Math.min(audio.duration || audio.currentTime, audio.currentTime + 10);
    });

    loopBtn.addEventListener('click', () => {
        audio.loop = !audio.loop;
        loopBtn.classList.toggle('active', audio.loop);
    });

    muteBtn.addEventListener('click', () => {
        audio.muted = !audio.muted;
        muteBtn.textContent = audio.muted ? '🔇' : '🔊';
    });

    progressBar.addEventListener('input', (event) => {
        const duration = audio.duration || 0;
        if (duration > 0) {
            audio.currentTime = (Number(event.target.value) / 100) * duration;
        }
    });

    audio.addEventListener('play', updateControls);
    audio.addEventListener('pause', updateControls);
    audio.addEventListener('timeupdate', updateControls);
    audio.addEventListener('loadedmetadata', updateControls);
    audio.addEventListener('ended', () => {
        if (!audio.loop) {
            playPauseBtn.textContent = '▶';
        }
    });

    if ('mediaSession' in navigator) {
        const coverUrl = audio.dataset.cover || '';
        navigator.mediaSession.metadata = new MediaMetadata({
            title: audio.dataset.title || 'Konvix Music',
            artist: audio.dataset.artist || 'Konvix Music',
            album: audio.dataset.album || '',
            artwork: coverUrl ? [{ src: coverUrl, sizes: '512x512', type: 'image/jpeg' }] : []
        });
    }
});
