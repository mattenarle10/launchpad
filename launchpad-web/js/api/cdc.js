/**
 * CDC Admin API and Functions
 */

import client from './client.js';
import DataTable from '../pages/table.js';
import { showSuccess, showError, showWarning } from '../utils/notifications.js';
import { openFileViewer } from '../utils/file-viewer.js';
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
     * @param {number} id - Student ID
     * @param {number} companyId - Company ID to attach student to
     */
    async verifyStudent(id, companyId) {
        return client.post(`/admin/verify/students/${id}`, { company_id: companyId });
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
     * Get unverified CDC users
     */
    async getUnverifiedCdcUsers() {
        return client.get('/admin/unverified/cdc');
    },

    /**
     * Verify CDC user
     */
    async verifyCdcUser(id) {
        return client.post(`/admin/verify/cdc/${id}`, {});
    },

    /**
     * Reject CDC user
     */
    async rejectCdcUser(id) {
        return client.delete(`/admin/reject/cdc/${id}`);
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
                        label: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>Decline',
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
     * Load and display all evaluated students in a DataTable (CDC view)
     */
    async loadEvaluatedStudentsTable(tableWrapperId, statElementId) {
        const tableWrapper = document.getElementById(tableWrapperId);
        tableWrapper.innerHTML = '<div class="loading"><div class="loading-spinner"></div><p>Loading evaluated students...</p></div>';

        try {
            const response = await client.get('/admin/evaluated/students?pageSize=1000');
            const students = response.data?.students || response.data || [];
            const summary = response.data?.summary || null;

            // Update stats if element exists
            if (statElementId) {
                const statElement = document.getElementById(statElementId);
                if (statElement) statElement.textContent = students.length;
            }

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
                        key: 'company_name',
                        label: 'Partner Company',
                        sortable: true,
                        format: (value) => value || '<span style="color: #9CA3AF;">Not assigned</span>'
                    },
                    {
                        key: 'ojt_status',
                        label: 'OJT Status',
                        sortable: true,
                        format: (value) => {
                            if (!value) return '<span class="status-badge pending">Not Started</span>';
                            const normalized = value === 'in_progress' ? 'ongoing' : value;
                            const statusClass = normalized === 'completed' ? 'completed' : normalized === 'ongoing' ? 'ongoing' : 'pending';
                            const text = normalized === 'not_started' ? 'Not Started' : normalized.charAt(0).toUpperCase() + normalized.slice(1);
                            return `<span class="status-badge ${statusClass}">${text}</span>`;
                        }
                    },
                    {
                        key: 'evaluation_rank',
                        label: 'Score',
                        sortable: true,
                        format: (value) => {
                            if (value === null || value === undefined) {
                                return '<span style="color: #9CA3AF;">Not Evaluated</span>';
                            }
                            const score = parseInt(value, 10);
                            const color = score >= 80 ? '#10B981' : score >= 60 ? '#F59E0B' : '#EF4444';
                            return `<span style="color: ${color}; font-weight: 600;">${score}/100</span>`;
                        }
                    },
                    {
                        key: 'performance_score',
                        label: 'Performance',
                        sortable: true,
                        format: (value) => {
                            if (!value) return '<span style="color: #9CA3AF;">--</span>';
                            const colors = {
                                'Excellent': '#10B981',
                                'Good': '#6366F1',
                                'Satisfactory': '#F59E0B',
                                'Needs Improvement': '#F97316',
                                'Poor': '#EF4444'
                            };
                            return `<span style="color: ${colors[value] || '#6B7280'}; font-weight: 500;">${value}</span>`;
                        }
                    }
                ],
                data: students,
                pageSize: 10,
                emptyMessage: 'No evaluated students found'
            });

            return { dataTable, students, summary };

        } catch (error) {
            console.error('Error loading evaluated students:', error);
            tableWrapper.innerHTML = `
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                    </div>
                    <div class="empty-state-text">Error loading evaluated students</div>
                    <div class="empty-state-subtext">${error.message}</div>
                </div>
            `;
            showError('Failed to load evaluated students: ' + error.message);
            throw error;
        }
    },

    /**
     * Load and display unverified CDC users in a DataTable
     */
    async loadUnverifiedCdcUsersTable(tableWrapperId, statElementId) {
        const tableWrapper = document.getElementById(tableWrapperId);
        tableWrapper.innerHTML = '<div class="loading"><div class="loading-spinner"></div><p>Loading CDC accounts...</p></div>';

        try {
            const response = await this.getUnverifiedCdcUsers();
            const users = response.data || [];

            // Update stats if element exists
            if (statElementId) {
                const statElement = document.getElementById(statElementId);
                if (statElement) statElement.textContent = users.length;
            }

            const dataTable = new DataTable({
                containerId: tableWrapperId,
                columns: [
                    { key: 'username', label: 'Username', sortable: true },
                    {
                        key: 'first_name',
                        label: 'Name',
                        sortable: true,
                        format: (value, row) => `${row.first_name} ${row.last_name}`
                    },
                    { key: 'email', label: 'Email', sortable: true },
                    {
                        key: 'created_at',
                        label: 'Requested',
                        sortable: true,
                        format: (value) => this.formatDate(value)
                    }
                ],
                actions: [
                    {
                        type: 'approve',
                        label: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;"><polyline points="20 6 9 17 4 12"></polyline></svg>Approve',
                        onClick: (row) => this.approveCdcUserWithConfirm(row, () => this.loadUnverifiedCdcUsersTable(tableWrapperId, statElementId))
                    },
                    {
                        type: 'reject',
                        label: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>Decline',
                        onClick: (row) => this.rejectCdcUserWithConfirm(row, () => this.loadUnverifiedCdcUsersTable(tableWrapperId, statElementId))
                    }
                ],
                data: users,
                pageSize: 10,
                emptyMessage: 'No pending CDC account requests'
            });

            return { dataTable, users };

        } catch (error) {
            console.error('Error loading CDC accounts:', error);
            tableWrapper.innerHTML = `
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                    </div>
                    <div class="empty-state-text">Error loading CDC accounts</div>
                    <div class="empty-state-subtext">${error.message}</div>
                </div>
            `;
            showError('Failed to load CDC accounts: ' + error.message);
            throw error;
        }
    },

    /**
     * Approve CDC user with confirmation
     */
    async approveCdcUserWithConfirm(user, onSuccess) {
        if (!confirm(`Verify CDC account for ${user.first_name} ${user.last_name} (${user.username})?`)) {
            return;
        }

        try {
            await this.verifyCdcUser(user.id);
            showSuccess(`CDC account for ${user.first_name} ${user.last_name} has been verified.`);
            if (onSuccess) {
                setTimeout(() => onSuccess(), 1000);
            }
        } catch (error) {
            console.error('Error verifying CDC user:', error);
            showError('Failed to verify CDC user: ' + error.message);
        }
    },

    /**
     * Reject CDC user with confirmation
     */
    async rejectCdcUserWithConfirm(user, onSuccess) {
        if (!confirm(`Reject CDC account request for ${user.first_name} ${user.last_name} (${user.username})?\n\nThis action cannot be undone.`)) {
            return;
        }

        try {
            await this.rejectCdcUser(user.id);
            showWarning(`CDC account request for ${user.first_name} ${user.last_name} has been rejected.`);
            if (onSuccess) {
                setTimeout(() => onSuccess(), 1000);
            }
        } catch (error) {
            console.error('Error rejecting CDC user:', error);
            showError('Failed to reject CDC user: ' + error.message);
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
                <span class="detail-label">Registered:</span>
                <span class="detail-value">${this.formatDate(student.created_at)}</span>
            </div>
            <div style="background: #FFFBEB; border-left: 4px solid #F59E0B; padding: 12px; border-radius: 6px; margin-top: 16px;">
                <p style="margin: 0; color: #92400E; font-size: 13px;">
                    <strong>Note:</strong> Company will be assigned during verification.
                </p>
            </div>
            <div class="cor-preview">
                <h4>Certificate of Registration</h4>
                <div style="display: flex; gap: 10px; margin-top: 10px; justify-content: center;">
                    <button class="btn-action btn-view" onclick="window.viewCORFile('${corUrl}', '${student.id_num} - COR')">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                        View COR
                    </button>
                    <button class="btn-action btn-download" onclick="window.downloadCORFile('${corUrl}', '${student.cor}')">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="7 10 12 15 17 10"></polyline>
                            <line x1="12" y1="15" x2="12" y2="3"></line>
                        </svg>
                        Download
                    </button>
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
                Decline
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

            // Setup file viewer and download
            window.viewCORFile = (fileUrl, title) => {
                openFileViewer(fileUrl, title);
            };
            
            window.downloadCORFile = async (fileUrl, fileName) => {
                try {
                    const response = await fetch(fileUrl);
                    if (!response.ok) throw new Error('File not found');
                    
                    const blob = await response.blob();
                    const url = window.URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    link.href = url;
                    link.download = fileName;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    window.URL.revokeObjectURL(url);
                    
                    showSuccess('File downloaded successfully');
                } catch (error) {
                    console.error('Download error:', error);
                    showError('Failed to download file');
                }
            };
        }, 0);
    },

    /**
     * Approve student with company selection
     */
    async approveStudentWithConfirm(student, onSuccess) {
        // Import CompanyAPI dynamically
        const CompanyAPI = (await import('./company.js')).default;
        
        try {
            // Fetch all verified companies
            const response = await CompanyAPI.getAll();
            const companies = response.data || [];
            
            if (companies.length === 0) {
                showError('No verified companies available. Please verify a company first.');
                return;
            }
            
            // Create company selection modal
            const companiesOptions = companies.map(c => 
                `<option value="${c.company_id}">${c.company_name}</option>`
            ).join('');
            
            const content = `
                <div style="padding: 10px 0;">
                    <p style="margin-bottom: 20px; color: #6B7280; line-height: 1.6;">
                        Select a partner company to assign <strong>${student.first_name} ${student.last_name}</strong> to:
                    </p>
                    <div class="form-group">
                        <label for="company-select" style="display: block; margin-bottom: 10px; font-weight: 600; color: #374151; font-size: 14px;">
                            Partner Company <span style="color: #EF4444;">*</span>
                        </label>
                        <select id="company-select" class="custom-select" required>
                            <option value="" disabled selected>-- Select a company --</option>
                            ${companiesOptions}
                        </select>
                    </div>
                    <div style="background: #EFF6FF; border-left: 4px solid #3B82F6; padding: 14px 16px; border-radius: 8px; margin-top: 20px;">
                        <p style="margin: 0; color: #1E40AF; font-size: 13px; line-height: 1.5;">
                            <strong>💼 Note:</strong> The student will be associated with the selected company for their OJT placement.
                        </p>
                    </div>
                </div>
            `;
            
            const modal = createModal('approve-student-modal', {
                title: `Verify Student - ${student.first_name} ${student.last_name}`,
                size: 'medium'
            });
            
            const footer = `
                <button class="btn-modal" data-modal-close>Cancel</button>
                <button class="btn-modal btn-approve" id="confirm-approve-btn">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    Verify Student
                </button>
            `;
            
            modal.setFooter(footer);
            modal.open(content);
            
            // Set up approve button handler
            setTimeout(() => {
                const companySelect = document.getElementById('company-select');
                
                document.getElementById('confirm-approve-btn')?.addEventListener('click', async () => {
                    const companyId = parseInt(companySelect.value, 10);
                    
                    if (!companyId || isNaN(companyId) || companyId <= 0) {
                        showError('Please select a company');
                        companySelect.classList.add('error');
                        companySelect.focus();
                        return;
                    }
                    
                    // Remove error class if valid
                    companySelect.classList.remove('error');
                    
                    try {
                        await this.verifyStudent(student.student_id, companyId);
                        modal.close();
                        showSuccess(`${student.first_name} ${student.last_name} has been verified and assigned to ${companySelect.options[companySelect.selectedIndex].text}!`);
                        
                        if (onSuccess) {
                            setTimeout(() => onSuccess(), 1000);
                        }
                    } catch (error) {
                        console.error('Error approving student:', error);
                        showError('Failed to approve student: ' + error.message);
                    }
                });
            }, 0);
            
        } catch (error) {
            console.error('Error loading companies:', error);
            showError('Failed to load companies: ' + error.message);
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
                        key: 'company_name',
                        label: 'Partner Company',
                        sortable: true,
                        format: (value) => {
                            if (!value) return '<span style="color: #9CA3AF;">Not assigned</span>';
                            return value;
                        }
                    },
                    { 
                        key: 'specialization', 
                        label: 'Specializations', 
                        sortable: true,
                        format: (value) => {
                            if (!value) return '<span style="color: #9CA3AF;">Not specified</span>';
                            const specs = value.split(',').map(s => s.trim()).slice(0, 3);
                            return specs.map(s => `<span class="tag-badge" style="background: #EDE9FE; color: #7C3AED; padding: 2px 8px; border-radius: 4px; font-size: 11px; margin-right: 4px; display: inline-block;">${s}</span>`).join('');
                        }
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
        const evaluationRank = student.evaluation_rank || 0;
        const performanceScore = student.performance_score || 'Not Assessed';
        const hasEvaluation = evaluationRank > 0;
        
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
            
            <!-- Evaluation Scores Section -->
            <div style="border-top: 2px solid #E5E7EB; margin: 20px 0; padding-top: 20px;">
                <h4 style="font-weight: 600; color: #3D5A7E; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path>
                    </svg>
                    Performance Evaluation
                </h4>
                ${hasEvaluation ? `
                    <div style="background: linear-gradient(135deg, #3D5A7E 0%, #4A6491 100%); padding: 20px; border-radius: 12px; color: white; margin-bottom: 16px;">
                        <div style="text-align: center;">
                            <div style="font-size: 12px; opacity: 0.9; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 1px;">Average Score</div>
                            <div style="font-size: 48px; font-weight: 700; margin-bottom: 8px;">${evaluationRank}</div>
                            <div style="font-size: 18px; font-weight: 600; background: rgba(255,255,255,0.2); padding: 6px 16px; border-radius: 20px; display: inline-block;">
                                ${performanceScore}
                            </div>
                        </div>
                    </div>
                    <div style="background: #F3F4F6; padding: 12px; border-radius: 8px; font-size: 13px; color: #6B7280;">
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="16" x2="12" y2="12"></line>
                                <line x1="12" y1="8" x2="12.01" y2="8"></line>
                            </svg>
                            <span>Score is automatically calculated from company evaluations</span>
                        </div>
                    </div>
                ` : `
                    <div style="text-align: center; padding: 30px; background: #F9FAFB; border-radius: 8px; color: #9CA3AF;">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-bottom: 12px; opacity: 0.5;">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path>
                        </svg>
                        <p style="margin: 0; font-size: 14px;">No evaluations yet</p>
                    </div>
                `}
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
    async editStudent(student, onSuccess) {
        // Load companies for dropdown
        let companies = [];
        try {
            const res = await client.get('/admin/companies');
            companies = res.data?.data || [];
        } catch (error) {
            console.error('Error loading companies:', error);
        }

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
                    <label for="company_id">Assign to Company</label>
                    <select id="company_id" name="company_id">
                        <option value="">-- No Company Assigned --</option>
                        ${companies.map(company => `
                            <option value="${company.company_id}" ${student.company_id === company.company_id ? 'selected' : ''}>
                                ${company.company_name}
                            </option>
                        `).join('')}
                    </select>
                </div>
            </form>
        `;

        const modal = createModal('edit-student-modal', {
            title: `Edit Student - ${student.first_name} ${student.last_name}`,
            size: 'medium'
        });

        const footer = `
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
            // Cancel button handler
            document.querySelector('[data-modal-close]')?.addEventListener('click', () => {
                modal.close();
            });
            
            // Save button handler
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
        }, 100);
    },

    /**
     * Delete student
     */
    deleteStudentWithConfirm(student, onSuccess) {
        const content = `
            <div style="text-align: center; padding: 20px 0;">
                <div style="color: #EF4444; margin-bottom: 16px;">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block;">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                </div>
                <h3 style="margin: 0 0 16px 0; color: #111827;">Delete Student?</h3>
                <p style="margin: 0 0 16px 0; color: #6B7280; line-height: 1.5;">
                    Are you sure you want to delete <strong>${student.first_name} ${student.last_name}</strong>?
                </p>
                <div style="background: #FEF2F2; border-left: 4px solid #EF4444; padding: 12px; border-radius: 6px; text-align: left; margin-bottom: 16px;">
                    <p style="margin: 0 0 8px 0; font-weight: 600; color: #991B1B; font-size: 14px;">This action will also delete:</p>
                    <ul style="margin: 0; padding-left: 20px; color: #991B1B; font-size: 14px;">
                        <li>All daily reports</li>
                        <li>OJT progress records</li>
                    </ul>
                </div>
                <p style="margin: 0; color: #EF4444; font-weight: 600; font-size: 14px;">This action cannot be undone!</p>
            </div>
        `;

        const modal = createModal('delete-student-modal', {
            title: 'Confirm Deletion',
            size: 'small'
        });

        const footer = `
            <button class="btn-modal" data-modal-close>No, Cancel</button>
            <button class="btn-modal btn-reject" id="confirm-delete-btn">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;">
                    <polyline points="3 6 5 6 21 6"></polyline>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                </svg>
                Yes, Delete
            </button>
        `;

        modal.setFooter(footer);
        modal.open(content);

        setTimeout(() => {
            document.getElementById('confirm-delete-btn')?.addEventListener('click', async () => {
                try {
                    await this.deleteStudent(student.student_id);
                    modal.close();
                    showSuccess(`${student.first_name} ${student.last_name} has been deleted successfully.`);
                    
                    if (onSuccess) {
                        setTimeout(() => onSuccess(), 1000);
                    }
                } catch (error) {
                    console.error('Error deleting student:', error);
                    showError('Failed to delete student: ' + error.message);
                }
            });
        }, 0);
    }
};

export default CDCAPI;

