import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['trigger', 'menu', 'logoutTrigger'];

    connect() {
        this.setupEventListeners();
    }

    setupEventListeners() {
        // Toggle dropdown on click
        this.triggerTarget.addEventListener('click', (e) => {
            e.stopPropagation();
            this.toggle();
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!this.element.contains(e.target)) {
                this.close();
            }
        });

        // Close dropdown on escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.close();
            }
        });

        // Handle logout
        this.logoutTriggerTarget?.addEventListener('click', (e) => {
            e.preventDefault();
            this.close();
            this.showLogoutModal();
        });
    }

    toggle() {
        const isOpen = this.triggerTarget.getAttribute('aria-expanded') === 'true';
        isOpen ? this.close() : this.open();
    }

    open() {
        this.triggerTarget.setAttribute('aria-expanded', 'true');
    }

    close() {
        this.triggerTarget.setAttribute('aria-expanded', 'false');
    }

    showLogoutModal() {
        const modal = document.createElement('div');
        modal.className = 'logout-modal-overlay';
        modal.innerHTML = `
            <div class="logout-modal">
                <div class="logout-modal__header">
                    <h2>Confirmer la déconnexion</h2>
                </div>
                <div class="logout-modal__body">
                    <p>Êtes-vous sûr de vouloir vous déconnecter?</p>
                </div>
                <div class="logout-modal__footer">
                    <button class="logout-modal__btn logout-modal__btn--cancel">Annuler</button>
                    <form method="POST" action="/logout" class="logout-modal__form">
                        <button type="submit" class="logout-modal__btn logout-modal__btn--logout">Se déconnecter</button>
                    </form>
                </div>
            </div>
        `;

        document.body.appendChild(modal);

        const cancelBtn = modal.querySelector('.logout-modal__btn--cancel');
        cancelBtn.addEventListener('click', () => {
            modal.remove();
        });

        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.remove();
            }
        });

        // Add fade-in animation
        setTimeout(() => {
            modal.classList.add('show');
        }, 10);
    }
}
