/**
 * Student API and Functions
 */

import client from './client.js';
import DataTable from '../pages/table.js';
import { showSuccess, showError } from '../utils/notifications.js';
import { createModal } from '../utils/modal.js';

const StudentAPI = {
    // ========== API ENDPOINTS ==========
    
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
     * Get all students (CDC/PC)
     */
    async getAll(page = 1, pageSize = 100) {
        return client.get(`/students?page=${page}&pageSize=${pageSize}`);
    },

    // ========== PC STUDENTS PAGE FUNCTIONS ==========

    /**
     * Load and display students for Partner Company
     */
    async loadPCStudentsTable(tableWrapperId, statElementId) {
        const tableWrapper = document.getElementById(tableWrapperId);
        tableWrapper.innerHTML = '<div class="loading"><div class="loading-spinner"></div><p>Loading students...</p></div>';

        try {
            const response = await this.getAll();
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
                    { key: 'course', label: 'Course', sortable: true },
                    { key: 'email', label: 'Email', sortable: true },
                    { 
                        key: 'company_name', 
                        label: 'Company', 
                        sortable: true,
                        format: (value) => value || 'N/A'
                    },
                    { 
                        key: 'ojt_status', 
                        label: 'OJT Status', 
                        sortable: true,
                        format: (value) => {
                            if (value === 'ongoing') return '<span class="status-badge ongoing">Ongoing</span>';
                            if (value === 'completed') return '<span class="status-badge completed">Completed</span>';
                            return '<span class="status-badge pending">Not Started</span>';
                        }
                    },
                    {
                        key: 'performance_score',
                        label: 'Performance Score',
                        sortable: true,
                        format: (value) => value ? `<strong style="color: var(--primary-blue);">${value}%</strong>` : '<span style="color: #9CA3AF;">N/A</span>'
                    },
                    {
                        key: 'evaluation_rank',
                        label: 'Evaluation Rank',
                        sortable: true,
                        format: (value) => {
                            if (value === 'Excellent') return '<span class="status-badge completed">Excellent</span>';
                            if (value === 'Good') return '<span class="status-badge ongoing">Good</span>';
                            if (value === 'Fair') return '<span class="status-badge pending">Fair</span>';
                            if (value === 'Poor') return '<span class="status-badge">Poor</span>';
                            return '<span style="color: #9CA3AF;">Not Evaluated</span>';
                        }
                    }
                ],
                actions: [
                    {
                        type: 'view',
                        label: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>View',
                        onClick: (row) => this.viewStudentDetailsModal(row)
                    }
                ],
                data: students,
                pageSize: 10,
                emptyMessage: 'No students found'
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
     * Show student details in modal (PC view)
     */
    viewStudentDetailsModal(student) {
        const content = `
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
                <span class="detail-value">${student.course}</span>
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
                <span class="detail-label">OJT Status:</span>
                <span class="detail-value">
                    ${student.ojt_status === 'ongoing' ? '<span class="status-badge ongoing">Ongoing</span>' : 
                      student.ojt_status === 'completed' ? '<span class="status-badge completed">Completed</span>' : 
                      '<span class="status-badge pending">Not Started</span>'}
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Required Hours:</span>
                <span class="detail-value">${student.required_hours || 'N/A'} hrs</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Completed Hours:</span>
                <span class="detail-value">${student.completed_hours || '0'} hrs</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Performance Score:</span>
                <span class="detail-value"><strong style="color: var(--primary-blue);">${student.performance_score || 'Not Evaluated'}${student.performance_score ? '%' : ''}</strong></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Evaluation Rank:</span>
                <span class="detail-value">
                    ${student.evaluation_rank === 'Excellent' ? '<span class="status-badge completed">Excellent</span>' : 
                      student.evaluation_rank === 'Good' ? '<span class="status-badge ongoing">Good</span>' : 
                      student.evaluation_rank === 'Fair' ? '<span class="status-badge pending">Fair</span>' :
                      student.evaluation_rank === 'Poor' ? '<span class="status-badge">Poor</span>' :
                      '<span style="color: #9CA3AF;">Not Evaluated</span>'}
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Verified:</span>
                <span class="detail-value">${this.formatDate(student.verified_at)}</span>
            </div>
        `;

        const modal = createModal('student-details-modal', {
            title: `${student.first_name} ${student.last_name} - Details`,
            size: 'medium'
        });

        modal.open(content);
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

export default StudentAPI;

