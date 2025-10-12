/**
 * CDC Dashboard Logic
 */

import { initSidebar } from '../sidebar.js';
import { showAlert } from '../../components.js';

// Initialize sidebar and get current user
const currentUser = initSidebar({
    userType: 'cdc',
    portalName: 'Career Development Centre Portal'
});

// If currentUser is undefined, initSidebar already handled the redirect
if (!currentUser) {
    throw new Error('User not authenticated');
}

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
