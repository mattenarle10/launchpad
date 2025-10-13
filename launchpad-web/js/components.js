/**
 * Load Reusable Components
 * Dynamically loads sidebar and other shared components
 */

// Load Sidebar Component
export async function loadSidebar(activePage) {
    const sidebarPlaceholder = document.getElementById('sidebar-placeholder');
    if (!sidebarPlaceholder) return;

    try {
        const response = await fetch('../sidebar.html');
        const html = await response.text();
        sidebarPlaceholder.innerHTML = html;

        // Set active page
        if (activePage) {
            const navItems = sidebarPlaceholder.querySelectorAll('.nav-item');
            navItems.forEach(item => {
                if (item.dataset.page === activePage) {
                    item.classList.add('active');
                }
            });
        }
    } catch (error) {
        console.error('Error loading sidebar:', error);
    }
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
