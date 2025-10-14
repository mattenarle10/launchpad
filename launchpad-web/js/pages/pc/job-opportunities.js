/**
 * Job Opportunities Page
 * Partner Company manages job postings
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
let editingJobId = null;

async function loadJobsTable() {
    const tableWrapper = document.getElementById('table-wrapper');
    tableWrapper.innerHTML = '<div class="loading"><p>Loading jobs...</p></div>';

    try {
        const res = await client.get('/jobs/company');
        allJobs = res.data || [];

        // Create DataTable
        dataTable = new DataTable({
            containerId: 'table-wrapper',
            columns: [
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
                    type: 'edit',
                    label: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>Edit',
                    onClick: (row) => openJobModal(row)
                },
                {
                    type: 'delete',
                    label: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>Delete',
                    onClick: (row) => deleteJob(row.job_id)
                }
            ],
            data: allJobs,
            pageSize: 10,
            emptyMessage: 'No job opportunities posted yet'
        });

    } catch (error) {
        console.error('Error loading jobs:', error);
        tableWrapper.innerHTML = `
            <div class="empty-state">
                <div class="empty-state-text">Error loading jobs</div>
                <div class="empty-state-subtext">${error.message}</div>
            </div>
        `;
        showError('Failed to load jobs: ' + error.message);
    }
}

function openJobModal(job = null) {
    editingJobId = job ? job.job_id : null;
    const isEdit = !!job;
    
    const content = `
        <div style="padding: 10px 0;">
            <div class="form-group" style="margin-bottom: 16px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #374151;">
                    Job Title <span style="color: #EF4444;">*</span>
                </label>
                <input 
                    type="text" 
                    id="job-title" 
                    class="form-input" 
                    value="${job?.title || ''}"
                    placeholder="e.g. Full Stack Developer"
                    style="width: 100%; padding: 12px; border: 2px solid #E5E7EB; border-radius: 8px; font-size: 14px;"
                    required
                >
            </div>

            <div class="form-group" style="margin-bottom: 16px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #374151;">
                    Description <span style="color: #EF4444;">*</span>
                </label>
                <textarea 
                    id="job-description" 
                    class="form-input" 
                    rows="4"
                    placeholder="Describe the job role and responsibilities..."
                    style="width: 100%; padding: 12px; border: 2px solid #E5E7EB; border-radius: 8px; font-size: 14px; resize: vertical;"
                    required
                >${job?.description || ''}</textarea>
            </div>

            <div class="form-group" style="margin-bottom: 16px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #374151;">
                    Requirements
                </label>
                <textarea 
                    id="job-requirements" 
                    class="form-input" 
                    rows="3"
                    placeholder="List the required skills and qualifications..."
                    style="width: 100%; padding: 12px; border: 2px solid #E5E7EB; border-radius: 8px; font-size: 14px; resize: vertical;"
                >${job?.requirements || ''}</textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                <div class="form-group">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #374151;">
                        Job Type <span style="color: #EF4444;">*</span>
                    </label>
                    <select 
                        id="job-type" 
                        class="form-input" 
                        style="width: 100%; padding: 12px; border: 2px solid #E5E7EB; border-radius: 8px; font-size: 14px;"
                        required
                    >
                        <option value="Full-time" ${job?.job_type === 'Full-time' ? 'selected' : ''}>Full-time</option>
                        <option value="Part-time" ${job?.job_type === 'Part-time' ? 'selected' : ''}>Part-time</option>
                        <option value="Contract" ${job?.job_type === 'Contract' ? 'selected' : ''}>Contract</option>
                        <option value="Internship" ${job?.job_type === 'Internship' ? 'selected' : ''}>Internship</option>
                    </select>
                </div>

                <div class="form-group">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #374151;">
                        Location
                    </label>
                    <input 
                        type="text" 
                        id="job-location" 
                        class="form-input" 
                        value="${job?.location || ''}"
                        placeholder="e.g. Cebu City, Philippines"
                        style="width: 100%; padding: 12px; border: 2px solid #E5E7EB; border-radius: 8px; font-size: 14px;"
                    >
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 16px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #374151;">
                    Salary Range
                </label>
                <input 
                    type="text" 
                    id="job-salary" 
                    class="form-input" 
                    value="${job?.salary_range || ''}"
                    placeholder="e.g. ₱25,000 - ₱35,000/month"
                    style="width: 100%; padding: 12px; border: 2px solid #E5E7EB; border-radius: 8px; font-size: 14px;"
                >
            </div>

            ${isEdit ? `
            <div class="form-group" style="margin-bottom: 16px;">
                <label style="display: flex; align-items: center; cursor: pointer;">
                    <input 
                        type="checkbox" 
                        id="job-active" 
                        ${job?.is_active ? 'checked' : ''}
                        style="width: 18px; height: 18px; margin-right: 8px; cursor: pointer;"
                    >
                    <span style="font-weight: 600; color: #374151;">Active (visible to students)</span>
                </label>
            </div>
            ` : ''}
        </div>
    `;
    
    const modal = createModal('job-modal', {
        title: isEdit ? 'Edit Job Opportunity' : 'Post New Job Opportunity',
        size: 'large'
    });
    
    const footer = `
        <button class="btn-modal" data-modal-close>Cancel</button>
        <button class="btn-modal btn-approve" id="save-job-btn">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;">
                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                <polyline points="17 21 17 13 7 13 7 21"></polyline>
                <polyline points="7 3 7 8 15 8"></polyline>
            </svg>
            ${isEdit ? 'Update Job' : 'Post Job'}
        </button>
    `;
    
    modal.setFooter(footer);
    modal.open(content);
    
    // Set up save button handler
    setTimeout(() => {
        document.getElementById('save-job-btn')?.addEventListener('click', async () => {
            await saveJob();
        });
    }, 0);
}

async function saveJob() {
    const title = document.getElementById('job-title').value.trim();
    const description = document.getElementById('job-description').value.trim();
    const requirements = document.getElementById('job-requirements').value.trim();
    const jobType = document.getElementById('job-type').value;
    const location = document.getElementById('job-location').value.trim();
    const salaryRange = document.getElementById('job-salary').value.trim();
    const isActive = document.getElementById('job-active')?.checked ?? true;

    if (!title || !description) {
        showError('Title and description are required');
        return;
    }

    try {
        const data = {
            title,
            description,
            requirements: requirements || null,
            job_type: jobType,
            location: location || null,
            salary_range: salaryRange || null
        };

        if (editingJobId) {
            data.is_active = isActive;
            await client.put(`/jobs/${editingJobId}`, data);
            showSuccess('Job updated successfully!');
        } else {
            await client.post('/jobs', data);
            showSuccess('Job posted successfully!');
        }

        // Close modal
        const modal = document.querySelector('.modal');
        if (modal) modal.remove();

        // Reload table
        setTimeout(() => loadJobsTable(), 500);
    } catch (error) {
        console.error('Error saving job:', error);
        showError('Failed to save job: ' + error.message);
    }
}

async function deleteJob(jobId) {
    if (!confirm('Are you sure you want to delete this job posting? This action cannot be undone.')) {
        return;
    }

    try {
        await client.delete(`/jobs/${jobId}`);
        showSuccess('Job deleted successfully!');
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
        setTimeout(() => window.location.href = '../login.html?type=company', 1500);
        return;
    }

    // Load components
    await loadSidebar('job-opportunities', getSidebarMode());
    loadUserInfo();
    
    // Initialize dropdown
    initUserDropdown(
        'user-menu-toggle',
        'user-dropdown',
        () => {
            client.clearAuth();
            window.location.href = '../login.html?type=company';
        },
        () => window.location.href = '../profile.html'
    );
    
    // Load jobs table
    await loadJobsTable();

    // Setup add job button
    document.getElementById('add-job-btn')?.addEventListener('click', () => {
        openJobModal();
    });
});
