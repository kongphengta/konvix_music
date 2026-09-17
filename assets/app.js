import './stimulus_bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';

const logoutTrigger = document.querySelector('.logout-trigger');
const logoutModal = document.getElementById('logout-modal');

if (logoutTrigger && logoutModal) {
    const closeButtons = logoutModal.querySelectorAll('[data-close-logout-modal="true"]');
    const logoutForm = logoutModal.querySelector('form');

    const openModal = () => {
        logoutModal.classList.add('is-open');
        logoutModal.setAttribute('aria-hidden', 'false');
    };

    const closeModal = () => {
        logoutModal.classList.remove('is-open');
        logoutModal.setAttribute('aria-hidden', 'true');
    };

    logoutTrigger.addEventListener('click', openModal);
    closeButtons.forEach((button) => {
        button.addEventListener('click', closeModal);
    });

    if (logoutForm) {
        logoutForm.addEventListener('submit', closeModal);
    }

    logoutModal.addEventListener('click', (event) => {
        if (event.target === logoutModal || event.target.matches('[data-close-logout-modal="true"]')) {
            closeModal();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && logoutModal.classList.contains('is-open')) {
            closeModal();
        }
    });
}
