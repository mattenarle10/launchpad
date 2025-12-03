/**
 * Base API Client - Handles all HTTP requests
 */

// Local Development (commented for production)
const API_BASE_URL = 'http://localhost/LaunchPad/launchpad-api/public';

// Production (Hostinger) - ACTIVE
// const API_BASE_URL = 'https://launchpadph.net/launchpad-api/public';

class APIClient {
    constructor() {
        this.token = localStorage.getItem('auth_token');
        this.user = JSON.parse(localStorage.getItem('user') || 'null');
    }

    /**
     * Set authentication token
     */
    setAuth(token, user) {
        this.token = token;
        this.user = user;
        localStorage.setItem('auth_token', token);
        localStorage.setItem('user', JSON.stringify(user));
    }

    /**
     * Clear authentication
     */
    clearAuth() {
        this.token = null;
        this.user = null;
        localStorage.removeItem('auth_token');
        localStorage.removeItem('user');
    }

    /**
     * Check if user is authenticated
     */
    isAuthenticated() {
        return !!this.token;
    }

    /**
     * Get current user
     */
    getCurrentUser() {
        return this.user;
    }

    /**
     * Make HTTP request
     */
    async request(endpoint, options = {}) {
        const url = `${API_BASE_URL}${endpoint}`;
        
        const config = {
            ...options,
            headers: {
                ...options.headers,
            }
        };

        // Add auth token if available
        if (this.token && !options.skipAuth) {
            config.headers['Authorization'] = `Bearer ${this.token}`;
        }

        // Add Content-Type for JSON requests (not for FormData)
        if (options.body && !(options.body instanceof FormData)) {
            config.headers['Content-Type'] = 'application/json';
        }

        try {
            console.log('API Request:', { url, method: config.method, body: config.body });
            const response = await fetch(url, config);
            const data = await response.json();
            console.log('API Response:', { status: response.status, data });

            if (!response.ok) {
                throw new Error(data.message || 'Request failed');
            }

            return data;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    }

    /**
     * GET request
     */
    async get(endpoint, options = {}) {
        return this.request(endpoint, {
            ...options,
            method: 'GET'
        });
    }

    /**
     * POST request
     */
    async post(endpoint, data, options = {}) {
        return this.request(endpoint, {
            ...options,
            method: 'POST',
            body: data instanceof FormData ? data : JSON.stringify(data)
        });
    }

    /**
     * PUT request
     */
    async put(endpoint, data, options = {}) {
        return this.request(endpoint, {
            ...options,
            method: 'PUT',
            body: JSON.stringify(data)
        });
    }

    /**
     * DELETE request
     */
    async delete(endpoint, options = {}) {
        return this.request(endpoint, {
            ...options,
            method: 'DELETE'
        });
    }

    /**
     * Get base API URL
     */
    getBaseUrl() {
        return API_BASE_URL;
    }

    /**
     * Get uploads base URL
     */
    getUploadsUrl() {
        return API_BASE_URL.replace('/public', '/uploads');
    }

}

export default new APIClient();

