/**
 * Load Reusable Components
 * Dynamically loads sidebar and other shared components
 */

// Load Sidebar Component
export async function loadSidebar(activePage) {
    const sidebarPlaceholder = document.getElementById('sidebar-placeholder');
    if (!sidebarPlaceholder) return;

    try {
        // Inject minimal fade CSS once
        const fadeStyleId = 'app-fade-style';
        if (!document.getElementById(fadeStyleId)) {
            const style = document.createElement('style');
            style.id = fadeStyleId;
            style.textContent = `
                .app-loading .page-header, .app-loading .main-content { opacity: 0; }
                .page-header, .main-content { transition: opacity 150ms ease; }
            `;
            document.head.appendChild(style);
        }

        // Determine correct path to sidebar based on current location
        const currentPath = window.location.pathname;
        let sidebarPath = 'sidebar.html';
        
        // If we're in a subdirectory like /cdc/ or /pc/, go up one level
        if (currentPath.includes('/cdc/') || currentPath.includes('/pc/')) {
            sidebarPath = '../sidebar.html';
        }
        
        // Try using cached sidebar to render instantly
        const cacheKey = 'sidebar_html_cache_v1';
        const cached = sessionStorage.getItem(cacheKey);

        // Reduce flash only when we do not have cached HTML ready
        let originalSidebarWidth = '';
        let originalSidebarMinWidth = '';
        if (!cached) {
            document.body.classList.add('app-loading');
            originalSidebarWidth = sidebarPlaceholder.style.width;
            originalSidebarMinWidth = sidebarPlaceholder.style.minWidth;
            sidebarPlaceholder.style.width = '260px';
            sidebarPlaceholder.style.minWidth = '260px';
        }
        if (cached) {
            sidebarPlaceholder.innerHTML = cached;
        }

        // Fetch latest sidebar HTML (do not block render if cached exists)
        const response = await fetch(sidebarPath);
        const html = await response.text();
        if (!cached || cached !== html) {
            sidebarPlaceholder.innerHTML = html;
            sessionStorage.setItem(cacheKey, html);
        }

        // Fix image paths based on current location
        const isInSubdirectory = currentPath.includes('/cdc/') || currentPath.includes('/pc/');
        const imagePrefix = isInSubdirectory ? '../../images/' : '../images/';
        
        // Update logo image path and preload common logos
        const logoImg = sidebarPlaceholder.querySelector('.sidebar-logo img');
        const launchpadLogo = imagePrefix + 'logo/launchpad.png';
        const cdcLogo = imagePrefix + 'logo/cdc-avatar.png';
        if (logoImg) logoImg.src = launchpadLogo;
        const preloadA = new Image(); preloadA.src = launchpadLogo;
        const preloadB = new Image(); preloadB.src = cdcLogo;

        // Fix navigation links
        const navItems = sidebarPlaceholder.querySelectorAll('.nav-item');
        navItems.forEach(item => {
            const href = item.getAttribute('href');
            // If we're in /pages/, links should go to cdc/
            if (!isInSubdirectory && href && !href.startsWith('http')) {
                item.setAttribute('href', 'cdc/' + href);
            }
            
            // Set active page
            if (activePage && item.dataset.page === activePage) {
                item.classList.add('active');
            }

            // Prefetch on hover to reduce perceived flash
            const finalHref = item.getAttribute('href');
            item.addEventListener('mouseenter', () => prefetchLink(finalHref));
        });

        // Fade content back in and clear reserved width (only if we set it)
        if (!cached) {
            requestAnimationFrame(() => {
                document.body.classList.remove('app-loading');
                sidebarPlaceholder.style.width = originalSidebarWidth;
                sidebarPlaceholder.style.minWidth = originalSidebarMinWidth;
            });
        }
    } catch (error) {
        console.error('Error loading sidebar:', error);
        // Ensure we don't leave the UI faded out on error
        document.body.classList.remove('app-loading');
        sidebarPlaceholder.style.width = '';
        sidebarPlaceholder.style.minWidth = '';
    }
}

// Prefetch helper for internal navigation (reduces perceived flash on next page)
function prefetchLink(href) {
    if (!href || href.startsWith('http')) return;
    // Avoid duplicating prefetch tags
    const id = `prefetch:${href}`;
    if (document.getElementById(id)) return;
    const link = document.createElement('link');
    link.id = id;
    link.rel = 'prefetch';
    link.href = href;
    document.head.appendChild(link);
}

// Load user info from localStorage
export function loadUserInfo() {
    const user = JSON.parse(localStorage.getItem('user') || '{}');
    
    const userNameEl = document.getElementById('user-name');
    const dropdownNameEl = document.getElementById('dropdown-user-name');
    
    if (userNameEl && user.username) {
        userNameEl.textContent = user.username;
    }
    
    if (dropdownNameEl && user.username) {
        dropdownNameEl.textContent = user.username;
    }
}
