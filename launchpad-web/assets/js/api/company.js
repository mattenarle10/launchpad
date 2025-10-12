/**
 * Company API
 */

import client from './client.js';

const CompanyAPI = {
    /**
     * Register new company
     */
    async register(formData) {
        return client.post('/companies/register', formData, { skipAuth: true });
    },

    /**
     * Get company profile
     */
    async getProfile(id) {
        return client.get(`/companies/${id}`);
    },

    /**
     * Get all companies
     */
    async getAll(page = 1, pageSize = 10) {
        return client.get(`/companies?page=${page}&pageSize=${pageSize}`);
    }
};

export default CompanyAPI;

