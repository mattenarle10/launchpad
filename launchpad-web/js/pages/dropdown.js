/**
 * Reusable User Dropdown Component
 * Handles dropdown toggle and click outside to close
 */

/**
 * Initialize user dropdown menu
 * @param {string} toggleId - ID of the toggle button
 * @param {string} dropdownId - ID of the dropdown menu
 * @param {Function} onLogout - Callback function for logout
 * @param {Function} onProfile - Callback function for profile (optional)
 */
export function initUserDropdown(toggleId, dropdownId, onLogout, onProfile = null) {
    const toggle = document.getElementById(toggleId);
    const dropdown = document.getElementById(dropdownId);
    const userMenu = toggle?.closest('.user-menu');
    
    if (!toggle || !dropdown) {
        console.error('Dropdown elements not found');
        return;
    }

    // Toggle dropdown
    toggle.addEventListener('click', (e) => {
        e.stopPropagation();
        dropdown.classList.toggle('show');
        userMenu?.classList.toggle('active');
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', (e) => {
        if (!userMenu?.contains(e.target)) {
            dropdown.classList.remove('show');
            userMenu?.classList.remove('active');
        }
    });

    // Handle logout button
    const logoutBtn = document.getElementById('logout-btn');
    if (logoutBtn && onLogout) {
        logoutBtn.addEventListener('click', (e) => {
            e.preventDefault();
            dropdown.classList.remove('show');
            userMenu?.classList.remove('active');
            onLogout();
        });
    }

    // Handle profile button (optional)
    const profileBtn = document.getElementById('profile-btn');
    if (profileBtn && onProfile) {
        profileBtn.addEventListener('click', (e) => {
            e.preventDefault();
            dropdown.classList.remove('show');
            userMenu?.classList.remove('active');
            onProfile();
        });
    }

    // Close on Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            dropdown.classList.remove('show');
            userMenu?.classList.remove('active');
        }
    });
}

/**
 * Update dropdown user name
 * @param {string} name - User's full name
 */
export function updateDropdownUserName(name) {
    const dropdownName = document.getElementById('dropdown-user-name');
    if (dropdownName) {
        dropdownName.textContent = name;
    }
}

