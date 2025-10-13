/**
 * CDC Dashboard Logic
 */

import { AuthAPI } from '../../api/index.js';
import { showSuccess, showError, showWarning } from '../../utils/notifications.js';
import { initUserDropdown, updateDropdownUserName } from '../dropdown.js';

// Check authentication
if (!AuthAPI.isAuthenticated()) {
    window.location.href = '../login.html?type=cdc';
}

const currentUser = AuthAPI.getCurrentUser();

// Check if user exists
if (!currentUser) {
    showError('Session expired. Please login again.');
    setTimeout(() => {
        window.location.href = '../login.html?type=cdc';
    }, 1500);
}

// Display user info in header
const userName = `${currentUser.first_name || ''} ${currentUser.last_name || ''}`.trim() 
    || currentUser.username 
    || 'User';

document.getElementById('user-name').textContent = userName;
updateDropdownUserName(userName);

// Initialize dropdown menu
initUserDropdown(
    'user-menu-toggle',
    'user-dropdown',
    // Logout callback
    async () => {
        try {
            await AuthAPI.logout();
            showSuccess('Logged out successfully');
            setTimeout(() => {
                window.location.href = '../login.html?type=cdc';
            }, 1000);
        } catch (error) {
            console.error('Logout error:', error);
            window.location.href = '../login.html?type=cdc';
        }
    },
    // Profile callback (optional)
    () => {
        showWarning('Profile page coming soon!');
        // TODO: Navigate to profile page
        // window.location.href = 'profile.html';
    }
);

console.log('CDC Dashboard loaded for:', currentUser);

// TODO: Fetch real dashboard data from API
// For now, using placeholder data from the HTML

/**
 * Load dashboard statistics
 */
async function loadDashboardStats() {
    try {
        // TODO: Call GET /admin/ojt-stats endpoint
        // const stats = await CDCAPI.getOJTStats();
        // Update the UI with real data
        
        console.log('Dashboard stats loaded');
    } catch (error) {
        console.error('Error loading dashboard stats:', error);
        showError('Failed to load dashboard statistics');
    }
}

// Load data on page load
loadDashboardStats();
