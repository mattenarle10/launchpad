/**
 * Universal File Viewer Component
 * Displays images, PDFs, and provides download for other file types
 */

export class FileViewer {
    constructor() {
        this.currentFile = null;
        this.isOpen = false;
        this.createViewer();
    }

    createViewer() {
        // Create viewer overlay
        const viewer = document.createElement('div');
        viewer.id = 'file-viewer';
        viewer.className = 'file-viewer';
        viewer.innerHTML = `
            <div class="file-viewer-overlay"></div>
            <div class="file-viewer-container">
                <div class="file-viewer-header">
                    <h3 class="file-viewer-title"></h3>
                    <div class="file-viewer-actions">
                        <button class="file-viewer-btn" id="file-download" title="Download">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                <polyline points="7 10 12 15 17 10"></polyline>
                                <line x1="12" y1="15" x2="12" y2="3"></line>
                            </svg>
                        </button>
                        <button class="file-viewer-btn" id="file-close" title="Close">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="file-viewer-body" id="file-viewer-body">
                    <!-- Content will be loaded dynamically -->
                </div>
            </div>
        `;

        document.body.appendChild(viewer);
        this.viewer = viewer;
        this.setupEventListeners();
    }

    setupEventListeners() {
        // Close button
        document.getElementById('file-close')?.addEventListener('click', () => this.close());

        // Download button
        document.getElementById('file-download')?.addEventListener('click', () => this.download());

        // Overlay click
        this.viewer.querySelector('.file-viewer-overlay')?.addEventListener('click', () => this.close());

        // Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isOpen) {
                this.close();
            }
        });
    }

    open(fileUrl, fileName = 'File') {
        this.currentFile = { url: fileUrl, name: fileName };
        this.isOpen = true;

        // Set title
        const titleElement = this.viewer.querySelector('.file-viewer-title');
        if (titleElement) {
            titleElement.textContent = fileName;
        }

        // Determine file type and display accordingly
        const extension = this.getFileExtension(fileUrl);
        this.displayFile(fileUrl, extension);

        // Show viewer
        this.viewer.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    displayFile(fileUrl, extension) {
        const body = document.getElementById('file-viewer-body');
        if (!body) return;

        // Clear previous content
        body.innerHTML = '';

        if (this.isImage(extension)) {
            // Display image
            body.innerHTML = `
                <div class="file-viewer-image-container">
                    <img src="${fileUrl}" alt="File preview" class="file-viewer-image" />
                </div>
            `;
        } else if (this.isPDF(extension)) {
            // Display PDF
            body.innerHTML = `
                <iframe 
                    src="${fileUrl}" 
                    class="file-viewer-pdf" 
                    frameborder="0"
                    title="PDF Preview"
                ></iframe>
            `;
        } else if (this.isDocument(extension)) {
            // Try to display document using Google Docs Viewer
            body.innerHTML = `
                <iframe 
                    src="https://docs.google.com/viewer?url=${encodeURIComponent(fileUrl)}&embedded=true" 
                    class="file-viewer-pdf" 
                    frameborder="0"
                    title="Document Preview"
                ></iframe>
            `;
        } else {
            // Unsupported file type - show download option
            body.innerHTML = `
                <div class="file-viewer-unsupported">
                    <div class="file-viewer-icon">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                        </svg>
                    </div>
                    <h3>Preview Not Available</h3>
                    <p>This file type cannot be previewed in the browser.</p>
                    <p style="margin-top: 8px; color: #6B7280; font-size: 14px;">File type: <strong>${extension.toUpperCase()}</strong></p>
                    <button class="btn-download" onclick="document.getElementById('file-download').click()">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 8px;">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="7 10 12 15 17 10"></polyline>
                            <line x1="12" y1="15" x2="12" y2="3"></line>
                        </svg>
                        Download File
                    </button>
                </div>
            `;
        }
    }

    close() {
        this.viewer.style.display = 'none';
        this.isOpen = false;
        document.body.style.overflow = '';
        this.currentFile = null;
    }

    download() {
        if (!this.currentFile) return;

        const link = document.createElement('a');
        link.href = this.currentFile.url;
        link.download = this.currentFile.name;
        link.target = '_blank';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    getFileExtension(url) {
        const fileName = url.split('/').pop().split('?')[0];
        const parts = fileName.split('.');
        return parts.length > 1 ? parts.pop().toLowerCase() : '';
    }

    isImage(extension) {
        return ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'].includes(extension);
    }

    isPDF(extension) {
        return extension === 'pdf';
    }

    isDocument(extension) {
        return ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'].includes(extension);
    }
}

// Create singleton instance
let fileViewerInstance = null;

export function openFileViewer(fileUrl, fileName = 'File') {
    if (!fileViewerInstance) {
        fileViewerInstance = new FileViewer();
    }
    fileViewerInstance.open(fileUrl, fileName);
}

export function closeFileViewer() {
    if (fileViewerInstance) {
        fileViewerInstance.close();
    }
}
