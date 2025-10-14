/**
 * Students Evaluation Page
 * Partner Company evaluates their students (0-100 rank)
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
                    key: 'evaluation_rank', 
                    label: 'Evaluation', 
                    sortable: true,
                    format: (value) => {
                        if (value === null || value === undefined) {
                            return '<span style="color: #9CA3AF;">Not Evaluated</span>';
                        }
                        const color = value >= 80 ? '#10B981' : value >= 60 ? '#F59E0B' : '#EF4444';
                        return `<span style="color: ${color}; font-weight: 600;">${value}/100</span>`;
                    }
                }
            ],
            actions: [
                {
                    type: 'evaluate',
                    label: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;"><path d="M9 11l3 3L22 4"></path><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>Evaluate',
                    onClick: (row) => openEvaluationModal(row)
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

function openEvaluationModal(student) {
    const currentRank = student.evaluation_rank ?? '';
    
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
                    <label for="evaluation-rank" style="display: block; margin-bottom: 10px; font-weight: 600; color: #374151; font-size: 14px;">
                        Evaluation Rank (0-100) <span style="color: #EF4444;">*</span>
                    </label>
                    <input 
                        type="number" 
                        id="evaluation-rank" 
                        class="form-input" 
                        min="0" 
                        max="100" 
                        step="1"
                        value="${currentRank}"
                        placeholder="Enter rank (0-100)"
                        style="width: 100%; padding: 12px; border: 2px solid #E5E7EB; border-radius: 8px; font-size: 16px;"
                        required
                    >
                    <p style="margin-top: 8px; color: #6B7280; font-size: 13px;">
                        💡 Enter a score from 0 to 100 based on the student's performance
                    </p>
                </div>
            </div>
        </div>
    `;
    
    const modal = createModal('evaluation-modal', {
        title: `Evaluate ${student.first_name} ${student.last_name}`,
        size: 'medium'
    });
    
    const footer = `
        <button class="btn-modal" data-modal-close>Cancel</button>
        <button class="btn-modal btn-approve" id="save-evaluation-btn">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;">
                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                <polyline points="17 21 17 13 7 13 7 21"></polyline>
                <polyline points="7 3 7 8 15 8"></polyline>
            </svg>
            Save Evaluation
        </button>
    `;
    
    modal.setFooter(footer);
    modal.open(content);
    
    // Set up save button handler
    setTimeout(() => {
        const rankInput = document.getElementById('evaluation-rank');
        
        document.getElementById('save-evaluation-btn')?.addEventListener('click', async () => {
            const rank = parseInt(rankInput.value, 10);
            
            if (isNaN(rank) || rank < 0 || rank > 100) {
                showError('Please enter a valid rank between 0 and 100');
                rankInput.classList.add('error');
                rankInput.focus();
                return;
            }
            
            rankInput.classList.remove('error');
            
            try {
                await client.put(`/companies/students/${student.student_id}/evaluation`, {
                    evaluation_rank: rank
                });
                
                modal.close();
                showSuccess(`${student.first_name} ${student.last_name} evaluated with rank ${rank}/100`);
                
                // Reload table
                setTimeout(() => loadStudentsTable(), 1000);
            } catch (error) {
                console.error('Error saving evaluation:', error);
                showError('Failed to save evaluation: ' + error.message);
            }
        });
        
        // Focus on input
        rankInput.focus();
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
    await loadSidebar('evaluations', getSidebarMode());
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
