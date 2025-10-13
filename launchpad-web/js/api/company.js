/**
 * Company API and Functions
 */

import client from './client.js';
import DataTable from '../pages/table.js';
import { showSuccess, showError, showWarning } from '../utils/notifications.js';
import { openImageViewer } from '../utils/image-viewer.js';
import { createModal } from '../utils/modal.js';

const CompanyAPI = {
    // ========== API ENDPOINTS ==========
    
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
     * Get all verified companies
     */
    async getAll(page = 1, pageSize = 100) {
        return client.get(`/companies?page=${page}&pageSize=${pageSize}`);
    },

    /**
     * Get unverified companies (CDC admin)
     */
    async getUnverified() {
        return client.get('/admin/unverified/companies');
    },

    /**
     * Verify company (CDC admin)
     */
    async verifyCompany(id) {
        return client.post(`/admin/verify/companies/${id}`, {});
    },

    /**
     * Reject company (CDC admin)
     */
    async rejectCompany(id) {
        return client.delete(`/admin/reject/companies/${id}`);
    },

    /**
     * Update company information (CDC admin)
     */
    async updateCompany(id, data) {
        return client.put(`/admin/companies/${id}`, data);
    },

    /**
     * Delete company (CDC admin)
     */
    async deleteCompany(id) {
        return client.delete(`/admin/companies/${id}`);
    },

    // ========== VERIFICATION PAGE FUNCTIONS ==========

    /**
     * Load and display unverified companies awaiting verification
     */
    async loadUnverifiedCompaniesTable(tableWrapperId, statElementId) {
        const tableWrapper = document.getElementById(tableWrapperId);
        tableWrapper.innerHTML = '<div class="loading"><div class="loading-spinner"></div><p>Loading pending companies...</p></div>';

        try {
            const response = await this.getUnverified();
            const companies = response.data || [];

            // Update stats if element exists
            if (statElementId) {
                const statElement = document.getElementById(statElementId);
                if (statElement) statElement.textContent = companies.length;
            }

            // Create DataTable
            const dataTable = new DataTable({
                containerId: tableWrapperId,
                columns: [
                    { key: 'company_name', label: 'Company Name', sortable: true },
                    { key: 'email', label: 'Email', sortable: true },
                    { key: 'contact_num', label: 'Contact', sortable: true },
                    { 
                        key: 'address', 
                        label: 'Address', 
                        sortable: true,
                        format: (value) => value || 'N/A'
                    },
                    { 
                        key: 'website', 
                        label: 'Website', 
                        sortable: true,
                        format: (value) => value ? `<a href="${value}" target="_blank" style="color: #3B82F6;">${value}</a>` : 'N/A'
                    },
                    { 
                        key: 'created_at', 
                        label: 'Submitted', 
                        sortable: true,
                        format: (value) => this.formatDate(value)
                    }
                ],
                actions: [
                    {
                        type: 'view',
                        label: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>View',
                        onClick: (row) => this.viewUnverifiedCompanyModal(row, () => this.loadUnverifiedCompaniesTable(tableWrapperId, statElementId))
                    }
                ],
                data: companies,
                pageSize: 10,
                emptyMessage: 'No pending company verifications'
            });

            return { dataTable, companies };

        } catch (error) {
            console.error('Error loading unverified companies:', error);
            tableWrapper.innerHTML = `
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                    </div>
                    <div class="empty-state-text">Error loading pending companies</div>
                    <div class="empty-state-subtext">${error.message}</div>
                </div>
            `;
            showError('Failed to load pending companies: ' + error.message);
            throw error;
        }
    },

    /**
     * Show unverified company details in modal with approve/reject actions
     */
    viewUnverifiedCompanyModal(company, onSuccess) {
        const logoUrl = company.company_logo ? `../../../launchpad-api/uploads/company_logos/${company.company_logo}` : null;
        const moaUrl = company.moa_document ? `../../../launchpad-api/uploads/company_moas/${company.moa_document}` : null;

        const content = `
            <div class="detail-row">
                <span class="detail-label">Company Name:</span>
                <span class="detail-value">${company.company_name}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Username:</span>
                <span class="detail-value">${company.username || 'N/A'}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Email:</span>
                <span class="detail-value">${company.email}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Contact:</span>
                <span class="detail-value">${company.contact_num || 'N/A'}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Address:</span>
                <span class="detail-value">${company.address || 'N/A'}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Website:</span>
                <span class="detail-value">
                    ${company.website ? `<a href="${company.website}" target="_blank" style="color: #3B82F6;">${company.website}</a>` : 'N/A'}
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Submitted:</span>
                <span class="detail-value">${this.formatDate(company.created_at)}</span>
            </div>
            ${logoUrl || moaUrl ? `
                <div class="cor-preview">
                    <h4>Documents</h4>
                    <div style="display: flex; gap: 10px; margin-top: 10px; justify-content: center;">
                        ${logoUrl ? `
                            <button class="btn-action btn-view" onclick="window.viewCompanyDocImage('${logoUrl}', '${company.company_name} - Logo')">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                    <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                    <polyline points="21 15 16 10 5 21"></polyline>
                                </svg>
                                View Logo
                            </button>
                        ` : ''}
                        ${moaUrl ? `
                            <button class="btn-action btn-view" onclick="window.viewCompanyDocImage('${moaUrl}', '${company.company_name} - MOA')">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                    <polyline points="14 2 14 8 20 8"></polyline>
                                </svg>
                                View MOA
                            </button>
                        ` : ''}
                    </div>
                </div>
            ` : ''}
        `;

        const modal = createModal('company-verification-modal', {
            title: `Verify Company - ${company.company_name}`,
            size: 'medium'
        });

        const footer = `
            <button class="btn-modal btn-reject" id="reject-company-btn">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="15" y1="9" x2="9" y2="15"></line>
                    <line x1="9" y1="9" x2="15" y2="15"></line>
                </svg>
                Reject
            </button>
            <button class="btn-modal btn-approve" id="approve-company-btn">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
                Approve
            </button>
        `;

        modal.setFooter(footer);
        modal.open(content);

        // Setup image viewer
        setTimeout(() => {
            window.viewCompanyDocImage = (imageUrl, title) => {
                openImageViewer(imageUrl, title);
            };

            // Approve handler
            document.getElementById('approve-company-btn')?.addEventListener('click', async () => {
                try {
                    await this.verifyCompany(company.company_id);
                    showSuccess(`${company.company_name} has been verified successfully!`);
                    modal.close();
                    
                    if (onSuccess) {
                        setTimeout(() => onSuccess(), 1000);
                    }
                } catch (error) {
                    console.error('Error verifying company:', error);
                    showError('Failed to verify company: ' + error.message);
                }
            });

            // Reject handler
            document.getElementById('reject-company-btn')?.addEventListener('click', async () => {
                try {
                    await this.rejectCompany(company.company_id);
                    showWarning(`${company.company_name} has been rejected.`);
                    modal.close();
                    
                    if (onSuccess) {
                        setTimeout(() => onSuccess(), 1000);
                    }
                } catch (error) {
                    console.error('Error rejecting company:', error);
                    showError('Failed to reject company: ' + error.message);
                }
            });
        }, 0);
    },

    // ========== COMPANIES PAGE FUNCTIONS ==========

    /**
     * Load and display all verified companies in a DataTable
     */
    async loadCompaniesTable(tableWrapperId, statElementId) {
        const tableWrapper = document.getElementById(tableWrapperId);
        tableWrapper.innerHTML = '<div class="loading"><div class="loading-spinner"></div><p>Loading companies...</p></div>';

        try {
            const response = await this.getAll();
            const companies = response.data || [];

            // Update stats if element exists
            if (statElementId) {
                const statElement = document.getElementById(statElementId);
                if (statElement) statElement.textContent = companies.length;
            }

            // Create DataTable
            const dataTable = new DataTable({
                containerId: tableWrapperId,
                columns: [
                    { key: 'company_name', label: 'Company Name', sortable: true },
                    { key: 'email', label: 'Email', sortable: true },
                    { key: 'contact_num', label: 'Contact', sortable: true },
                    { 
                        key: 'address', 
                        label: 'Address', 
                        sortable: true,
                        format: (value) => value || 'N/A'
                    },
                    { 
                        key: 'website', 
                        label: 'Website', 
                        sortable: true,
                        format: (value) => value ? `<a href="${value}" target="_blank" style="color: #3B82F6;">${value}</a>` : 'N/A'
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
                        onClick: (row) => this.viewCompanyDetailsModal(row)
                    },
                    {
                        type: 'edit',
                        label: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>Edit',
                        onClick: (row) => this.editCompany(row, () => this.loadCompaniesTable(tableWrapperId, statElementId))
                    },
                    {
                        type: 'delete',
                        label: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>Delete',
                        onClick: (row) => this.deleteCompanyWithConfirm(row, () => this.loadCompaniesTable(tableWrapperId, statElementId))
                    }
                ],
                data: companies,
                pageSize: 10,
                emptyMessage: 'No companies found'
            });

            return { dataTable, companies };

        } catch (error) {
            console.error('Error loading companies:', error);
            tableWrapper.innerHTML = `
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                    </div>
                    <div class="empty-state-text">Error loading companies</div>
                    <div class="empty-state-subtext">${error.message}</div>
                </div>
            `;
            showError('Failed to load companies: ' + error.message);
            throw error;
        }
    },

    /**
     * Show company details in modal
     */
    viewCompanyDetailsModal(company) {
        const logoUrl = company.company_logo ? `../../../launchpad-api/uploads/company_logos/${company.company_logo}` : null;
        const moaUrl = company.moa_document ? `../../../launchpad-api/uploads/company_moas/${company.moa_document}` : null;

        const content = `
            <div class="detail-row">
                <span class="detail-label">Company Name:</span>
                <span class="detail-value">${company.company_name}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Username:</span>
                <span class="detail-value">${company.username || 'N/A'}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Email:</span>
                <span class="detail-value">${company.email}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Contact:</span>
                <span class="detail-value">${company.contact_num || 'N/A'}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Address:</span>
                <span class="detail-value">${company.address || 'N/A'}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Website:</span>
                <span class="detail-value">
                    ${company.website ? `<a href="${company.website}" target="_blank" style="color: #3B82F6;">${company.website}</a>` : 'N/A'}
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Verified:</span>
                <span class="detail-value">${this.formatDate(company.verified_at)}</span>
            </div>
            ${logoUrl || moaUrl ? `
                <div class="cor-preview">
                    <h4>Documents</h4>
                    <div style="display: flex; gap: 10px; margin-top: 10px; justify-content: center;">
                        ${logoUrl ? `
                            <button class="btn-action btn-view" onclick="window.viewCompanyImage('${logoUrl}', '${company.company_name} - Logo')">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                    <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                    <polyline points="21 15 16 10 5 21"></polyline>
                                </svg>
                                View Logo
                            </button>
                        ` : ''}
                        ${moaUrl ? `
                            <button class="btn-action btn-view" onclick="window.viewCompanyImage('${moaUrl}', '${company.company_name} - MOA')">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                    <polyline points="14 2 14 8 20 8"></polyline>
                                </svg>
                                View MOA
                            </button>
                        ` : ''}
                    </div>
                </div>
            ` : ''}
        `;

        const modal = createModal('company-details-modal', {
            title: `${company.company_name} - Details`,
            size: 'medium'
        });

        modal.open(content);

        // Setup image viewer
        setTimeout(() => {
            window.viewCompanyImage = (imageUrl, title) => {
                openImageViewer(imageUrl, title);
            };
        }, 0);
    },

    /**
     * Edit company
     */
    editCompany(company, onSuccess) {
        const content = `
            <form id="edit-company-form" class="modal-form">
                <div class="form-group">
                    <label for="company_name">Company Name</label>
                    <input type="text" id="company_name" name="company_name" value="${company.company_name}" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="${company.email}" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="contact_num">Contact Number</label>
                        <input type="text" id="contact_num" name="contact_num" value="${company.contact_num || ''}" placeholder="09171234567">
                    </div>
                    <div class="form-group">
                        <label for="website">Website</label>
                        <input type="url" id="website" name="website" value="${company.website || ''}" placeholder="https://example.com">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea id="address" name="address" rows="3" style="padding: 10px 12px; border: 1px solid #D1D5DB; border-radius: 8px; font-size: 14px; font-family: inherit; resize: vertical;">${company.address || ''}</textarea>
                </div>
            </form>
        `;

        const modal = createModal('edit-company-modal', {
            title: `Edit Company - ${company.company_name}`,
            size: 'medium'
        });

        const footer = `
            <button class="btn-modal" data-modal-close>Cancel</button>
            <button class="btn-modal btn-approve" id="save-company-btn">
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
            document.getElementById('save-company-btn')?.addEventListener('click', async () => {
                const form = document.getElementById('edit-company-form');
                const formData = new FormData(form);
                const data = Object.fromEntries(formData);

                // Validate
                if (!data.company_name || !data.email) {
                    showError('Please fill in all required fields');
                    return;
                }

                try {
                    await this.updateCompany(company.company_id, data);
                    showSuccess(`${data.company_name} updated successfully!`);
                    modal.close();
                    
                    if (onSuccess) {
                        setTimeout(() => onSuccess(), 1000);
                    }
                } catch (error) {
                    console.error('Error updating company:', error);
                    showError('Failed to update company: ' + error.message);
                }
            });
        }, 0);
    },

    /**
     * Delete company with confirmation
     */
    deleteCompanyWithConfirm(company, onSuccess) {
        const content = `
            <div style="text-align: center; padding: 20px 0;">
                <div style="color: #EF4444; margin-bottom: 16px;">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block;">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                </div>
                <h3 style="margin: 0 0 16px 0; color: #111827;">Delete Company?</h3>
                <p style="margin: 0 0 16px 0; color: #6B7280; line-height: 1.5;">
                    Are you sure you want to delete <strong>${company.company_name}</strong>?
                </p>
                <p style="margin: 0; color: #EF4444; font-weight: 600; font-size: 14px;">This action cannot be undone!</p>
            </div>
        `;

        const modal = createModal('delete-company-modal', {
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
                    await this.deleteCompany(company.company_id);
                    modal.close();
                    showSuccess(`${company.company_name} has been deleted successfully.`);
                    
                    if (onSuccess) {
                        setTimeout(() => onSuccess(), 1000);
                    }
                } catch (error) {
                    console.error('Error deleting company:', error);
                    showError('Failed to delete company: ' + error.message);
                }
            });
        }, 0);
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

export default CompanyAPI;

