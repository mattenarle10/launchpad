/**
 * CDC Admin API and Functions
 */

import client from './client.js';
import DataTable from '../pages/table.js';
import { showSuccess, showError, showWarning } from '../utils/notifications.js';
import { openImageViewer } from '../utils/image-viewer.js';

const CDCAPI = {
    // ========== API ENDPOINTS ==========
    
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
        return client.delete(`/admin/reject/students/${id}`);
    },

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
        return client.delete(`/admin/reject/companies/${id}`);
    },

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
    },

    // ========== VERIFY STUDENTS PAGE FUNCTIONS ==========

    /**
     * Load and display unverified students in a DataTable
     */
    async loadUnverifiedStudentsTable(tableWrapperId, statElementId) {
        const tableWrapper = document.getElementById(tableWrapperId);
        tableWrapper.innerHTML = '<div class="loading"><div class="loading-spinner"></div><p>Loading students...</p></div>';

        try {
            const response = await this.getUnverifiedStudents();
            const students = response.data || [];

            // Update stats if element exists
            if (statElementId) {
                const statElement = document.getElementById(statElementId);
                if (statElement) statElement.textContent = students.length;
            }

            // Create DataTable
            const dataTable = new DataTable({
                containerId: tableWrapperId,
                columns: [
                    { key: 'id_num', label: 'ID Number', sortable: true },
                    { 
                        key: 'first_name', 
                        label: 'Name', 
                        sortable: true,
                        format: (value, row) => `${row.first_name} ${row.last_name}`
                    },
                    { key: 'email', label: 'Email', sortable: true },
                    { 
                        key: 'course', 
                        label: 'Course', 
                        sortable: true,
                        format: (value) => `<span class="course-badge ${value.toLowerCase()}">${value}</span>`
                    },
                    { key: 'company_name', label: 'Company', sortable: true },
                    { 
                        key: 'created_at', 
                        label: 'Registered', 
                        sortable: true,
                        format: (value) => this.formatDate(value)
                    }
                ],
                actions: [
                    {
                        type: 'view',
                        label: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>View',
                        onClick: (row) => this.viewStudentModal(row)
                    },
                    {
                        type: 'approve',
                        label: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;"><polyline points="20 6 9 17 4 12"></polyline></svg>Approve',
                        onClick: (row) => this.approveStudentWithConfirm(row, () => this.loadUnverifiedStudentsTable(tableWrapperId, statElementId))
                    },
                    {
                        type: 'reject',
                        label: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>Reject',
                        onClick: (row) => this.rejectStudentWithConfirm(row, () => this.loadUnverifiedStudentsTable(tableWrapperId, statElementId))
                    }
                ],
                data: students,
                pageSize: 10,
                emptyMessage: 'No pending student verifications'
            });

            return { dataTable, students };

        } catch (error) {
            console.error('Error loading students:', error);
            tableWrapper.innerHTML = `
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                    </div>
                    <div class="empty-state-text">Error loading students</div>
                    <div class="empty-state-subtext">${error.message}</div>
                </div>
            `;
            showError('Failed to load students: ' + error.message);
            throw error;
        }
    },

    /**
     * Show student details in modal
     */
    viewStudentModal(student, modalId = 'view-modal') {
        const modal = document.getElementById(modalId);
        const modalBody = document.getElementById('modal-body');

        const corUrl = `../../uploads/student_cors/${student.cor}`;
        const fileExtension = student.cor.split('.').pop().toLowerCase();
        const isImage = ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(fileExtension);

        modalBody.innerHTML = `
            <div class="detail-row">
                <span class="detail-label">ID Number:</span>
                <span class="detail-value">${student.id_num}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Name:</span>
                <span class="detail-value">${student.first_name} ${student.last_name}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Email:</span>
                <span class="detail-value">${student.email}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Course:</span>
                <span class="detail-value"><span class="course-badge ${student.course.toLowerCase()}">${student.course}</span></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Contact:</span>
                <span class="detail-value">${student.contact_num || 'N/A'}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Company:</span>
                <span class="detail-value">${student.company_name || 'N/A'}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Registered:</span>
                <span class="detail-value">${this.formatDate(student.created_at)}</span>
            </div>
            <div class="cor-preview">
                <h4>Certificate of Registration</h4>
                <div style="display: flex; gap: 10px; margin-top: 10px;">
                    ${isImage ? `
                        <button class="btn-action btn-view" onclick="window.viewCORImage('${corUrl}', '${student.id_num} - COR')">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                            View COR
                        </button>
                    ` : ''}
                    <a href="${corUrl}" target="_blank" class="btn-action" style="text-decoration: none;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="7 10 12 15 17 10"></polyline>
                            <line x1="12" y1="15" x2="12" y2="3"></line>
                        </svg>
                        ${isImage ? 'Download' : 'Download COR'}
                    </a>
                </div>
            </div>
        `;

        // Set up modal buttons
        document.getElementById('modal-approve-btn').onclick = () => {
            modal.style.display = 'none';
            this.approveStudentWithConfirm(student);
        };

        document.getElementById('modal-reject-btn').onclick = () => {
            modal.style.display = 'none';
            this.rejectStudentWithConfirm(student);
        };

        modal.style.display = 'block';
    },

    /**
     * Approve student with confirmation
     */
    async approveStudentWithConfirm(student, onSuccess) {
        if (!confirm(`Approve ${student.first_name} ${student.last_name}?\n\nThis will verify the student and allow them to access the system.`)) {
            return;
        }

        try {
            await this.verifyStudent(student.student_id);
            showSuccess(`${student.first_name} ${student.last_name} has been verified successfully!`);
            
            if (onSuccess) {
                setTimeout(() => onSuccess(), 1000);
            }
        } catch (error) {
            console.error('Error approving student:', error);
            showError('Failed to approve student: ' + error.message);
        }
    },

    /**
     * Reject student with confirmation
     */
    async rejectStudentWithConfirm(student, onSuccess) {
        const reason = prompt(`Why are you rejecting ${student.first_name} ${student.last_name}?\n\n(This is optional but recommended for record keeping)`);
        
        if (reason === null) return;

        if (!confirm(`Are you sure you want to reject ${student.first_name} ${student.last_name}?\n\nThis action cannot be undone.`)) {
            return;
        }

        try {
            await this.rejectStudent(student.student_id);
            showWarning(`${student.first_name} ${student.last_name}'s registration has been rejected.`);
            
            if (onSuccess) {
                setTimeout(() => onSuccess(), 1000);
            }
        } catch (error) {
            console.error('Error rejecting student:', error);
            showError('Failed to reject student: ' + error.message);
        }
    },

    /**
     * Format date helper
     */
    formatDate(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric' 
        });
    }
};

export default CDCAPI;

