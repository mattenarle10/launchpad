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
        
        // Fetch evaluation counts for each student
        const currentMonth = new Date().getMonth() + 1;
        const currentYear = new Date().getFullYear();
        
        for (let student of allStudents) {
            try {
                const evalRes = await client.get(`/companies/students/${student.student_id}/evaluations`);
                student.evaluations_this_month = evalRes.data.evaluations_this_month || 0;
                student.current_evaluation = evalRes.data.current_evaluation;
            } catch (e) {
                student.evaluations_this_month = 0;
                student.current_evaluation = null;
            }
        }

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
                    label: 'Avg Score', 
                    sortable: true,
                    format: (value) => {
                        if (value === null || value === undefined) {
                            return '<span style="color: #9CA3AF;">Not Evaluated</span>';
                        }
                        const color = value >= 80 ? '#10B981' : value >= 60 ? '#F59E0B' : '#EF4444';
                        return `<span style="color: ${color}; font-weight: 600;">${value}/100</span>`;
                    }
                },
                { 
                    key: 'evaluations_this_month', 
                    label: 'This Month', 
                    sortable: false,
                    format: (value) => {
                        const count = value || 0;
                        const color = count === 2 ? '#10B981' : count === 1 ? '#F59E0B' : '#9CA3AF';
                        return `<span style="color: ${color}; font-weight: 600;">${count}/2 Evaluations</span>`;
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

async function openEvaluationModal(student) {
    // Fetch current evaluation data
    let evalData = null;
    try {
        const res = await client.get(`/companies/students/${student.student_id}/evaluations`);
        evalData = res.data;
    } catch (e) {
        console.error('Error fetching evaluation data:', e);
    }
    
    const currentScore = evalData?.current_evaluation?.score ?? '';
    const currentCategory = evalData?.current_evaluation?.category ?? '';
    const evaluationsThisMonth = evalData?.evaluations_this_month ?? 0;
    const currentPeriod = evalData?.current_period === 'first_half' ? '1st-15th' : '16th-End';
    const monthName = new Date(evalData?.current_year, evalData?.current_month - 1).toLocaleString('default', { month: 'long' });
    
    const content = `
        <div style="padding: 10px 0;">
            <div class="detail-row" style="margin-bottom: 16px;">
                <span class="detail-label">Student:</span>
                <span class="detail-value">${student.first_name} ${student.last_name}</span>
            </div>
            <div class="detail-row" style="margin-bottom: 16px;">
                <span class="detail-label">ID Number:</span>
                <span class="detail-value">${student.id_num}</span>
            </div>
            <div class="detail-row" style="margin-bottom: 16px;">
                <span class="detail-label">Course:</span>
                <span class="detail-value"><span class="course-badge ${student.course.toLowerCase()}">${student.course}</span></span>
            </div>
            
            <div style="background: #F3F4F6; padding: 16px; border-radius: 8px; margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <span style="font-weight: 600; color: #374151;">📊 Evaluation Progress</span>
                    <span style="font-weight: 700; color: ${evaluationsThisMonth === 2 ? '#10B981' : '#F59E0B'}; font-size: 18px;">${evaluationsThisMonth}/2</span>
                </div>
                <div style="font-size: 13px; color: #6B7280; margin-bottom: 8px;">
                    Current Period: <strong>${currentPeriod} of ${monthName}</strong>
                </div>
                ${currentCategory ? `
                <div style="margin-top: 8px;">
                    <span style="font-size: 12px; color: #6B7280;">Current Score:</span>
                    <span style="font-weight: 600; color: #374151; margin-left: 4px;">${currentScore}/100</span>
                    <span class="course-badge" style="margin-left: 8px; font-size: 11px;">${currentCategory}</span>
                </div>
                ` : ''}
            </div>
            
            <div style="border-top: 2px solid #E5E7EB; padding-top: 20px; margin-top: 20px;">
                <div class="form-group">
                    <label for="evaluation-score" style="display: block; margin-bottom: 10px; font-weight: 600; color: #374151; font-size: 14px;">
                        Evaluation Score (0-100) <span style="color: #EF4444;">*</span>
                    </label>
                    <input 
                        type="number" 
                        id="evaluation-score" 
                        class="form-input" 
                        min="0" 
                        max="100" 
                        step="1"
                        value="${currentScore}"
                        placeholder="Enter score (0-100)"
                        style="width: 100%; padding: 12px; border: 2px solid #E5E7EB; border-radius: 8px; font-size: 16px;"
                        required
                    >
                    <div style="margin-top: 12px; padding: 12px; background: #EFF6FF; border-radius: 6px; font-size: 12px; color: #1E40AF;">
                        <strong>📋 Grading Scale:</strong><br>
                        81-100: Excellent | 61-80: Good | 41-60: Enough | 21-40: Poor | 0-20: Very Poor
                    </div>
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
        const scoreInput = document.getElementById('evaluation-score');
        
        document.getElementById('save-evaluation-btn')?.addEventListener('click', async () => {
            const score = parseInt(scoreInput.value, 10);
            
            if (isNaN(score) || score < 0 || score > 100) {
                showError('Please enter a valid score between 0 and 100');
                scoreInput.classList.add('error');
                scoreInput.focus();
                return;
            }
            
            scoreInput.classList.remove('error');
            
            try {
                const res = await client.post(`/companies/students/${student.student_id}/evaluations`, {
                    evaluation_score: score
                });
                
                modal.close();
                const category = res.data.category;
                const evalCount = res.data.evaluations_this_month;
                showSuccess(`${student.first_name} ${student.last_name} evaluated: ${score}/100 (${category}) - ${evalCount}/2 this month`);
                
                // Reload table
                setTimeout(() => loadStudentsTable(), 1000);
            } catch (error) {
                console.error('Error saving evaluation:', error);
                showError('Failed to save evaluation: ' + error.message);
            }
        });
        
        // Focus on input
        scoreInput.focus();
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
