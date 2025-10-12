/**
 * Student API
 */

import client from './client.js';

const StudentAPI = {
    /**
     * Register new student
     */
    async register(formData) {
        return client.post('/students/register', formData, { skipAuth: true });
    },

    /**
     * Get student profile
     */
    async getProfile(id) {
        return client.get(`/students/${id}`);
    },

    /**
     * Get student OJT progress
     */
    async getOJTProgress(studentId) {
        return client.get(`/students/${studentId}/ojt`);
    },

    /**
     * Submit daily report
     */
    async submitDailyReport(studentId, formData) {
        return client.post(`/students/${studentId}/reports/daily`, formData);
    },

    /**
     * Get daily reports
     */
    async getDailyReports(studentId, status = null) {
        const query = status ? `?status=${status}` : '';
        return client.get(`/students/${studentId}/reports/daily${query}`);
    },

    /**
     * Get all students (admin only)
     */
    async getAll(page = 1, pageSize = 10) {
        return client.get(`/students?page=${page}&pageSize=${pageSize}`);
    }
};

export default StudentAPI;

