/**
 * CDC Notifications Page
 * Manage and send notifications to students
 */

import { loadSidebar, loadUserInfo } from '../../components.js';
import { initUserDropdown } from '../dropdown.js';
import client from '../../api/client.js';
import { showError, showSuccess } from '../../utils/notifications.js';
import { getSidebarMode } from '../../utils/sidebar-helper.js';
import DataTable from '../table.js';
import { createModal } from '../../utils/modal.js';

let allNotifications = [];
let dataTable = null;
let allStudents = [];
let currentModal = null;

async function loadNotificationsTable() {
    const tableWrapper = document.getElementById('table-wrapper');
    tableWrapper.innerHTML = '<div class="loading"><p>Loading notifications...</p></div>';

    try {
        const res = await client.get('/notifications');
        allNotifications = res.data?.data || [];

        // Create DataTable
        dataTable = new DataTable({
            containerId: 'table-wrapper',
            columns: [
                { key: 'title', label: 'Title', sortable: true },
                { 
                    key: 'message', 
                    label: 'Message', 
                    sortable: false,
                    format: (value) => value.length > 100 ? value.substring(0, 100) + '...' : value
                },
                { 
                    key: 'recipient_type', 
                    label: 'Recipients', 
                    sortable: true,
                    format: (value, row) => {
                        if (value === 'all') {
                            return `<span class="course-badge">All Students (${row.recipients_count})</span>`;
                        } else {
                            return `<span class="course-badge" style="background: #F59E0B;">${row.recipients_count} Students</span>`;
                        }
                    }
                },
                { 
                    key: 'read_count', 
                    label: 'Read', 
                    sortable: true,
                    format: (value, row) => {
                        if (row.recipient_type === 'all') {
                            return '<span style="color: #6B7280;">N/A</span>';
                        }
                        return value !== null ? `${value} / ${row.recipients_count}` : '0 / ' + row.recipients_count;
                    }
                },
                { key: 'created_by_name', label: 'Sent By', sortable: true },
                { 
                    key: 'created_at', 
                    label: 'Date', 
                    sortable: true,
                    format: (value) => new Date(value).toLocaleString()
                }
            ],
            actions: [
                {
                    type: 'view',
                    label: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>View',
                    onClick: (row) => viewNotification(row)
                },
                {
                    type: 'delete',
                    label: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>Delete',
                    onClick: (row) => deleteNotification(row.notification_id, row.title)
                }
            ],
            data: allNotifications,
            pageSize: 10,
            emptyMessage: 'No notifications sent yet'
        });

    } catch (error) {
        console.error('Error loading notifications:', error);
        tableWrapper.innerHTML = `
            <div class="empty-state">
                <div class="empty-state-text">Error loading notifications</div>
                <div class="empty-state-subtext">${error.message}</div>
            </div>
        `;
        showError('Failed to load notifications: ' + error.message);
    }
}

function viewNotification(notification) {
    const content = `
        <div style="padding: 10px 0;">
            <div class="detail-row" style="margin-bottom: 16px;">
                <span class="detail-label" style="font-weight: 600; color: #6B7280;">Recipients:</span>
                <span class="detail-value">
                    ${notification.recipient_type === 'all' 
                        ? `<span class="course-badge">All Students (${notification.recipients_count})</span>`
                        : `<span class="course-badge" style="background: #F59E0B;">${notification.recipients_count} Students</span>`
                    }
                </span>
            </div>
            
            ${notification.recipient_type === 'specific' ? `
            <div class="detail-row" style="margin-bottom: 16px;">
                <span class="detail-label" style="font-weight: 600; color: #6B7280;">Read Count:</span>
                <span class="detail-value">${notification.read_count || 0} / ${notification.recipients_count}</span>
            </div>
            ` : ''}
            
            <div class="detail-row" style="margin-bottom: 16px;">
                <span class="detail-label" style="font-weight: 600; color: #6B7280;">Sent By:</span>
                <span class="detail-value">${notification.created_by_name}</span>
            </div>
            
            <div class="detail-row" style="margin-bottom: 16px;">
                <span class="detail-label" style="font-weight: 600; color: #6B7280;">Date:</span>
                <span class="detail-value">${new Date(notification.created_at).toLocaleString()}</span>
            </div>

            <div style="border-top: 2px solid #E5E7EB; padding-top: 16px; margin-top: 16px;">
                <h4 style="font-weight: 600; color: #111827; margin-bottom: 8px;">Message</h4>
                <p style="color: #374151; line-height: 1.6; white-space: pre-wrap;">${notification.message}</p>
            </div>
        </div>
    `;
    
    if (currentModal) {
        currentModal.destroy();
    }
    
    currentModal = createModal('notification-details-modal', {
        title: notification.title,
        size: 'large'
    });
    
    currentModal.open(content);
}

async function deleteNotification(notificationId, title) {
    if (!confirm(`Are you sure you want to delete "${title}"?`)) {
        return;
    }

    try {
        await client.delete(`/notifications/${notificationId}`);
        showSuccess('Notification deleted successfully!');
        setTimeout(() => loadNotificationsTable(), 500);
    } catch (error) {
        console.error('Error deleting notification:', error);
        showError('Failed to delete notification: ' + error.message);
    }
}

async function openSendNotificationModal() {
    // Load students for dropdown
    try {
        const res = await client.get('/students?pageSize=1000');
        allStudents = res.data?.data || [];
    } catch (error) {
        console.error('Error loading students:', error);
        showError('Failed to load students');
        return;
    }

    const content = `
        <div style="padding: 10px 0;">
            <div class="form-group" style="margin-bottom: 16px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #374151;">
                    Title <span style="color: #EF4444;">*</span>
                </label>
                <input 
                    type="text" 
                    id="notif-title" 
                    class="form-input" 
                    placeholder="e.g. Important Announcement"
                    style="width: 100%; padding: 12px; border: 2px solid #E5E7EB; border-radius: 8px; font-size: 14px;"
                    required
                >
            </div>

            <div class="form-group" style="margin-bottom: 16px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #374151;">
                    Message <span style="color: #EF4444;">*</span>
                </label>
                <textarea 
                    id="notif-message" 
                    class="form-input" 
                    rows="5"
                    placeholder="Enter your message here..."
                    style="width: 100%; padding: 12px; border: 2px solid #E5E7EB; border-radius: 8px; font-size: 14px; resize: vertical;"
                    required
                ></textarea>
            </div>

            <div class="form-group" style="margin-bottom: 16px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #374151;">
                    Send To <span style="color: #EF4444;">*</span>
                </label>
                <select 
                    id="recipient-type" 
                    class="form-input" 
                    style="width: 100%; padding: 12px; border: 2px solid #E5E7EB; border-radius: 8px; font-size: 14px;"
                    required
                >
                    <option value="all">All Students</option>
                    <option value="specific">Specific Students</option>
                </select>
            </div>

            <div class="form-group" id="students-selector" style="margin-bottom: 16px; display: none;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #374151;">
                    Select Students <span style="color: #EF4444;">*</span>
                </label>
                <div style="max-height: 200px; overflow-y: auto; border: 2px solid #E5E7EB; border-radius: 8px; padding: 12px;">
                    ${allStudents.map(student => `
                        <label style="display: block; padding: 8px; cursor: pointer; border-radius: 4px; transition: background 0.2s;" onmouseover="this.style.background='#F3F4F6'" onmouseout="this.style.background='transparent'">
                            <input type="checkbox" class="student-checkbox" value="${student.student_id}" style="margin-right: 8px;">
                            ${student.first_name} ${student.last_name} (${student.id_num})
                        </label>
                    `).join('')}
                </div>
            </div>
        </div>
    `;
    
    if (currentModal) {
        currentModal.destroy();
    }
    
    currentModal = createModal('send-notification-modal', {
        title: 'Send Notification',
        size: 'large'
    });
    
    const footer = `
        <button class="btn-modal" data-modal-close>Cancel</button>
        <button class="btn-modal btn-approve" id="send-notif-btn">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;">
                <line x1="22" y1="2" x2="11" y2="13"></line>
                <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
            </svg>
            Send Notification
        </button>
    `;
    
    currentModal.setFooter(footer);
    currentModal.open(content);
    
    // Setup event handlers after modal is opened
    setTimeout(() => {
        // Recipient type change handler
        const recipientTypeSelect = document.getElementById('recipient-type');
        const studentsSelector = document.getElementById('students-selector');
        
        if (recipientTypeSelect) {
            recipientTypeSelect.addEventListener('change', (e) => {
                if (studentsSelector) {
                    studentsSelector.style.display = e.target.value === 'specific' ? 'block' : 'none';
                }
            });
        }

        // Send notification button handler
        const sendBtn = document.getElementById('send-notif-btn');
        if (sendBtn) {
            sendBtn.addEventListener('click', async (e) => {
                e.preventDefault();
                
                // Disable button and show loading state
                sendBtn.disabled = true;
                const originalContent = sendBtn.innerHTML;
                sendBtn.innerHTML = '<span>Sending...</span>';
                
                try {
                    await sendNotification();
                } catch (error) {
                    // Re-enable button on error
                    sendBtn.disabled = false;
                    sendBtn.innerHTML = originalContent;
                }
            });
        }
        
        // Setup close button handlers
        const closeButtons = document.querySelectorAll('[data-modal-close]');
        closeButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                if (currentModal) {
                    currentModal.close();
                }
            });
        });
    }, 100);
}

async function sendNotification() {
    const title = document.getElementById('notif-title').value.trim();
    const message = document.getElementById('notif-message').value.trim();
    const recipientType = document.getElementById('recipient-type').value;
    
    if (!title || !message) {
        showError('Title and message are required');
        return;
    }

    const data = {
        title,
        message,
        recipient_type: recipientType
    };

    if (recipientType === 'specific') {
        const checkboxes = document.querySelectorAll('.student-checkbox:checked');
        if (checkboxes.length === 0) {
            showError('Please select at least one student');
            return;
        }
        data.student_ids = Array.from(checkboxes).map(cb => parseInt(cb.value));
    }

    try {
        await client.post('/notifications', data);
        showSuccess('Notification sent successfully!');
        
        // Close modal properly
        if (currentModal) {
            currentModal.close();
        }

        // Reload table
        setTimeout(() => loadNotificationsTable(), 500);
    } catch (error) {
        console.error('Error sending notification:', error);
        showError('Failed to send notification: ' + error.message);
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
    await loadSidebar('notifications', getSidebarMode());
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
    
    // Load notifications table
    await loadNotificationsTable();

    // Setup send notification button
    document.getElementById('send-notification-btn')?.addEventListener('click', () => {
        openSendNotificationModal();
    });
});
