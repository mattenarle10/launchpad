/**
 * Requirements Page Logic
 * CDC views and downloads student requirements
 */

import { loadSidebar, loadUserInfo } from '../../components.js';
import { initUserDropdown } from '../dropdown.js';
import client from '../../api/client.js';
import { getSidebarMode } from '../../utils/sidebar-helper.js';
import DataTable from '../table.js';
import { showSuccess, showError } from '../../utils/notifications.js';
import { createModal } from '../../utils/modal.js';
import { openFileViewer } from '../../utils/file-viewer.js';

let requirementsData = [];
let dataTable = null;

document.addEventListener('DOMContentLoaded', async () => {
    // Check authentication
    if (!client.isAuthenticated()) {
        showError('Please login to access this page');
        setTimeout(() => window.location.href = '../login.html', 1500);
        return;
    }

    // Load components
    await loadSidebar('requirements', getSidebarMode());
    loadUserInfo();
    
    // Initialize dropdown
    initUserDropdown(
        'user-menu-toggle',
        'user-dropdown',
        () => {
            client.clearAuth();
            window.location.href = '../login.html';
        },
        () => console.log('View profile')
    );
    
    // Load requirements
    await loadRequirements();

    // Setup search
    document.getElementById('search-input')?.addEventListener('input', (e) => {
        dataTable?.search(e.target.value);
    });

    // Setup filter
    document.getElementById('requirement-type-filter')?.addEventListener('change', (e) => {
        filterByType(e.target.value);
    });
});

async function loadRequirements() {
    const tableWrapper = document.getElementById('table-wrapper');
    tableWrapper.innerHTML = '<div class="loading"><div class="loading-spinner"></div><p>Loading requirements...</p></div>';

    try {
        const response = await client.get('/admin/requirements');
        console.log('API Response:', response);
        
        const students = response.data?.students || [];
        console.log('Students data:', students);

        requirementsData = students;

        // Create DataTable
        dataTable = new DataTable({
            containerId: 'table-wrapper',
            columns: [
                { 
                    key: 'id_num', 
                    label: 'ID Number',
                    sortable: true,
                    format: (value) => value || 'N/A'
                },
                { 
                    key: 'full_name', 
                    label: 'Student Name',
                    sortable: true,
                    format: (value) => value || 'Unknown'
                },
                { 
                    key: 'course', 
                    label: 'Course',
                    sortable: true,
                    format: (value) => value || 'N/A'
                },
                { 
                    key: 'company_name', 
                    label: 'Company',
                    sortable: true,
                    format: (value) => value || 'Not Assigned'
                },
                {
                    key: 'requirements_count',
                    label: 'Pre-Deploy',
                    format: (value) => value?.pre_deployment || 0
                },
                {
                    key: 'requirements_count',
                    label: 'Deploy',
                    format: (value) => value?.deployment || 0
                },
                {
                    key: 'requirements_count',
                    label: 'Final',
                    format: (value) => value?.final_requirements || 0
                },
                {
                    key: 'last_submission',
                    label: 'Last Submit',
                    sortable: true,
                    format: (value) => value ? formatDate(value) : 'Never'
                }
            ],
            actions: [
                {
                    type: 'view',
                    label: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>View',
                    onClick: (student) => showRequirementsModal(student)
                }
            ],
            data: students,
            pagination: true,
            pageSize: 10,
            emptyMessage: 'No students with requirements found'
        });

    } catch (error) {
        console.error('Error loading requirements:', error);
        showError('Failed to load requirements');
        tableWrapper.innerHTML = '<div class="error-message">Failed to load requirements. Please try again.</div>';
    }
}

function filterByType(type) {
    if (!type) {
        dataTable.setData(requirementsData);
        return;
    }

    const filtered = requirementsData.filter(student => {
        return student.requirements_count && student.requirements_count[type] > 0;
    });

    dataTable.setData(filtered);
}

async function showRequirementsModal(student) {
    try {
        // Get student ID
        const studentId = student.student_id || student.id || student.user_id;
        
        if (!studentId) {
            console.error('No student ID found in student object:', student);
            showError('Student ID not found. Please try again.');
            return;
        }
        
        // Fetch student's detailed requirements
        const response = await client.get(`/admin/students/${studentId}/requirements`);
        const data = response.data;

        if (!data || !data.student_info) {
            showError('Invalid response from server');
            return;
        }

        const studentInfo = data.student_info;
        const groupedByType = data.grouped_by_type || {
            pre_deployment: [],
            deployment: [],
            final_requirements: []
        };

        const modalContent = `
            <div class="requirements-modal">
                <div class="student-info">
                    <h3>${studentInfo.first_name || ''} ${studentInfo.last_name || ''}</h3>
                    <p><strong>ID:</strong> ${studentInfo.id_num || 'N/A'}</p>
                    <p><strong>Course:</strong> ${studentInfo.course || 'N/A'}</p>
                    <p><strong>Company:</strong> ${studentInfo.company_name || 'Not Assigned'}</p>
                </div>

                <div class="requirements-sections">
                    ${renderRequirementSection('Pre-Deployment', 'pre_deployment', groupedByType.pre_deployment || [])}
                    ${renderRequirementSection('Deployment', 'deployment', groupedByType.deployment || [])}
                    ${renderRequirementSection('Final Requirements', 'final_requirements', groupedByType.final_requirements || [])}
                </div>

                ${(data.total_count || 0) === 0 ? '<p class="no-requirements">No requirements submitted yet.</p>' : ''}
            </div>
        `;

        const modal = createModal('requirements-modal', {
            title: `Requirements - ${student.full_name || 'Student'}`,
            size: 'large'
        });
        
        modal.open(modalContent);

    } catch (error) {
        console.error('Error loading student requirements:', error);
        showError('Failed to load student requirements');
    }
}

function renderRequirementSection(title, type, requirements) {
    if (requirements.length === 0) {
        return `
            <div class="requirement-section">
                <h4>${title}</h4>
                <p class="empty-section">No files submitted</p>
            </div>
        `;
    }

    return `
        <div class="requirement-section">
            <h4>${title} (${requirements.length} file${requirements.length !== 1 ? 's' : ''})</h4>
            <div class="requirements-list">
                ${requirements.map(req => `
                    <div class="requirement-item">
                        <div class="file-icon">
                            ${getFileIcon(req.file_name)}
                        </div>
                        <div class="file-info">
                            <p class="file-name">${req.file_name}</p>
                            <p class="file-meta">${formatFileSize(req.file_size)} • ${formatDate(req.submitted_at)}</p>
                            ${req.description ? `<p class="file-description">${req.description}</p>` : ''}
                        </div>
                        <div class="file-actions">
                            <button class="btn-download" onclick="downloadFile('${req.file_path}', '${req.file_name}', '${type}')">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                    <polyline points="7 10 12 15 17 10"></polyline>
                                    <line x1="12" y1="15" x2="12" y2="3"></line>
                                </svg>
                                Download
                            </button>
                            <button class="btn-view" onclick="viewFile('${req.file_path}', '${req.file_name}', '${type}')">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                View
                            </button>
                        </div>
                    </div>
                `).join('')}
            </div>
        </div>
    `;
}

function getFileIcon(fileName) {
    const ext = fileName.split('.').pop().toLowerCase();
    if (['jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp', 'svg'].includes(ext)) {
        return '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>';
    } else if (ext === 'pdf') {
        return '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>';
    } else if (['doc', 'docx'].includes(ext)) {
        return '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>';
    }
    return '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>';
}

function formatFileSize(bytes) {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
}

function formatDate(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diffTime = Math.abs(now - date);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

    if (diffDays === 0) return 'Today';
    if (diffDays === 1) return 'Yesterday';
    if (diffDays < 7) return `${diffDays} days ago`;
    
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}

// Make downloadFile and viewFile available globally for onclick
window.downloadFile = async function(filePath, fileName, type) {
    try {
        const uploadsUrl = client.getUploadsUrl();
        const fileUrl = `${uploadsUrl}/requirements/${type}/${filePath}`;
        
        // Fetch the file and trigger download
        const response = await fetch(fileUrl);
        if (!response.ok) {
            throw new Error('File not found');
        }
        
        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = fileName;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        window.URL.revokeObjectURL(url);
        
        showSuccess(`${fileName} downloaded successfully`);
    } catch (error) {
        console.error('Download error:', error);
        showError('Failed to download file');
    }
};

window.viewFile = function(filePath, fileName, type) {
    const uploadsUrl = client.getUploadsUrl();
    const fileUrl = `${uploadsUrl}/requirements/${type}/${filePath}`;
    openFileViewer(fileUrl, fileName);
};
