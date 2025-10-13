/**
 * CDC Admin API and Functions
 */

import client from './client.js';
import DataTable from '../pages/table.js';
import { showSuccess, showError, showWarning } from '../utils/notifications.js';
import { openImageViewer } from '../utils/image-viewer.js';
import { createModal } from '../utils/modal.js';

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

    /**
     * Get all verified students
     */
    async getAllStudents(page = 1, pageSize = 100) {
        return client.get(`/students?page=${page}&pageSize=${pageSize}`);
    },

    /**
     * Update student information
     */
    async updateStudent(id, data) {
        return client.put(`/admin/students/${id}`, data);
    },

    /**
     * Delete student
     */
    async deleteStudent(id) {
        return client.delete(`/admin/students/${id}`);
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
    viewStudentModal(student) {
        const corUrl = `../../../launchpad-api/uploads/student_cors/${student.cor}`;
        const fileExtension = student.cor.split('.').pop().toLowerCase();
        const isImage = ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(fileExtension);

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
                <div style="display: flex; gap: 10px; margin-top: 10px; justify-content: center;">
                    ${isImage ? `
                        <button class="btn-action btn-view" onclick="window.viewCORImage('${corUrl}', '${student.id_num} - COR')">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                            View COR
                        </button>
                    ` : ''}

                </div>
            </div>
        `;

        // Create modal
        const modal = createModal('student-verification-modal', {
            title: 'Student Verification Details',
            size: 'medium'
        });

        // Set custom footer with action buttons
        const footer = `
            <button class="btn-modal btn-reject" id="modal-reject-btn">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
                Reject
            </button>
            <button class="btn-modal btn-approve" id="modal-approve-btn">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
                Approve
            </button>
        `;

        modal.setFooter(footer);
        modal.open(content);

        // Set up modal buttons after modal is opened
        setTimeout(() => {
            document.getElementById('modal-approve-btn')?.addEventListener('click', () => {
                modal.close();
                this.approveStudentWithConfirm(student);
            });

            document.getElementById('modal-reject-btn')?.addEventListener('click', () => {
                modal.close();
                this.rejectStudentWithConfirm(student);
            });

            // Setup image viewer
            window.viewCORImage = (imageUrl, title) => {
                openImageViewer(imageUrl, title);
            };
        }, 0);
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
    },

    // ========== STUDENTS PAGE FUNCTIONS ==========

    /**
     * Load and display all verified students in a DataTable
     */
    async loadStudentsTable(tableWrapperId, statElementId) {
        const tableWrapper = document.getElementById(tableWrapperId);
        tableWrapper.innerHTML = '<div class="loading"><div class="loading-spinner"></div><p>Loading students...</p></div>';

        try {
            const response = await this.getAllStudents();
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
                    { 
                        key: 'ojt_status', 
                        label: 'OJT Status', 
                        sortable: true,
                        format: (value) => {
                            if (!value) return '<span class="status-badge pending">Not Started</span>';
                            const statusClass = value === 'completed' ? 'completed' : value === 'ongoing' ? 'ongoing' : 'pending';
                            return `<span class="status-badge ${statusClass}">${value || 'N/A'}</span>`;
                        }
                    },
                    { 
                        key: 'verified_at', 
                        label: 'Verified', 
                        sortable: true,
                        format: (value) => this.formatDate(value)
                    }
                ],
                actions: [
                    {
                        type: 'view',
                        label: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>View',
                        onClick: (row) => this.viewStudentDetailsModal(row)
                    },
                    {
                        type: 'edit',
                        label: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>Edit',
                        onClick: (row) => this.editStudent(row, () => this.loadStudentsTable(tableWrapperId, statElementId))
                    },
                    {
                        type: 'delete',
                        label: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>Delete',
                        onClick: (row) => this.deleteStudentWithConfirm(row, () => this.loadStudentsTable(tableWrapperId, statElementId))
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
     * Show student details in modal (for verified students)
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
                <span class="detail-label">Verified:</span>
                <span class="detail-value">${this.formatDate(student.verified_at)}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">OJT Status:</span>
                <span class="detail-value">
                    ${student.ojt_status ? `
                        <span class="status-badge ${student.ojt_status === 'completed' ? 'completed' : 'ongoing'}">${student.ojt_status}</span>
                        <br><small>${student.completed_hours || 0} / ${student.required_hours || 0} hours</small>
                    ` : '<span class="status-badge pending">Not Started</span>'}
                </span>
            </div>
        `;

        // Create modal
        const modal = createModal('student-details-modal', {
            title: `${student.first_name} ${student.last_name} - Details`,
            size: 'medium'
        });

        modal.open(content);
    },

    /**
     * Edit student
     */
    editStudent(student, onSuccess) {
        const content = `
            <form id="edit-student-form" class="modal-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" value="${student.first_name}" required>
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" value="${student.last_name}" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="${student.email}" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="course">Course</label>
                        <select id="course" name="course" required>
                            <option value="IT" ${student.course === 'IT' ? 'selected' : ''}>IT</option>
                            <option value="COMSCI" ${student.course === 'COMSCI' ? 'selected' : ''}>Computer Science</option>
                            <option value="EMC" ${student.course === 'EMC' ? 'selected' : ''}>EMC</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="contact_num">Contact Number</label>
                        <input type="text" id="contact_num" name="contact_num" value="${student.contact_num || ''}" placeholder="09171234567">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="company_name">Company</label>
                    <input type="text" id="company_name" name="company_name" value="${student.company_name || ''}" placeholder="Company Name">
                </div>
            </form>
        `;

        const modal = createModal('edit-student-modal', {
            title: `Edit Student - ${student.first_name} ${student.last_name}`,
            size: 'medium'
        });

        const footer = `
            <button class="btn-modal" data-modal-close>Cancel</button>
            <button class="btn-modal btn-approve" id="save-student-btn">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;">
                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                    <polyline points="17 21 17 13 7 13 7 21"></polyline>
                    <polyline points="7 3 7 8 15 8"></polyline>
                </svg>
                Save Changes
            </button>
        `;

        modal.setFooter(footer);
        modal.open(content);

        setTimeout(() => {
            document.getElementById('save-student-btn')?.addEventListener('click', async () => {
                const form = document.getElementById('edit-student-form');
                const formData = new FormData(form);
                const data = Object.fromEntries(formData);

                // Validate
                if (!data.first_name || !data.last_name || !data.email || !data.course) {
                    showError('Please fill in all required fields');
                    return;
                }

                try {
                    await this.updateStudent(student.student_id, data);
                    showSuccess(`${data.first_name} ${data.last_name} updated successfully!`);
                    modal.close();
                    
                    if (onSuccess) {
                        setTimeout(() => onSuccess(), 1000);
                    }
                } catch (error) {
                    console.error('Error updating student:', error);
                    showError('Failed to update student: ' + error.message);
                }
            });
        }, 0);
    },

    /**
     * Delete student
     */
    async deleteStudentWithConfirm(student, onSuccess) {
        if (!confirm(`Are you sure you want to delete ${student.first_name} ${student.last_name}?\n\nThis action will also delete:\n- All daily reports\n- OJT progress records\n\nThis cannot be undone!`)) {
            return;
        }

        try {
            await this.deleteStudent(student.student_id);
            showSuccess(`${student.first_name} ${student.last_name} has been deleted successfully.`);
            
            if (onSuccess) {
                setTimeout(() => onSuccess(), 1000);
            }
        } catch (error) {
            console.error('Error deleting student:', error);
            showError('Failed to delete student: ' + error.message);
        }
    }
};

export default CDCAPI;

