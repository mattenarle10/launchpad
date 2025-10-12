/**
 * Reusable Sidebar Component
 * Used for both CDC and PC portals
 */

import { AuthAPI } from '../api/index.js';
import { showAlert } from '../components.js';

/**
 * Initialize sidebar with navigation items and user info
 * @param {Object} config - Sidebar configuration
 * @param {string} config.userType - 'cdc' or 'pc'
 * @param {Array} config.navItems - Array of navigation items {label, href, active}
 * @param {string} config.portalName - Display name for the portal
 */
export function initSidebar(config) {
    const { userType, navItems, portalName } = config;
    
    // Check authentication
    if (!AuthAPI.isAuthenticated()) {
        window.location.href = `../login.html?type=${userType}`;
        return;
    }

    const currentUser = AuthAPI.getCurrentUser();
    
    // Check if user exists
    if (!currentUser) {
        showAlert('Session expired. Please login again.', 'error');
        setTimeout(() => {
            window.location.href = `../login.html?type=${userType}`;
        }, 1500);
        return;
    }

    // Display user info in header
    const userName = `${currentUser.first_name || ''} ${currentUser.last_name || ''}`.trim() 
        || currentUser.company_name 
        || currentUser.username 
        || 'User';
    
    document.getElementById('user-name').textContent = userName;

    // Set avatar initials
    let initials = 'U';
    if (currentUser.first_name && currentUser.last_name) {
        initials = `${currentUser.first_name[0]}${currentUser.last_name[0]}`.toUpperCase();
    } else if (currentUser.company_name) {
        initials = currentUser.company_name.substring(0, 2).toUpperCase();
    } else if (currentUser.username) {
        initials = currentUser.username.substring(0, 2).toUpperCase();
    }
    
    document.getElementById('user-avatar').textContent = initials;
    document.getElementById('user-badge').textContent = initials;

    // Setup logout handler
    document.getElementById('logout-btn').addEventListener('click', async () => {
        try {
            await AuthAPI.logout();
            showAlert('Logged out successfully', 'success');
            setTimeout(() => {
                window.location.href = `../login.html?type=${userType}`;
            }, 1000);
        } catch (error) {
            console.error('Logout error:', error);
            window.location.href = `../login.html?type=${userType}`;
        }
    });

    return currentUser;
}

/**
 * CDC Navigation Items
 */
export const CDC_NAV_ITEMS = [
    { label: 'Dashboard', href: 'dashboard.html' },
    { label: 'Students', href: 'students.html' },
    { label: 'Companies', href: 'companies.html' },
    { label: 'Verify Users', href: 'verify-users.html' },
    { label: 'Send Notification', href: 'send-notification.html' },
    { label: 'Submission Reports', href: 'submission-reports.html' },
    { label: "Students' OJT Hours", href: 'ojt-hours.html' },
    { label: 'Job Postings', href: 'job-postings.html' }
];

/**
 * PC Navigation Items
 */
export const PC_NAV_ITEMS = [
    { label: 'Dashboard', href: 'dashboard.html' },
    { label: 'Students', href: 'students.html' },
    { label: "Students' Evaluation", href: 'students-evaluation.html' },
    { label: 'Performance Scores', href: 'performance-scores.html' },
    { label: 'Job Opportunities', href: 'job-opportunities.html' }
];

