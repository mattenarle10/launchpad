/**
 * Image Viewer Component
 * Lightbox-style image viewer with zoom, pan, and download
 */

export class ImageViewer {
    constructor() {
        this.currentImage = null;
        this.isOpen = false;
        this.createViewer();
    }

    createViewer() {
        // Create viewer overlay
        const viewer = document.createElement('div');
        viewer.id = 'image-viewer';
        viewer.className = 'image-viewer';
        viewer.innerHTML = `
            <div class="image-viewer-overlay"></div>
            <div class="image-viewer-container">
                <div class="image-viewer-header">
                    <h3 class="image-viewer-title"></h3>
                    <div class="image-viewer-actions">
                        <button class="image-viewer-btn" id="image-download" title="Download">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                <polyline points="7 10 12 15 17 10"></polyline>
                                <line x1="12" y1="15" x2="12" y2="3"></line>
                            </svg>
                        </button>
                        <button class="image-viewer-btn" id="image-close" title="Close">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="image-viewer-content">
                    <img class="image-viewer-img" src="" alt="">
                    <div class="image-viewer-loading">
                        <div class="loading-spinner"></div>
                        <p>Loading image...</p>
                    </div>
                </div>
                <div class="image-viewer-footer">
                    <button class="image-viewer-btn" id="image-zoom-out" title="Zoom Out">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="8" y1="11" x2="14" y2="11"></line>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                    </button>
                    <button class="image-viewer-btn" id="image-zoom-reset" title="Reset Zoom">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                    </button>
                    <button class="image-viewer-btn" id="image-zoom-in" title="Zoom In">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="11" y1="8" x2="11" y2="14"></line>
                            <line x1="8" y1="11" x2="14" y2="11"></line>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                    </button>
                </div>
            </div>
        `;

        document.body.appendChild(viewer);
        this.viewer = viewer;
        this.img = viewer.querySelector('.image-viewer-img');
        this.title = viewer.querySelector('.image-viewer-title');
        this.loading = viewer.querySelector('.image-viewer-loading');
        
        this.scale = 1;
        this.translateX = 0;
        this.translateY = 0;

        this.setupEventListeners();
    }

    setupEventListeners() {
        // Close button
        this.viewer.querySelector('#image-close').addEventListener('click', () => this.close());
        
        // Overlay click
        this.viewer.querySelector('.image-viewer-overlay').addEventListener('click', () => this.close());
        
        // Download button
        this.viewer.querySelector('#image-download').addEventListener('click', () => this.download());
        
        // Zoom controls
        this.viewer.querySelector('#image-zoom-in').addEventListener('click', () => this.zoom(0.2));
        this.viewer.querySelector('#image-zoom-out').addEventListener('click', () => this.zoom(-0.2));
        this.viewer.querySelector('#image-zoom-reset').addEventListener('click', () => this.resetZoom());
        
        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            if (!this.isOpen) return;
            
            if (e.key === 'Escape') this.close();
            if (e.key === '+' || e.key === '=') this.zoom(0.2);
            if (e.key === '-' || e.key === '_') this.zoom(-0.2);
            if (e.key === '0') this.resetZoom();
        });

        // Mouse wheel zoom
        this.img.addEventListener('wheel', (e) => {
            e.preventDefault();
            const delta = e.deltaY > 0 ? -0.1 : 0.1;
            this.zoom(delta);
        });

        // Pan functionality
        let isPanning = false;
        let startX = 0;
        let startY = 0;

        this.img.addEventListener('mousedown', (e) => {
            if (this.scale > 1) {
                isPanning = true;
                startX = e.clientX - this.translateX;
                startY = e.clientY - this.translateY;
                this.img.style.cursor = 'grabbing';
            }
        });

        document.addEventListener('mousemove', (e) => {
            if (isPanning) {
                this.translateX = e.clientX - startX;
                this.translateY = e.clientY - startY;
                this.updateTransform();
            }
        });

        document.addEventListener('mouseup', () => {
            if (isPanning) {
                isPanning = false;
                this.img.style.cursor = this.scale > 1 ? 'grab' : 'default';
            }
        });
    }

    open(imageUrl, imageTitle = 'Image') {
        this.currentImage = imageUrl;
        this.title.textContent = imageTitle;
        this.loading.style.display = 'flex';
        this.img.style.display = 'none';
        
        // Reset zoom/pan
        this.resetZoom();
        
        // Load image
        this.img.src = imageUrl;
        this.img.onload = () => {
            this.loading.style.display = 'none';
            this.img.style.display = 'block';
        };
        
        this.img.onerror = () => {
            this.loading.innerHTML = `
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <p>Failed to load image</p>
            `;
        };

        this.viewer.classList.add('active');
        this.isOpen = true;
        document.body.style.overflow = 'hidden';
    }

    close() {
        this.viewer.classList.remove('active');
        this.isOpen = false;
        document.body.style.overflow = '';
        
        // Clear after animation
        setTimeout(() => {
            this.img.src = '';
            this.currentImage = null;
        }, 300);
    }

    zoom(delta) {
        this.scale = Math.max(0.5, Math.min(5, this.scale + delta));
        this.updateTransform();
        this.img.style.cursor = this.scale > 1 ? 'grab' : 'default';
    }

    resetZoom() {
        this.scale = 1;
        this.translateX = 0;
        this.translateY = 0;
        this.updateTransform();
        this.img.style.cursor = 'default';
    }

    updateTransform() {
        this.img.style.transform = `translate(${this.translateX}px, ${this.translateY}px) scale(${this.scale})`;
    }

    download() {
        if (!this.currentImage) return;
        
        const link = document.createElement('a');
        link.href = this.currentImage;
        link.download = this.title.textContent.replace(/[^a-z0-9]/gi, '_').toLowerCase();
        link.click();
    }
}

// Global instance
let viewerInstance = null;

export function openImageViewer(imageUrl, imageTitle) {
    if (!viewerInstance) {
        viewerInstance = new ImageViewer();
    }
    viewerInstance.open(imageUrl, imageTitle);
}

export function closeImageViewer() {
    if (viewerInstance) {
        viewerInstance.close();
    }
}

export default ImageViewer;

