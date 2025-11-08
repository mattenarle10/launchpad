/**
 * Daily Time Records Page Logic
 * CDC reviews and approves/rejects student daily reports
 */

import { loadSidebar, loadUserInfo } from '../components.js';
import { initUserDropdown } from './dropdown.js';
import ReportAPI from '../api/report.js';
import client from '../api/client.js';
import { getSidebarMode } from '../utils/sidebar-helper.js';
import DataTable from './table.js';
import { showSuccess, showError, showWarning } from '../utils/notifications.js';
import { createModal } from '../utils/modal.js';
import { openFileViewer } from '../utils/file-viewer.js';

let pendingReportsData = [];
let approvedReportsData = [];
let pendingDataTable = null;
let approvedDataTable = null;
let currentTab = 'pending';

document.addEventListener('DOMContentLoaded', async () => {
    // Check authentication
    if (!client.isAuthenticated()) {
        showError('Please login to access this page');
        setTimeout(() => window.location.href = '../login.html', 1500);
        return;
    }

    // Load components
    await loadSidebar('submission-reports', getSidebarMode());
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
    
    // Load both tables
    await loadPendingReports();
    await loadApprovedReports();

    // Setup tabs
    setupTabs();

    // Setup search for pending tab
    document.getElementById('pending-search-input')?.addEventListener('input', (e) => {
        pendingDataTable?.search(e.target.value);
    });

    // Setup search for approved tab
    document.getElementById('approved-search-input')?.addEventListener('input', (e) => {
        approvedDataTable?.search(e.target.value);
    });
});

function setupTabs() {
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const targetTab = btn.getAttribute('data-tab');

            // Remove active class from all tabs and buttons
            tabBtns.forEach(b => b.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));

            // Add active class to clicked button and corresponding tab
            btn.classList.add('active');
            document.getElementById(`${targetTab}-tab`).classList.add('active');
            
            currentTab = targetTab;
        });
    });
}

async function loadPendingReports() {
    const tableWrapper = document.getElementById('pending-table-wrapper');
    tableWrapper.innerHTML = '<div class="loading"><div class="loading-spinner"></div><p>Loading pending reports...</p></div>';

    try {
        const response = await ReportAPI.getPendingReports();
        const reports = response.data || [];

        // Update stats
        const statElement = document.getElementById('pending-reports-count');
        if (statElement) statElement.textContent = reports.length;

        pendingReportsData = reports;

        // Create DataTable
        pendingDataTable = new DataTable({
            containerId: 'pending-table-wrapper',
            columns: [
                { 
                    key: 'report_date', 
                    label: 'Date', 
                    sortable: true,
                    format: (value) => formatDate(value)
                },
                { 
                    key: 'id_num', 
                    label: 'Student ID', 
                    sortable: true
                },
                { 
                    key: 'first_name', 
                    label: 'Student Name', 
                    sortable: true,
                    format: (value, row) => `${row.first_name} ${row.last_name}`
                },
                { 
                    key: 'course', 
                    label: 'Course', 
                    sortable: true,
                    format: (value) => `<span class="course-badge ${value.toLowerCase()}">${value}</span>`
                },
                { 
                    key: 'hours_requested', 
                    label: 'Hours', 
                    sortable: true,
                    format: (value) => `<strong>${value} hrs</strong>`
                },
                { 
                    key: 'activity_type', 
                    label: 'Activity', 
                    sortable: true
                },
                { 
                    key: 'submitted_at', 
                    label: 'Submitted', 
                    sortable: true,
                    format: (value) => formatDate(value)
                }
            ],
            actions: [
                {
                    type: 'view',
                    label: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>View',
                    onClick: (row) => viewReportModal(row)
                },
                {
                    type: 'approve',
                    label: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;"><polyline points="20 6 9 17 4 12"></polyline></svg>Approve',
                    onClick: (row) => approveReportWithConfirm(row)
                },
                {
                    type: 'reject',
                    label: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>Decline',
                    onClick: (row) => rejectReportWithConfirm(row)
                }
            ],
            data: reports,
            pageSize: 10,
            emptyMessage: 'No pending reports to review'
        });

    } catch (error) {
        console.error('Error loading reports:', error);
        tableWrapper.innerHTML = `
            <div class="empty-state">
                <div class="empty-state-icon">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                </div>
                <div class="empty-state-text">Error loading reports</div>
                <div class="empty-state-subtext">${error.message}</div>
            </div>
        `;
        showError('Failed to load pending reports: ' + error.message);
    }
}

async function loadApprovedReports() {
    const tableWrapper = document.getElementById('approved-table-wrapper');
    tableWrapper.innerHTML = '<div class="loading"><div class="loading-spinner"></div><p>Loading approved reports...</p></div>';

    try {
        const response = await ReportAPI.getApprovedReports();
        const reports = response.data || [];

        // Update stats
        const statElement = document.getElementById('approved-reports-count');
        if (statElement) statElement.textContent = reports.length;

        approvedReportsData = reports;

        // Create DataTable (view only, no approve/decline actions)
        approvedDataTable = new DataTable({
            containerId: 'approved-table-wrapper',
            columns: [
                { 
                    key: 'report_date', 
                    label: 'Date', 
                    sortable: true,
                    format: (value) => formatDate(value)
                },
                { 
                    key: 'id_num', 
                    label: 'Student ID', 
                    sortable: true
                },
                { 
                    key: 'first_name', 
                    label: 'Student Name', 
                    sortable: true,
                    format: (value, row) => `${row.first_name} ${row.last_name}`
                },
                { 
                    key: 'course', 
                    label: 'Course', 
                    sortable: true,
                    format: (value) => `<span class="course-badge ${value.toLowerCase()}">${value}</span>`
                },
                { 
                    key: 'hours_requested', 
                    label: 'Hours', 
                    sortable: true,
                    format: (value) => `<strong style="color: #10B981;">${value} hrs</strong>`
                },
                { 
                    key: 'activity_type', 
                    label: 'Activity', 
                    sortable: true
                },
                { 
                    key: 'reviewed_at', 
                    label: 'Approved', 
                    sortable: true,
                    format: (value) => formatDate(value)
                }
            ],
            actions: [
                {
                    type: 'view',
                    label: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>View',
                    onClick: (row) => viewReportModal(row, true)
                }
            ],
            data: reports,
            pageSize: 10,
            emptyMessage: 'No approved reports yet'
        });

    } catch (error) {
        console.error('Error loading approved reports:', error);
        tableWrapper.innerHTML = `
            <div class="empty-state">
                <div class="empty-state-icon">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                </div>
                <div class="empty-state-text">Error loading approved reports</div>
                <div class="empty-state-subtext">${error.message}</div>
            </div>
        `;
        showError('Failed to load approved reports: ' + error.message);
    }
}

function viewReportModal(report, isApproved = false) {
    const reportUrl = `../../../launchpad-api/uploads/daily_reports/${report.report_file}`;
    
    const content = `
        <div class="detail-row">
            <span class="detail-label">Student:</span>
            <span class="detail-value">${report.first_name} ${report.last_name} (${report.id_num})</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Course:</span>
            <span class="detail-value"><span class="course-badge ${report.course.toLowerCase()}">${report.course}</span></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Company:</span>
            <span class="detail-value">${report.company_name || 'N/A'}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Report Date:</span>
            <span class="detail-value">${formatDate(report.report_date)}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Hours Requested:</span>
            <span class="detail-value"><strong>${report.hours_requested} hours</strong></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Activity Type:</span>
            <span class="detail-value">${report.activity_type}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Description:</span>
            <span class="detail-value">${report.description}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Submitted:</span>
            <span class="detail-value">${formatDate(report.submitted_at)}</span>
        </div>
        <div style="margin-top: 20px;">
            <h4 style="margin-bottom: 10px;">Report File</h4>
            <div style="display: flex; gap: 10px;">
                <button onclick="window.viewReportFile('${reportUrl}', '${report.first_name} ${report.last_name} - ${formatDate(report.report_date)}')" class="btn-action btn-view" style="border: none; cursor: pointer;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                    View File
                </button>

            </div>
        </div>
    `;

    const modal = createModal('report-details-modal', {
        title: 'Daily Report Details',
        size: 'medium'
    });

    const footer = isApproved ? `
        <button class="btn-modal" data-modal-close>Close</button>
    ` : `
        <button class="btn-modal" data-modal-close>Close</button>
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

    setTimeout(() => {
        // Setup file viewer function
        window.viewReportFile = (url, title) => {
            openFileViewer(url, title);
        };

        if (!isApproved) {
            document.getElementById('modal-approve-btn')?.addEventListener('click', () => {
                modal.close();
                approveReportWithConfirm(report);
            });

            document.getElementById('modal-reject-btn')?.addEventListener('click', () => {
                modal.close();
                rejectReportWithConfirm(report);
            });
        }
    }, 0);
}

async function approveReportWithConfirm(report) {
    const content = `
        <div style="padding: 10px 0;">
            <p style="margin-bottom: 20px; color: #6B7280; line-height: 1.6;">
                Approve <strong>${report.first_name} ${report.last_name}</strong>'s daily report for <strong>${formatDate(report.report_date)}</strong>
            </p>
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 10px; font-weight: 600; color: #374151; font-size: 14px;">
                    Set Hours to Approve <span style="color: #EF4444;">*</span>
                </label>
                <div style="display: flex; align-items: center; gap: 12px;">
                    <button type="button" id="decrease-hours" style="width: 40px; height: 40px; border: 2px solid #E5E7EB; border-radius: 8px; background: white; cursor: pointer; font-size: 20px; font-weight: bold; color: #6B7280; transition: all 0.2s;">
                        −
                    </button>
                    <input type="number" id="hours-input" value="8" min="0.5" max="24" step="0.5" style="width: 80px; height: 40px; text-align: center; font-size: 18px; font-weight: bold; border: 2px solid #3B82F6; border-radius: 8px; color: #111827; -moz-appearance: textfield;" />
                    <button type="button" id="increase-hours" style="width: 40px; height: 40px; border: 2px solid #E5E7EB; border-radius: 8px; background: white; cursor: pointer; font-size: 20px; font-weight: bold; color: #6B7280; transition: all 0.2s;">
                        +
                    </button>
                    <span style="color: #6B7280; font-size: 14px;">hours</span>
                </div>
                <style>
                    #hours-input::-webkit-outer-spin-button,
                    #hours-input::-webkit-inner-spin-button {
                        -webkit-appearance: none;
                        margin: 0;
                    }
                </style>
                <p style="margin-top: 8px; font-size: 12px; color: #9CA3AF;">Student requested: ${report.hours_requested} hours</p>
            </div>
            
            <div style="background: #EFF6FF; border-left: 4px solid #3B82F6; padding: 14px 16px; border-radius: 8px;">
                <p style="margin: 0; color: #1E40AF; font-size: 13px; line-height: 1.5;">
                    <strong>💡 Tip:</strong> You can adjust the hours based on the report quality and activities performed.
                </p>
            </div>
        </div>
    `;
    
    const modal = createModal('approve-report-modal', {
        title: `Approve Report`,
        size: 'medium'
    });
    
    const footer = `
        <button class="btn-modal" data-modal-close>Cancel</button>
        <button class="btn-modal btn-approve" id="confirm-approve-btn">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
            Approve Report
        </button>
    `;
    
    modal.setFooter(footer);
    modal.open(content);
    
    setTimeout(() => {
        const hoursInput = document.getElementById('hours-input');
        const decreaseBtn = document.getElementById('decrease-hours');
        const increaseBtn = document.getElementById('increase-hours');
        
        // Decrease hours
        decreaseBtn?.addEventListener('click', () => {
            const current = parseFloat(hoursInput.value);
            if (current > 0.5) {
                hoursInput.value = (current - 0.5).toFixed(1);
            }
        });
        
        // Increase hours
        increaseBtn?.addEventListener('click', () => {
            const current = parseFloat(hoursInput.value);
            if (current < 24) {
                hoursInput.value = (current + 0.5).toFixed(1);
            }
        });
        
        // Hover effects
        decreaseBtn?.addEventListener('mouseenter', () => {
            decreaseBtn.style.borderColor = '#EF4444';
            decreaseBtn.style.color = '#EF4444';
        });
        decreaseBtn?.addEventListener('mouseleave', () => {
            decreaseBtn.style.borderColor = '#E5E7EB';
            decreaseBtn.style.color = '#6B7280';
        });
        
        increaseBtn?.addEventListener('mouseenter', () => {
            increaseBtn.style.borderColor = '#10B981';
            increaseBtn.style.color = '#10B981';
        });
        increaseBtn?.addEventListener('mouseleave', () => {
            increaseBtn.style.borderColor = '#E5E7EB';
            increaseBtn.style.color = '#6B7280';
        });
        
        document.getElementById('confirm-approve-btn')?.addEventListener('click', async () => {
            const hours = parseFloat(hoursInput.value);
            
            if (!hours || hours <= 0 || hours > 24) {
                showError('Please enter valid hours (0.5 - 24)');
                return;
            }
            
            try {
                // We need to update the API call to include hours
                await ReportAPI.approveReportWithHours(report.report_id, hours);
                modal.close();
                showSuccess(`Report approved! ${hours} hours added to ${report.first_name} ${report.last_name}'s progress.`);
                
                setTimeout(() => {
                    loadPendingReports();
                    loadApprovedReports();
                }, 1000);
            } catch (error) {
                console.error('Error approving report:', error);
                showError('Failed to approve report: ' + error.message);
            }
        });
    }, 0);
}

async function rejectReportWithConfirm(report) {
    const content = `
        <div style="padding: 10px 0;">
            <p style="margin-bottom: 20px; color: #6B7280; line-height: 1.6;">
                Decline <strong>${report.first_name} ${report.last_name}</strong>'s daily report for <strong>${formatDate(report.report_date)}</strong>
            </p>
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 10px; font-weight: 600; color: #374151; font-size: 14px;">
                    Reason for Declining <span style="color: #EF4444;">*</span>
                </label>
                <textarea 
                    id="decline-reason" 
                    placeholder="Explain why this report is being declined..." 
                    style="width: 100%; min-height: 100px; padding: 12px; border: 2px solid #E5E7EB; border-radius: 8px; font-size: 14px; font-family: inherit; resize: vertical;"
                ></textarea>
                <p style="margin-top: 8px; font-size: 12px; color: #9CA3AF;">The student will see this message</p>
            </div>
            
            <div style="background: #FEF2F2; border-left: 4px solid #EF4444; padding: 14px 16px; border-radius: 8px;">
                <p style="margin: 0; color: #991B1B; font-size: 13px; line-height: 1.5;">
                    <strong>⚠️ Note:</strong> The student can resubmit their report after reviewing your feedback.
                </p>
            </div>
        </div>
    `;
    
    const modal = createModal('decline-report-modal', {
        title: `Decline Report`,
        size: 'medium'
    });
    
    const footer = `
        <button class="btn-modal" data-modal-close>Cancel</button>
        <button class="btn-modal btn-reject" id="confirm-decline-btn">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
            Decline Report
        </button>
    `;
    
    modal.setFooter(footer);
    modal.open(content);
    
    setTimeout(() => {
        const reasonTextarea = document.getElementById('decline-reason');
        
        // Focus on textarea
        reasonTextarea?.focus();
        
        document.getElementById('confirm-decline-btn')?.addEventListener('click', async () => {
            const reason = reasonTextarea.value.trim();
            
            if (!reason) {
                showError('Please provide a reason for declining');
                reasonTextarea.style.borderColor = '#EF4444';
                return;
            }
            
            try {
                await ReportAPI.rejectReport(report.report_id, reason);
                modal.close();
                showWarning(`Report declined. ${report.first_name} ${report.last_name} will be notified.`);
                
                setTimeout(() => loadPendingReports(), 1000);
            } catch (error) {
                console.error('Error declining report:', error);
                showError('Failed to decline report: ' + error.message);
            }
        });
    }, 0);
}

function openReportFile(report) {
    const reportUrl = `../../../launchpad-api/uploads/daily_reports/${report.report_file}`;
    window.open(reportUrl, '_blank');
}

function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric' 
    });
}
