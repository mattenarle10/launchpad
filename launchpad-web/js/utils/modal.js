/**
 * Reusable Modal Component
 * Simple, flexible modal for displaying content
 */

export class Modal {
    constructor(id, options = {}) {
        this.id = id;
        this.options = {
            title: options.title || 'Modal',
            size: options.size || 'medium', // small, medium, large
            showFooter: options.showFooter !== false,
            closeOnOverlay: options.closeOnOverlay !== false,
            closeOnEscape: options.closeOnEscape !== false,
            ...options
        };
        this.isOpen = false;
        this.onCloseCallback = null;
        this.createModal();
    }

    createModal() {
        // Check if modal already exists
        if (document.getElementById(this.id)) {
            this.modal = document.getElementById(this.id);
            this.setupExistingModal();
            return;
        }

        // Create new modal
        const modal = document.createElement('div');
        modal.id = this.id;
        modal.className = `modal modal-${this.options.size}`;
        modal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title">${this.options.title}</h2>
                    <button class="close-modal" data-modal-close>&times;</button>
                </div>
                <div class="modal-body">
                    <!-- Content will be loaded dynamically -->
                </div>
                ${this.options.showFooter ? `
                    <div class="modal-footer">
                        <button class="btn-modal" data-modal-close>Close</button>
                    </div>
                ` : ''}
            </div>
        `;

        document.body.appendChild(modal);
        this.modal = modal;
        this.setupEventListeners();
    }

    setupExistingModal() {
        this.setupEventListeners();
    }

    setupEventListeners() {
        // Close button
        const closeButtons = this.modal.querySelectorAll('[data-modal-close]');
        closeButtons.forEach(btn => {
            btn.addEventListener('click', () => this.close());
        });

        // Overlay click
        if (this.options.closeOnOverlay) {
            this.modal.addEventListener('click', (e) => {
                if (e.target === this.modal) {
                    this.close();
                }
            });
        }

        // Escape key
        if (this.options.closeOnEscape) {
            this.escapeHandler = (e) => {
                if (e.key === 'Escape' && this.isOpen) {
                    this.close();
                }
            };
            document.addEventListener('keydown', this.escapeHandler);
        }
    }

    open(content, title = null) {
        if (title) {
            this.setTitle(title);
        }
        
        this.setContent(content);
        this.modal.style.display = 'block';
        this.isOpen = true;
        document.body.style.overflow = 'hidden';
    }

    close() {
        this.modal.style.display = 'none';
        this.isOpen = false;
        document.body.style.overflow = '';
        
        if (this.onCloseCallback) {
            this.onCloseCallback();
        }
    }

    setTitle(title) {
        const titleElement = this.modal.querySelector('.modal-title');
        if (titleElement) {
            titleElement.textContent = title;
        }
    }

    setContent(content) {
        const bodyElement = this.modal.querySelector('.modal-body');
        if (bodyElement) {
            if (typeof content === 'string') {
                bodyElement.innerHTML = content;
            } else if (content instanceof HTMLElement) {
                bodyElement.innerHTML = '';
                bodyElement.appendChild(content);
            }
        }
    }

    setFooter(footerContent) {
        let footerElement = this.modal.querySelector('.modal-footer');
        
        if (!footerElement && footerContent) {
            // Create footer if it doesn't exist
            const modalContent = this.modal.querySelector('.modal-content');
            footerElement = document.createElement('div');
            footerElement.className = 'modal-footer';
            modalContent.appendChild(footerElement);
        }
        
        if (footerElement) {
            if (typeof footerContent === 'string') {
                footerElement.innerHTML = footerContent;
            } else if (footerContent instanceof HTMLElement) {
                footerElement.innerHTML = '';
                footerElement.appendChild(footerContent);
            }
        }
    }

    onClose(callback) {
        this.onCloseCallback = callback;
    }

    destroy() {
        if (this.escapeHandler) {
            document.removeEventListener('keydown', this.escapeHandler);
        }
        if (this.modal && this.modal.parentNode) {
            this.modal.parentNode.removeChild(this.modal);
        }
    }
}

/**
 * Create and return a modal instance
 */
export function createModal(id, options) {
    return new Modal(id, options);
}

/**
 * Quick modal functions
 */
export function showModal(id, content, title) {
    const modal = new Modal(id, { title });
    modal.open(content, title);
    return modal;
}

export function closeModal(id) {
    const modalElement = document.getElementById(id);
    if (modalElement) {
        modalElement.style.display = 'none';
        document.body.style.overflow = '';
    }
}

export default Modal;

