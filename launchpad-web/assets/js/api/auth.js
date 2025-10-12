/**
 * Authentication API
 */

import client from './client.js';

const AuthAPI = {
    /**
     * Login user (CDC, Student, or Company)
     */
    async login(username, password, userType) {
        const data = await client.post('/auth/login', { 
            username, 
            password, 
            user_type: userType 
        }, { skipAuth: true });
        
        if (data.success) {
            client.setAuth(data.data.token, data.data.user);
        }
        
        return data;
    },

    /**
     * Logout user
     */
    async logout() {
        client.clearAuth();
        return { success: true };
    },

    /**
     * Check if user is authenticated
     */
    isAuthenticated() {
        return client.isAuthenticated();
    },

    /**
     * Get current user
     */
    getCurrentUser() {
        return client.getCurrentUser();
    }
};

export default AuthAPI;

