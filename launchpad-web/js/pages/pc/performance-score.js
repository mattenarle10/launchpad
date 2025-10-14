/**
 * Performance Score Page
 * Partner Company assesses student performance (qualitative)
 */

import { loadSidebar, loadUserInfo } from '../../components.js';
import { initUserDropdown } from '../dropdown.js';
import client from '../../api/client.js';
import { showError, showSuccess } from '../../utils/notifications.js';
import { getSidebarMode } from '../../utils/sidebar-helper.js';
import DataTable from '../table.js';
import { createModal } from '../../utils/modal.js';

let allStudents = [];
let dataTable = null;

async function loadStudentsTable() {
    const tableWrapper = document.getElementById('table-wrapper');
    tableWrapper.innerHTML = '<div class="loading"><p>Loading students...</p></div>';

    try {
        const res = await client.get('/companies/students');
        allStudents = res.data || [];

        // Create DataTable
        dataTable = new DataTable({
            containerId: 'table-wrapper',
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
                        const statusClass = value === 'completed' ? 'completed' : value === 'in_progress' ? 'ongoing' : 'pending';
                        const statusText = value === 'not_started' ? 'Not Started' : value.replace('_', ' ').toUpperCase();
                        return `<span class="status-badge ${statusClass}">${statusText}</span>`;
                    }
                },
                { 
                    key: 'performance_score', 
                    label: 'Performance', 
                    sortable: true,
                    format: (value) => {
                        if (!value) {
                            return '<span style="color: #9CA3AF;">Not Assessed</span>';
                        }
                        const colors = {
                            'Excellent': '#10B981',
                            'Good': '#3B82F6',
                            'Satisfactory': '#F59E0B',
                            'Needs Improvement': '#EF4444',
                            'Poor': '#991B1B'
                        };
                        const color = colors[value] || '#6B7280';
                        return `<span style="color: ${color}; font-weight: 600;">${value}</span>`;
                    }
                }
            ],
            actions: [
                {
                    type: 'assess',
                    label: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path></svg>Assess',
                    onClick: (row) => openPerformanceModal(row)
                }
            ],
            data: allStudents,
            pageSize: 10,
            emptyMessage: 'No students to evaluate'
        });

    } catch (error) {
        console.error('Error loading students:', error);
        tableWrapper.innerHTML = `
            <div class="empty-state">
                <div class="empty-state-text">Error loading students</div>
                <div class="empty-state-subtext">${error.message}</div>
            </div>
        `;
        showError('Failed to load students: ' + error.message);
    }
}

function openPerformanceModal(student) {
    const currentScore = student.performance_score || '';
    const scores = ['Excellent', 'Good', 'Satisfactory', 'Needs Improvement', 'Poor'];
    
    const content = `
        <div style="padding: 10px 0;">
            <div class="detail-row" style="margin-bottom: 20px;">
                <span class="detail-label">Student:</span>
                <span class="detail-value">${student.first_name} ${student.last_name}</span>
            </div>
            <div class="detail-row" style="margin-bottom: 20px;">
                <span class="detail-label">ID Number:</span>
                <span class="detail-value">${student.id_num}</span>
            </div>
            <div class="detail-row" style="margin-bottom: 20px;">
                <span class="detail-label">Course:</span>
                <span class="detail-value"><span class="course-badge ${student.course.toLowerCase()}">${student.course}</span></span>
            </div>
            <div class="detail-row" style="margin-bottom: 20px;">
                <span class="detail-label">OJT Progress:</span>
                <span class="detail-value">${student.completed_hours || 0} / ${student.required_hours || 0} hrs (${student.completion_percentage || 0}%)</span>
            </div>
            
            <div style="border-top: 2px solid #E5E7EB; padding-top: 20px; margin-top: 20px;">
                <div class="form-group">
                    <label for="performance-score" style="display: block; margin-bottom: 10px; font-weight: 600; color: #374151; font-size: 14px;">
                        Performance Assessment <span style="color: #EF4444;">*</span>
                    </label>
                    <select 
                        id="performance-score" 
                        class="form-input custom-select" 
                        style="width: 100%; padding: 12px; border: 2px solid #E5E7EB; border-radius: 8px; font-size: 16px;"
                        required
                    >
                        <option value="">-- Select Performance Level --</option>
                        ${scores.map(score => `<option value="${score}" ${score === currentScore ? 'selected' : ''}>${score}</option>`).join('')}
                    </select>
                    <p style="margin-top: 8px; color: #6B7280; font-size: 13px;">
                        ⭐ Select the performance level that best describes the student's work quality
                    </p>
                </div>
            </div>
        </div>
    `;
    
    const modal = createModal('performance-modal', {
        title: `Assess ${student.first_name} ${student.last_name}`,
        size: 'medium'
    });
    
    const footer = `
        <button class="btn-modal" data-modal-close>Cancel</button>
        <button class="btn-modal btn-approve" id="save-performance-btn">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;">
                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                <polyline points="17 21 17 13 7 13 7 21"></polyline>
                <polyline points="7 3 7 8 15 8"></polyline>
            </svg>
            Save Assessment
        </button>
    `;
    
    modal.setFooter(footer);
    modal.open(content);
    
    // Set up save button handler
    setTimeout(() => {
        const scoreSelect = document.getElementById('performance-score');
        
        document.getElementById('save-performance-btn')?.addEventListener('click', async () => {
            const score = scoreSelect.value;
            
            if (!score) {
                showError('Please select a performance level');
                scoreSelect.classList.add('error');
                scoreSelect.focus();
                return;
            }
            
            scoreSelect.classList.remove('error');
            
            try {
                await client.put(`/companies/students/${student.student_id}/performance`, {
                    performance_score: score
                });
                
                modal.close();
                showSuccess(`${student.first_name} ${student.last_name} assessed as "${score}"`);
                
                // Reload table
                setTimeout(() => loadStudentsTable(), 1000);
            } catch (error) {
                console.error('Error saving performance:', error);
                showError('Failed to save performance: ' + error.message);
            }
        });
        
        // Focus on select
        scoreSelect.focus();
    }, 0);
}

document.addEventListener('DOMContentLoaded', async () => {
    // Check authentication
    if (!client.isAuthenticated()) {
        showError('Please login to access this page');
        setTimeout(() => window.location.href = '../login.html?type=company', 1500);
        return;
    }

    // Load components
    await loadSidebar('performance', getSidebarMode());
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
    
    // Load students table
    await loadStudentsTable();

    // Setup search
    document.getElementById('search-input')?.addEventListener('input', (e) => {
        dataTable.search(e.target.value);
    });
});
