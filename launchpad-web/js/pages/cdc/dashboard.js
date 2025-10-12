/**
 * CDC Dashboard Logic
 */

import { AuthAPI } from '../../api/index.js';
import { showAlert } from '../../components.js';
import { initUserDropdown, updateDropdownUserName } from '../dropdown.js';

// Check authentication
if (!AuthAPI.isAuthenticated()) {
    window.location.href = '../login.html?type=cdc';
}

const currentUser = AuthAPI.getCurrentUser();

// Check if user exists
if (!currentUser) {
    showAlert('Session expired. Please login again.', 'error');
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
            showAlert('Logged out successfully', 'success');
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
        showAlert('Profile page coming soon!', 'info');
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
        showAlert('Failed to load dashboard statistics', 'error');
    }
}

// Load data on page load
loadDashboardStats();
