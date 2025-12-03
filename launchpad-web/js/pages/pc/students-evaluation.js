/**
 * Students Evaluation Page
 * Partner Company evaluates their students using 10-question form (1-4 scale)
 * Total score is calculated from responses and scaled to 100
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

// 10 Evaluation Questions with 4 choices each
const EVALUATION_QUESTIONS = [
    {
        id: 'q1',
        question: 'Attendance and Punctuality',
        description: 'How consistent is the student in attending work and arriving on time?'
    },
    {
        id: 'q2',
        question: 'Quality of Work',
        description: 'How well does the student complete assigned tasks with accuracy and attention to detail?'
    },
    {
        id: 'q3',
        question: 'Productivity',
        description: 'How efficiently does the student complete work within expected timeframes?'
    },
    {
        id: 'q4',
        question: 'Initiative',
        description: 'Does the student take initiative and show willingness to learn new tasks?'
    },
    {
        id: 'q5',
        question: 'Communication Skills',
        description: 'How effectively does the student communicate with supervisors and colleagues?'
    },
    {
        id: 'q6',
        question: 'Teamwork',
        description: 'How well does the student collaborate and work with team members?'
    },
    {
        id: 'q7',
        question: 'Professionalism',
        description: 'Does the student demonstrate professional behavior and appropriate workplace conduct?'
    },
    {
        id: 'q8',
        question: 'Problem Solving',
        description: 'How well does the student handle challenges and find solutions?'
    },
    {
        id: 'q9',
        question: 'Adaptability',
        description: 'How well does the student adapt to changes and new situations?'
    },
    {
        id: 'q10',
        question: 'Overall Performance',
        description: 'Overall assessment of the student\'s internship performance'
    }
];

// Rating scale (1-4)
const RATING_SCALE = [
    { value: 1, label: 'Poor', description: 'Below expectations, needs significant improvement' },
    { value: 2, label: 'Fair', description: 'Meets some expectations, needs improvement' },
    { value: 3, label: 'Good', description: 'Meets expectations, performs well' },
    { value: 4, label: 'Excellent', description: 'Exceeds expectations, outstanding performance' }
];

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
    
    const evaluationsThisMonth = evalData?.evaluations_this_month ?? 0;
    const monthName = new Date(evalData?.current_year, evalData?.current_month - 1).toLocaleString('default', { month: 'long' });
    
    const firstHalfEval = evalData?.first_half_evaluation;
    const secondHalfEval = evalData?.second_half_evaluation;
    
    // Generate questions HTML
    const questionsHtml = EVALUATION_QUESTIONS.map((q, idx) => `
        <div class="eval-question" style="background: #F9FAFB; padding: 16px; border-radius: 8px; margin-bottom: 12px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
                <div>
                    <div style="font-weight: 600; color: #1F2937; font-size: 14px;">
                        ${idx + 1}. ${q.question}
                    </div>
                    <div style="font-size: 12px; color: #6B7280; margin-top: 4px;">
                        ${q.description}
                    </div>
                </div>
            </div>
            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                ${RATING_SCALE.map(r => `
                    <label style="flex: 1; min-width: 80px; cursor: pointer;">
                        <input type="radio" name="${q.id}" value="${r.value}" style="display: none;" required>
                        <div class="rating-option" data-value="${r.value}" style="
                            padding: 10px 8px;
                            text-align: center;
                            border: 2px solid #E5E7EB;
                            border-radius: 8px;
                            background: white;
                            transition: all 0.2s;
                        ">
                            <div style="font-weight: 700; font-size: 18px; color: #374151;">${r.value}</div>
                            <div style="font-size: 11px; color: #6B7280;">${r.label}</div>
                        </div>
                    </label>
                `).join('')}
            </div>
        </div>
    `).join('');
    
    const content = `
        <div style="padding: 10px 0;">
            <!-- Student Info -->
            <div style="display: flex; gap: 20px; margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid #E5E7EB;">
                <div>
                    <div style="font-size: 12px; color: #6B7280;">Student</div>
                    <div style="font-weight: 600; color: #1F2937;">${student.first_name} ${student.last_name}</div>
                </div>
                <div>
                    <div style="font-size: 12px; color: #6B7280;">ID Number</div>
                    <div style="font-weight: 600; color: #1F2937;">${student.id_num}</div>
                </div>
                <div>
                    <div style="font-size: 12px; color: #6B7280;">Course</div>
                    <div><span class="course-badge ${student.course.toLowerCase()}">${student.course}</span></div>
                </div>
            </div>
            
            <!-- Evaluation Status -->
            <div style="background: #EFF6FF; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#4A6491" stroke-width="2">
                        <path d="M3 3v18h18"></path>
                        <path d="M18 17V9"></path>
                        <path d="M13 17V5"></path>
                        <path d="M8 17v-3"></path>
                    </svg>
                    <span style="font-weight: 600; color: #3D5A7E; font-size: 14px;">${monthName} Evaluations</span>
                </div>
                <div style="display: flex; gap: 12px; align-items: center;">
                    <span style="font-size: 13px; color: ${firstHalfEval ? '#10B981' : '#9CA3AF'};">
                        1st-15th: ${firstHalfEval ? `${firstHalfEval.score}/100` : 'Pending'}
                    </span>
                    <span style="font-size: 13px; color: ${secondHalfEval ? '#10B981' : '#9CA3AF'};">
                        16th-End: ${secondHalfEval ? `${secondHalfEval.score}/100` : 'Pending'}
                    </span>
                    <span style="font-weight: 700; color: ${evaluationsThisMonth === 2 ? '#10B981' : '#F59E0B'};">${evaluationsThisMonth}/2</span>
                </div>
            </div>
            
            <!-- Period Selection -->
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #3D5A7E; font-size: 14px;">
                    Evaluation Period <span style="color: #EF4444;">*</span>
                </label>
                <select id="evaluation-period" style="width: 100%; padding: 12px; border: 2px solid #E5E7EB; border-radius: 8px; font-size: 14px;">
                    <option value="first_half" ${!firstHalfEval ? 'selected' : ''}>1st-15th of ${monthName}</option>
                    <option value="second_half" ${firstHalfEval && !secondHalfEval ? 'selected' : ''}>16th-End of ${monthName}</option>
                </select>
            </div>
            
            <!-- Rating Scale Legend -->
            <div style="background: #FEF3C7; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px;">
                <div style="font-weight: 600; color: #92400E; font-size: 13px; margin-bottom: 8px;">Rating Scale</div>
                <div style="display: flex; gap: 16px; flex-wrap: wrap; font-size: 12px; color: #78350F;">
                    <span><strong>1</strong> - Poor</span>
                    <span><strong>2</strong> - Fair</span>
                    <span><strong>3</strong> - Good</span>
                    <span><strong>4</strong> - Excellent</span>
                </div>
            </div>
            
            <!-- Questions -->
            <div id="evaluation-questions" style="max-height: 400px; overflow-y: auto; padding-right: 8px;">
                ${questionsHtml}
            </div>
            
            <!-- Score Display -->
            <div style="background: #F3F4F6; padding: 16px; border-radius: 8px; margin-top: 20px; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <div style="font-size: 12px; color: #6B7280;">Calculated Score</div>
                    <div style="font-size: 11px; color: #9CA3AF;">Based on your responses (10 questions × 4 max = 40, scaled to 100)</div>
                </div>
                <div id="calculated-score" style="font-size: 32px; font-weight: 700; color: #6366F1;">--</div>
            </div>
        </div>
        
        <style>
            .rating-option:hover {
                border-color: #6366F1 !important;
                background: #EEF2FF !important;
            }
            .rating-option.selected {
                border-color: #6366F1 !important;
                background: #6366F1 !important;
            }
            .rating-option.selected div {
                color: white !important;
            }
            #evaluation-questions::-webkit-scrollbar {
                width: 6px;
            }
            #evaluation-questions::-webkit-scrollbar-track {
                background: #F3F4F6;
                border-radius: 3px;
            }
            #evaluation-questions::-webkit-scrollbar-thumb {
                background: #D1D5DB;
                border-radius: 3px;
            }
        </style>
    `;
    
    const modal = createModal('evaluation-modal', {
        title: `Evaluate ${student.first_name} ${student.last_name}`,
        size: 'large'
    });
    
    const footer = `
        <button class="btn-modal" data-modal-close>Cancel</button>
        <button class="btn-modal btn-approve" id="save-evaluation-btn">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 4px;">
                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                <polyline points="17 21 17 13 7 13 7 21"></polyline>
                <polyline points="7 3 7 8 15 8"></polyline>
            </svg>
            Submit Evaluation
        </button>
    `;
    
    modal.setFooter(footer);
    modal.open(content);
    
    // Set up rating selection and score calculation
    setTimeout(() => {
        const ratingOptions = document.querySelectorAll('.rating-option');
        const scoreDisplay = document.getElementById('calculated-score');
        
        // Handle rating selection
        ratingOptions.forEach(option => {
            option.addEventListener('click', () => {
                // Find the radio input and check it
                const radio = option.parentElement.querySelector('input[type="radio"]');
                radio.checked = true;
                
                // Update visual selection
                const questionContainer = option.closest('.eval-question');
                questionContainer.querySelectorAll('.rating-option').forEach(opt => {
                    opt.classList.remove('selected');
                });
                option.classList.add('selected');
                
                // Calculate score
                calculateScore();
            });
        });
        
        function calculateScore() {
            let total = 0;
            let answered = 0;
            
            EVALUATION_QUESTIONS.forEach(q => {
                const selected = document.querySelector(`input[name="${q.id}"]:checked`);
                if (selected) {
                    total += parseInt(selected.value);
                    answered++;
                }
            });
            
            if (answered === 0) {
                scoreDisplay.textContent = '--';
                scoreDisplay.style.color = '#9CA3AF';
            } else {
                // Scale to 100: (total / 40) * 100
                const score = Math.round((total / 40) * 100);
                scoreDisplay.textContent = score;
                
                // Color based on score
                if (score >= 81) {
                    scoreDisplay.style.color = '#10B981';
                } else if (score >= 61) {
                    scoreDisplay.style.color = '#6366F1';
                } else if (score >= 41) {
                    scoreDisplay.style.color = '#F59E0B';
                } else {
                    scoreDisplay.style.color = '#EF4444';
                }
            }
            
            return answered === 10 ? Math.round((total / 40) * 100) : null;
        }
        
        // Save button handler
        document.getElementById('save-evaluation-btn')?.addEventListener('click', async () => {
            const score = calculateScore();
            const period = document.getElementById('evaluation-period').value;
            
            if (score === null) {
                showError('Please answer all 10 questions');
                return;
            }
            
            try {
                const res = await client.post(`/companies/students/${student.student_id}/evaluations`, {
                    evaluation_score: score,
                    evaluation_period: period
                });
                
                modal.close();
                const category = res.data.category;
                const evalCount = res.data.evaluations_this_month;
                const periodText = period === 'first_half' ? '1st-15th' : '16th-End';
                showSuccess(`${student.first_name} ${student.last_name} evaluated: ${score}/100 (${category}) - ${periodText} - ${evalCount}/2 this month`);
                
                // Reload table
                setTimeout(() => loadStudentsTable(), 1000);
            } catch (error) {
                console.error('Error saving evaluation:', error);
                showError('Failed to save evaluation: ' + (error.response?.data?.message || error.message));
            }
        });
    }, 100);
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
