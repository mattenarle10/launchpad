/**
 * CDC Job Postings Page
 * View all job postings and delete if needed
 */

import { loadSidebar, loadUserInfo } from '../../components.js';
import { initUserDropdown } from '../dropdown.js';
import client from '../../api/client.js';
import { showError, showSuccess } from '../../utils/notifications.js';
import { getSidebarMode } from '../../utils/sidebar-helper.js';
import DataTable from '../table.js';
import { createModal } from '../../utils/modal.js';

let allJobs = [];
let dataTable = null;

async function loadJobsTable() {
    const tableWrapper = document.getElementById('table-wrapper');
    tableWrapper.innerHTML = '<div class="loading"><p>Loading job postings...</p></div>';

    try {
        const res = await client.get('/jobs');
        allJobs = res.data || [];

        // Create DataTable
        dataTable = new DataTable({
            containerId: 'table-wrapper',
            columns: [
                { key: 'company_name', label: 'Company', sortable: true },
                { key: 'title', label: 'Job Title', sortable: true },
                { 
                    key: 'job_type', 
                    label: 'Type', 
                    sortable: true,
                    format: (value) => `<span class="course-badge">${value}</span>`
                },
                { key: 'location', label: 'Location', sortable: true },
                { key: 'salary_range', label: 'Salary Range', sortable: true },
                { 
                    key: 'is_active', 
                    label: 'Status', 
                    sortable: true,
                    format: (value) => {
                        const statusClass = value ? 'completed' : 'pending';
                        const statusText = value ? 'Active' : 'Inactive';
                        return `<span class="status-badge ${statusClass}">${statusText}</span>`;
                    }
                },
                { 
                    key: 'created_at', 
                    label: 'Posted', 
                    sortable: true,
                    format: (value) => new Date(value).toLocaleDateString()
                }
            ],
            actions: [
                {
                    type: 'view',
                    label: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>View',
                    onClick: (row) => viewJobDetails(row)
                },
                {
                    type: 'delete',
                    label: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>Delete',
                    onClick: (row) => deleteJob(row.job_id, row.title)
                }
            ],
            data: allJobs,
            pageSize: 10,
            emptyMessage: 'No job postings available'
        });

    } catch (error) {
        console.error('Error loading jobs:', error);
        tableWrapper.innerHTML = `
            <div class="empty-state">
                <div class="empty-state-text">Error loading job postings</div>
                <div class="empty-state-subtext">${error.message}</div>
            </div>
        `;
        showError('Failed to load jobs: ' + error.message);
    }
}

function viewJobDetails(job) {
    const content = `
        <div style="padding: 10px 0;">
            <div class="detail-row" style="margin-bottom: 16px;">
                <span class="detail-label" style="font-weight: 600; color: #6B7280;">Company:</span>
                <span class="detail-value" style="color: #111827; font-weight: 500;">${job.company_name}</span>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                <div class="detail-row">
                    <span class="detail-label" style="font-weight: 600; color: #6B7280;">Job Type:</span>
                    <span class="detail-value"><span class="course-badge">${job.job_type}</span></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label" style="font-weight: 600; color: #6B7280;">Status:</span>
                    <span class="detail-value">
                        <span class="status-badge ${job.is_active ? 'completed' : 'pending'}">
                            ${job.is_active ? 'Active' : 'Inactive'}
                        </span>
                    </span>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                <div class="detail-row">
                    <span class="detail-label" style="font-weight: 600; color: #6B7280;">Location:</span>
                    <span class="detail-value" style="color: #111827;">${job.location || 'Not specified'}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label" style="font-weight: 600; color: #6B7280;">Salary Range:</span>
                    <span class="detail-value" style="color: #111827;">${job.salary_range || 'Not specified'}</span>
                </div>
            </div>

            <div class="detail-row" style="margin-bottom: 16px;">
                <span class="detail-label" style="font-weight: 600; color: #6B7280;">Posted:</span>
                <span class="detail-value" style="color: #111827;">${new Date(job.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}</span>
            </div>

            <div style="border-top: 2px solid #E5E7EB; padding-top: 16px; margin-top: 16px;">
                <h4 style="font-weight: 600; color: #111827; margin-bottom: 8px;">Description</h4>
                <p style="color: #374151; line-height: 1.6; white-space: pre-wrap;">${job.description}</p>
            </div>

            ${job.requirements ? `
            <div style="border-top: 2px solid #E5E7EB; padding-top: 16px; margin-top: 16px;">
                <h4 style="font-weight: 600; color: #111827; margin-bottom: 8px;">Requirements</h4>
                <p style="color: #374151; line-height: 1.6; white-space: pre-wrap;">${job.requirements}</p>
            </div>
            ` : ''}
        </div>
    `;
    
    const modal = createModal('job-details-modal', {
        title: job.title,
        size: 'large'
    });
    
    const footer = `
        <button class="btn-modal" data-modal-close>Close</button>
        <button class="btn-modal" style="background: #EF4444; color: white;" id="delete-from-modal-btn">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;">
                <polyline points="3 6 5 6 21 6"></polyline>
                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
            </svg>
            Delete Job
        </button>
    `;
    
    modal.setFooter(footer);
    modal.open(content);
    
    // Setup delete button
    setTimeout(() => {
        document.getElementById('delete-from-modal-btn')?.addEventListener('click', () => {
            const modalEl = document.querySelector('.modal');
            if (modalEl) modalEl.remove();
            deleteJob(job.job_id, job.title);
        });
    }, 0);
}

async function deleteJob(jobId, jobTitle) {
    if (!confirm(`Are you sure you want to delete "${jobTitle}"?\\n\\nThis will remove the job posting from all platforms.`)) {
        return;
    }

    try {
        // CDC uses admin endpoint to delete any job
        await client.delete(`/admin/jobs/${jobId}`);
        showSuccess('Job posting deleted successfully!');
        setTimeout(() => loadJobsTable(), 500);
    } catch (error) {
        console.error('Error deleting job:', error);
        showError('Failed to delete job: ' + error.message);
    }
}

document.addEventListener('DOMContentLoaded', async () => {
    // Check authentication
    if (!client.isAuthenticated()) {
        showError('Please login to access this page');
        setTimeout(() => window.location.href = '../login.html', 1500);
        return;
    }

    // Load components
    await loadSidebar('job-postings', getSidebarMode());
    loadUserInfo();
    
    // Initialize dropdown
    initUserDropdown(
        'user-menu-toggle',
        'user-dropdown',
        () => {
            client.clearAuth();
            window.location.href = '../login.html';
        },
        () => window.location.href = '../profile.html'
    );
    
    // Load jobs table
    await loadJobsTable();

    // Setup search
    document.getElementById('search-input')?.addEventListener('input', (e) => {
        dataTable.search(e.target.value);
    });
});
