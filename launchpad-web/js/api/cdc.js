/**
 * CDC Admin API
 */

import client from './client.js';

const CDCAPI = {
    // ========== STUDENT VERIFICATION ==========
    
    /**
     * Get unverified students
     */
    async getUnverifiedStudents() {
        return client.get('/admin/unverified/students');
    },

    /**
     * Verify student
     */
    async verifyStudent(id) {
        return client.post(`/admin/verify/students/${id}`, {});
    },

    /**
     * Reject student
     */
    async rejectStudent(id) {
        return client.post(`/admin/reject/students/${id}`, {});
    },

    // ========== COMPANY VERIFICATION ==========
    
    /**
     * Get unverified companies
     */
    async getUnverifiedCompanies() {
        return client.get('/admin/unverified/companies');
    },

    /**
     * Verify company
     */
    async verifyCompany(id) {
        return client.post(`/admin/verify/companies/${id}`, {});
    },

    /**
     * Reject company
     */
    async rejectCompany(id) {
        return client.post(`/admin/reject/companies/${id}`, {});
    },

    // ========== REPORT MANAGEMENT ==========
    
    /**
     * Get pending reports
     */
    async getPendingReports() {
        return client.get('/admin/reports/pending');
    },

    /**
     * Review report (approve or reject)
     */
    async reviewReport(reportId, action, rejectionReason = null) {
        return client.post(`/admin/reports/${reportId}/review`, {
            action,
            rejection_reason: rejectionReason
        });
    },

    // ========== OJT PROGRESS ==========
    
    /**
     * Get all students' OJT progress
     */
    async getAllOJTProgress(status = null) {
        const query = status ? `?status=${status}` : '';
        return client.get(`/admin/ojt/progress${query}`);
    },

    /**
     * Get OJT dashboard statistics
     */
    async getOJTStats() {
        return client.get('/admin/ojt/stats');
    }
};

export default CDCAPI;

