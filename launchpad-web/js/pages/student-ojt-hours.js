/**
 * Student OJT Hours Management Page
 * CDC can view and edit all students' OJT hours
 */

import { loadSidebar, loadUserInfo } from '../components.js';
import { initUserDropdown } from './dropdown.js';
import client from '../api/client.js';
import DataTable from './table.js';
import { showSuccess, showError } from '../utils/notifications.js';
import { createModal } from '../utils/modal.js';

let studentsData = [];
let dataTable = null;

document.addEventListener('DOMContentLoaded', async () => {
    // Check authentication
    if (!client.isAuthenticated()) {
        showError('Please login to access this page');
        setTimeout(() => window.location.href = '../login.html', 1500);
        return;
    }

    // Load components
    await loadSidebar('ojt-hours');
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
    
    // Load students table
    await loadStudentsTable();

    // Setup search
    document.getElementById('students-search-input')?.addEventListener('input', (e) => {
        dataTable?.search(e.target.value);
    });
});

async function loadStudentsTable() {
    const tableWrapper = document.getElementById('students-table-wrapper');
    tableWrapper.innerHTML = '<div class="loading"><div class="loading-spinner"></div><p>Loading students...</p></div>';

    try {
        const response = await client.get('/admin/ojt/progress?pageSize=1000');
        const students = response.data || [];

        studentsData = students;

        // Create DataTable
        dataTable = new DataTable({
            containerId: 'students-table-wrapper',
            columns: [
                { 
                    key: 'id_num', 
                    label: 'ID Number', 
                    sortable: true
                },
                { 
                    key: 'first_name', 
                    label: 'Name', 
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
                    key: 'completed_hours', 
                    label: 'Completed Hours', 
                    sortable: true,
                    format: (value) => `<strong>${parseFloat(value).toFixed(1)} hrs</strong>`
                },
                { 
                    key: 'completion_percentage', 
                    label: 'Progress', 
                    sortable: true,
                    format: (value) => {
                        const percent = parseFloat(value);
                        const color = percent >= 100 ? '#10B981' : percent >= 50 ? '#3B82F6' : '#F59E0B';
                        return `<strong style="color: ${color};">${percent.toFixed(1)}%</strong>`;
                    }
                }
            ],
            actions: [
                {
                    type: 'edit',
                    label: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>Update Hours',
                    onClick: (row) => editHoursModal(row)
                }
            ],
            data: students,
            pageSize: 20,
            emptyMessage: 'No students found'
        });

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
    }
}

function editHoursModal(student) {
    const content = `
        <div style="padding: 10px 0;">
            <p style="margin-bottom: 20px; color: #6B7280; line-height: 1.6;">
                Update OJT hours for <strong>${student.first_name} ${student.last_name}</strong>
            </p>
            
            <div style="margin-bottom: 20px;">
                <div style="background: #F3F4F6; padding: 16px; border-radius: 8px; margin-bottom: 16px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div>
                            <p style="margin: 0; font-size: 12px; color: #6B7280;">Required Hours</p>
                            <p style="margin: 4px 0 0 0; font-size: 18px; font-weight: bold; color: #374151;">${student.required_hours} hrs</p>
                        </div>
                        <div>
                            <p style="margin: 0; font-size: 12px; color: #6B7280;">Current Progress</p>
                            <p style="margin: 4px 0 0 0; font-size: 18px; font-weight: bold; color: #3B82F6;">${parseFloat(student.completion_percentage).toFixed(1)}%</p>
                        </div>
                    </div>
                </div>
                
                <label style="display: block; margin-bottom: 10px; font-weight: 600; color: #374151; font-size: 14px;">
                    Completed Hours <span style="color: #EF4444;">*</span>
                </label>
                <div style="display: flex; align-items: center; gap: 12px;">
                    <button type="button" id="decrease-hours" style="width: 40px; height: 40px; border: 2px solid #E5E7EB; border-radius: 8px; background: white; cursor: pointer; font-size: 20px; font-weight: bold; color: #6B7280; transition: all 0.2s;">
                        −
                    </button>
                    <input type="number" id="hours-input" value="${student.completed_hours}" min="0" max="${student.required_hours * 2}" step="0.5" style="width: 100px; height: 40px; text-align: center; font-size: 18px; font-weight: bold; border: 2px solid #3B82F6; border-radius: 8px; color: #111827; -moz-appearance: textfield;" />
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
            </div>
        </div>
    `;
    
    const modal = createModal('edit-hours-modal', {
        title: `Update OJT Hours`,
        size: 'medium'
    });
    
    const footer = `
        <button class="btn-modal" data-modal-close>Cancel</button>
        <button class="btn-modal btn-approve" id="confirm-update-btn">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
            Update Hours
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
            if (current > 0) {
                hoursInput.value = Math.max(0, current - 0.5).toFixed(1);
            }
        });
        
        // Increase hours
        increaseBtn?.addEventListener('click', () => {
            const current = parseFloat(hoursInput.value);
            hoursInput.value = (current + 0.5).toFixed(1);
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
        
        document.getElementById('confirm-update-btn')?.addEventListener('click', async () => {
            const hours = parseFloat(hoursInput.value);
            
            if (isNaN(hours) || hours < 0) {
                showError('Please enter valid hours');
                return;
            }
            
            try {
                await client.put(`/admin/ojt/${student.progress_id}/hours`, {
                    completed_hours: hours
                });
                modal.close();
                showSuccess(`Hours updated for ${student.first_name} ${student.last_name}!`);
                
                setTimeout(() => loadStudentsTable(), 1000);
            } catch (error) {
                console.error('Error updating hours:', error);
                showError('Failed to update hours: ' + error.message);
            }
        });
    }, 0);
}
