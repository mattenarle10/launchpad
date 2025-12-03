/**
 * Authentication API
 */

import client from './client.js';

const AuthAPI = {
    /**
     * Login user (CDC,  or Company)
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
    },

    /**
     * Change password for authenticated user
     */
    async changePassword(currentPassword, newPassword, confirmPassword) {
        return client.post('/auth/change-password', {
            current_password: currentPassword,
            new_password: newPassword,
            confirm_password: confirmPassword
        });
    },

    /**
     * Validate password complexity (client-side)
     * @returns {Object} { valid: boolean, errors: string[] }
     */
    validatePasswordComplexity(password) {
        const errors = [];

        if (password.length < 8) {
            errors.push('Password must be at least 8 characters');
        }

        if (!/[A-Z]/.test(password)) {
            errors.push('Password must contain at least one uppercase letter');
        }

        if (!/[a-z]/.test(password)) {
            errors.push('Password must contain at least one lowercase letter');
        }

        if (!/[0-9]/.test(password)) {
            errors.push('Password must contain at least one number');
        }

        if (!/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)) {
            errors.push('Password must contain at least one special character');
        }

        return {
            valid: errors.length === 0,
            errors
        };
    }
};

export default AuthAPI;

