const initTrackPublicationUi = () => {
    const form = document.querySelector('[data-track-publication-form]');

    if (!form) {
        return;
    }

    const modeInputs = form.querySelectorAll('input[name="track[publishMode]"]');
    const dateWrapper = form.querySelector('#scheduled-date-wrapper');
    const titleInput = form.querySelector('#track_title, input[id$="_title"], input[name$="[title]"]');
    const slugInput = form.querySelector('#track_slug, input[id$="_slug"], input[name$="[slug]"]');

    const slugify = (value) => value
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '')
        .slice(0, 80);

    const toggleScheduledDate = () => {
        const selectedMode = form.querySelector('input[name="track[publishMode]"]:checked');

        if (!dateWrapper || !selectedMode) {
            return;
        }

        const shouldShow = selectedMode.value === 'scheduled';
        dateWrapper.style.display = shouldShow ? 'block' : 'none';
    };

    modeInputs.forEach((input) => {
        input.removeEventListener('change', toggleScheduledDate);
        input.addEventListener('change', toggleScheduledDate);
    });
    toggleScheduledDate();

    if (titleInput && slugInput) {
        const syncSlug = () => {
            const slug = slugify(titleInput.value || '');
            if (slug) {
                slugInput.value = slug;
            }
        };

        titleInput.removeEventListener('input', syncSlug);
        titleInput.removeEventListener('change', syncSlug);
        titleInput.addEventListener('input', syncSlug);
        titleInput.addEventListener('change', syncSlug);

        if (!slugInput.value.trim() || slugInput.value === 'généré automatiquement') {
            syncSlug();
        }
    }
};

['DOMContentLoaded', 'turbo:load'].forEach((eventName) => {
    document.addEventListener(eventName, initTrackPublicationUi);
});

if (document.readyState !== 'loading') {
    initTrackPublicationUi();
}
