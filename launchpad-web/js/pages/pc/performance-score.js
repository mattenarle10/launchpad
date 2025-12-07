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
                    type: 'view',
                    label: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>View Evaluation Details',
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

async function openPerformanceModal(student) {
    // Fetch evaluation history
    let evalData = null;
    try {
        const res = await client.get(`/companies/students/${student.student_id}/evaluations`);
        evalData = res.data;
    } catch (e) {
        console.error('Error fetching evaluation data:', e);
    }
    
    const evaluationRank = student.evaluation_rank || 0;
    const performanceScore = student.performance_score || 'Not Assessed';
    const evaluations = evalData?.evaluations || [];
    
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
            
            <!-- Performance Summary -->
            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px; border-radius: 12px; margin: 20px 0; color: white;">
                <div style="text-align: center;">
                    <div style="font-size: 14px; opacity: 0.9; margin-bottom: 8px;">Overall Performance Score</div>
                    <div style="font-size: 48px; font-weight: 700; margin-bottom: 8px;">${evaluationRank}/100</div>
                    <div style="font-size: 20px; font-weight: 600; background: rgba(255,255,255,0.2); padding: 8px 20px; border-radius: 20px; display: inline-block;">
                        ${performanceScore}
                    </div>
                </div>
            </div>
            
            
            <!-- Grading Criteria -->
            <div style="background: #F9FAFB; padding: 16px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #E5E7EB;">
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#4A6491" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                        <polyline points="10 9 9 9 8 9"></polyline>
                    </svg>
                    <span style="font-weight: 600; color: #3D5A7E; font-size: 14px;">Grading Criteria (Automatic)</span>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; font-size: 13px;">
                    <div style="padding: 10px 12px; background: white; border-radius: 6px; border-left: 4px solid #10B981; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                        <strong style="color: #374151;">81-100:</strong> <span style="color: #10B981;">Excellent</span>
                    </div>
                    <div style="padding: 10px 12px; background: white; border-radius: 6px; border-left: 4px solid #3B82F6; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                        <strong style="color: #374151;">61-80:</strong> <span style="color: #3B82F6;">Good</span>
                    </div>
                    <div style="padding: 10px 12px; background: white; border-radius: 6px; border-left: 4px solid #F59E0B; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                        <strong style="color: #374151;">41-60:</strong> <span style="color: #F59E0B;">Satisfactory</span>
                    </div>
                    <div style="padding: 10px 12px; background: white; border-radius: 6px; border-left: 4px solid #EF4444; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                        <strong style="color: #374151;">21-40:</strong> <span style="color: #EF4444;">Needs Improvement</span>
                    </div>
                    <div style="padding: 10px 12px; background: white; border-radius: 6px; border-left: 4px solid #991B1B; grid-column: span 2; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                        <strong style="color: #374151;">0-20:</strong> <span style="color: #991B1B;">Poor</span>
                    </div>
                </div>
                <div style="margin-top: 12px; padding: 10px; background: #EFF6FF; border-radius: 6px; border-left: 3px solid #4A6491;">
                    <p style="font-size: 12px; color: #3D5A7E; margin: 0;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="16" x2="12" y2="12"></line>
                            <line x1="12" y1="8" x2="12.01" y2="8"></line>
                        </svg>
                        Performance score is automatically calculated from evaluation scores
                    </p>
                </div>
            </div>
            
            <!-- Evaluation History -->
            <div style="border-top: 2px solid #E5E7EB; padding-top: 20px; margin-top: 20px;">
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#4A6491" stroke-width="2">
                        <path d="M3 3v18h18"></path>
                        <path d="M18 17V9"></path>
                        <path d="M13 17V5"></path>
                        <path d="M8 17v-3"></path>
                    </svg>
                    <h3 style="font-size: 16px; font-weight: 600; color: #3D5A7E; margin: 0;">Evaluation History</h3>
                </div>
                ${evaluations.length > 0 ? `
                    <div style="max-height: 300px; overflow-y: auto;">
                        ${evaluations.map(evaluation => {
                            const date = new Date(evaluation.evaluated_at);
                            const dateLabel = date.toLocaleString('default', { month: 'short', day: 'numeric', year: 'numeric' });
                            const color = evaluation.evaluation_score >= 80 ? '#10B981' : evaluation.evaluation_score >= 60 ? '#F59E0B' : '#EF4444';
                            return `
                                <div style="background: white; padding: 12px; border-radius: 8px; margin-bottom: 8px; border-left: 4px solid ${color};">
                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                        <div>
                                            <div style="font-weight: 600; color: #374151;">${dateLabel}</div>
                                            <div style="font-size: 12px; color: #6B7280; margin-top: 2px;">${evaluation.category}</div>
                                        </div>
                                        <div style="font-size: 24px; font-weight: 700; color: ${color};">${evaluation.evaluation_score}</div>
                                    </div>
                                </div>
                            `;
                        }).join('')}
                    </div>
                ` : `
                    <div style="text-align: center; padding: 20px; color: #9CA3AF;">
                        <p>No evaluations yet</p>
                    </div>
                `}
            </div>
        </div>
    `;
    
    const modal = createModal('performance-modal', {
        title: `Performance Details - ${student.first_name} ${student.last_name}`,
        size: 'medium'
    });
    
    const footer = `
        <button class="btn-modal btn-approve" data-modal-close id="performance-close-btn">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;">
                <path d="M5 13l4 4L19 7"></path>
            </svg>
            Close
        </button>
    `;
    
    modal.setFooter(footer);
    modal.open(content);

    // Ensure footer Close button is wired to this modal instance
    setTimeout(() => {
        const closeBtn = document.getElementById('performance-close-btn');
        closeBtn?.addEventListener('click', () => modal.close());
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
