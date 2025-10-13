/**
 * Report API
 */

import client from './client.js';

const ReportAPI = {
    /**
     * Get pending reports
     */
    async getPendingReports(page = 1, pageSize = 100) {
        return client.get(`/admin/reports/pending?page=${page}&pageSize=${pageSize}`);
    },

    /**
     * Get approved reports
     */
    async getApprovedReports(page = 1, pageSize = 100) {
        return client.get(`/admin/reports/approved?page=${page}&pageSize=${pageSize}`);
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

    /**
     * Approve report with custom hours
     */
    async approveReport(reportId) {
        return this.reviewReport(reportId, 'approve');
    },

    /**
     * Approve report with specific hours
     */
    async approveReportWithHours(reportId, hours) {
        return client.post(`/admin/reports/${reportId}/review`, {
            action: 'approve',
            approved_hours: hours
        });
    },

    /**
     * Reject report
     */
    async rejectReport(reportId, reason) {
        return this.reviewReport(reportId, 'reject', reason);
    }
};

export default ReportAPI;
