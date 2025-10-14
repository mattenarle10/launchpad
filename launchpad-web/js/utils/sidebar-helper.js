/**
 * Sidebar Helper
 * Determines which sidebar to use based on user role
 */

import client from '../api/client.js';

/**
 * Get sidebar mode based on current user
 * @returns {string} 'pc' for partner company, 'cdc' for CDC/default
 */
export function getSidebarMode() {
    const currentUser = client.getCurrentUser() || {};
    // Check if user is a company (either by role or company_id presence)
    return (currentUser.role === 'company' || currentUser.company_id) ? 'pc' : 'cdc';
}
