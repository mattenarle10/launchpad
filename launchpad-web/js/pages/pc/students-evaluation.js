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

// Evaluation Categories with weighted scoring (based on USLS Trainee's Performance Evaluation)
const EVALUATION_CATEGORIES = [
    {
        id: 'competence',
        name: 'I. COMPETENCE',
        weight: 40, // 40%
        questions: [
            { id: 'c1', text: 'Exhibits workable knowledge and understanding of the assigned tasks' },
            { id: 'c2', text: 'Expresses willingness to work in groups' },
            { id: 'c3', text: 'Receptive to ideas of the other people' },
            { id: 'c4', text: 'Shows positive response to corrections made by his/her superiors' },
            { id: 'c5', text: 'Submits quality work' }
        ]
    },
    {
        id: 'attendance',
        name: 'II. ATTENDANCE AND PUNCTUALITY',
        weight: 30, // 30%
        questions: [
            { id: 'a1', text: 'Maintains regular OJT time' },
            { id: 'a2', text: 'Reports to work on time' },
            { id: 'a3', text: 'Notifies the Supervisor ahead of time if unable to report for duty' },
            { id: 'a4', text: 'Makes up for absences' }
        ]
    },
    {
        id: 'communication',
        name: 'III. COMMUNICATION SKILLS',
        weight: 20, // 20%
        questions: [
            { id: 'cm1', text: 'Proficiency in English' },
            { id: 'cm2', text: 'Ability to express ideas and deliver them clearly' }
        ]
    },
    {
        id: 'personality',
        name: 'IV. PERSONALITY',
        weight: 10, // 10%
        questions: [
            { id: 'p1', text: 'Physically neat in appearance' },
            { id: 'p2', text: 'Relates well with people in a pleasing manner and maintains sincerity and fairness when confronted with difficulties' },
            { id: 'p3', text: 'Possesses self-confidence in his/her ability and shows a high level of initiative' },
            { id: 'p4', text: 'Shows ability to manage time and identify priorities' }
        ]
    }
];

// Rating scale (1=Very Good to 4=Poor) - LOWER IS BETTER
const RATING_SCALE = [
    { value: 1, label: 'VG', fullLabel: 'Very Good', color: '#10B981' },
    { value: 2, label: 'G', fullLabel: 'Good', color: '#6366F1' },
    { value: 3, label: 'F', fullLabel: 'Fair', color: '#F59E0B' },
    { value: 4, label: 'P', fullLabel: 'Poor', color: '#EF4444' }
];

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
    // Single final evaluation (no monthly period selection)

    // Try to fetch latest evaluation for prefill (optional)
    let latestEvaluation = null;
    try {
        const res = await client.get(`/companies/students/${student.student_id}/evaluations`);
        const evals = res.data?.evaluations || [];
        if (evals.length > 0) {
            latestEvaluation = evals[0];
        }
    } catch (e) {
        console.error('Error fetching existing evaluations for prefill:', e);
    }

    // Generate categories and questions HTML
    const categoriesHtml = EVALUATION_CATEGORIES.map(cat => `
        <div class="eval-category" style="margin-bottom: 24px;">
            <div style="background: #4A6491; color: white; padding: 10px 16px; border-radius: 8px 8px 0 0; display: flex; justify-content: space-between; align-items: center;">
                <span style="font-weight: 600;">${cat.name}</span>
                <span style="background: rgba(255,255,255,0.2); padding: 4px 12px; border-radius: 20px; font-size: 12px;">${cat.weight}%</span>
            </div>
            <div style="border: 1px solid #E5E7EB; border-top: none; border-radius: 0 0 8px 8px; padding: 12px;">
                ${cat.questions.map((q, idx) => `
                    <div class="eval-question" style="padding: 12px; border-bottom: ${idx < cat.questions.length - 1 ? '1px solid #F3F4F6' : 'none'};">
                        <div style="font-size: 13px; color: #374151; margin-bottom: 10px;">
                            ${idx + 1}. ${q.text}
                        </div>
                        <div style="display: flex; gap: 8px; justify-content: flex-end;">
                            ${RATING_SCALE.map(r => `
                                <label style="cursor: pointer;">
                                    <input type="radio" name="${q.id}" value="${r.value}" data-category="${cat.id}" style="display: none;" required>
                                    <div class="rating-btn" data-value="${r.value}" style="
                                        width: 40px;
                                        height: 40px;
                                        display: flex;
                                        align-items: center;
                                        justify-content: center;
                                        border: 2px solid #E5E7EB;
                                        border-radius: 8px;
                                        background: white;
                                        font-weight: 600;
                                        font-size: 14px;
                                        color: #6B7280;
                                        transition: all 0.2s;
                                    ">${r.value}</div>
                                </label>
                            `).join('')}
                        </div>
                    </div>
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

            <!-- Evaluation Info -->
            <div style="background: #EFF6FF; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 13px; color: #3D5A7E;">
                This form records the student's <strong>final OJT performance evaluation</strong> for the semester.
            </div>
            
            <!-- Rating Scale Legend -->
            <div style="background: #FEF3C7; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px;">
                <div style="font-weight: 600; color: #92400E; font-size: 13px; margin-bottom: 8px;">Rating Scale (Please Encircle)</div>
                <div style="display: flex; gap: 20px; flex-wrap: wrap; font-size: 12px; color: #78350F;">
                    <span><strong>1</strong> - Very Good</span>
                    <span><strong>2</strong> - Good</span>
                    <span><strong>3</strong> - Fair</span>
                    <span><strong>4</strong> - Poor</span>
                </div>
            </div>
            
            <!-- Categories & Questions -->
            <div id="evaluation-questions" style="max-height: 450px; overflow-y: auto; padding-right: 8px;">
                ${categoriesHtml}
            </div>
            
            <!-- Comments Section -->
            <div style="margin-top: 16px;">
                <label style="display: block; font-size: 13px; color: #374151; margin-bottom: 8px;">
                    Comments or recommendations to help improve the trainee's performance:
                </label>
                <textarea id="evaluation-comments" rows="3" style="width: 100%; padding: 10px; border: 1px solid #E5E7EB; border-radius: 8px; font-size: 13px; resize: vertical;" placeholder="Optional comments..."></textarea>
            </div>
            
            <!-- Score Display -->
            <div style="background: linear-gradient(135deg, #4A6491 0%, #3D5A7E 100%); padding: 16px; border-radius: 8px; margin-top: 16px; color: white;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <div style="font-size: 14px; opacity: 0.9;">Weighted Score</div>
                        <div style="font-size: 11px; opacity: 0.7;">Competence 40% + Attendance 30% + Communication 20% + Personality 10%</div>
                    </div>
                    <div id="calculated-score" style="font-size: 36px; font-weight: 700;">--</div>
                </div>
                <div id="score-breakdown" style="margin-top: 12px; padding-top: 12px; border-top: 1px solid rgba(255,255,255,0.2); font-size: 12px; display: none;">
                    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; text-align: center;">
                        <div><div style="opacity: 0.7;">Competence</div><div id="score-competence" style="font-weight: 600;">-</div></div>
                        <div><div style="opacity: 0.7;">Attendance</div><div id="score-attendance" style="font-weight: 600;">-</div></div>
                        <div><div style="opacity: 0.7;">Communication</div><div id="score-communication" style="font-weight: 600;">-</div></div>
                        <div><div style="opacity: 0.7;">Personality</div><div id="score-personality" style="font-weight: 600;">-</div></div>
                    </div>
                </div>
            </div>
        </div>
        
        <style>
            .rating-btn:hover {
                border-color: #4A6491 !important;
                background: #EFF6FF !important;
                color: #4A6491 !important;
            }
            .rating-btn.selected {
                border-color: #4A6491 !important;
                background: #4A6491 !important;
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
        const ratingBtns = document.querySelectorAll('.rating-btn');
        const scoreDisplay = document.getElementById('calculated-score');
        const scoreBreakdown = document.getElementById('score-breakdown');

        // If we have a previous evaluation with stored answers, prefill the form
        if (latestEvaluation && latestEvaluation.answers) {
            try {
                EVALUATION_CATEGORIES.forEach(cat => {
                    cat.questions.forEach(q => {
                        const value = latestEvaluation.answers[q.id];
                        if (value === null || value === undefined) return;

                        const radio = document.querySelector(`input[name="${q.id}"][value="${value}"]`);
                        if (radio) {
                            radio.checked = true;
                            const btn = radio.closest('label')?.querySelector('.rating-btn');
                            if (btn) {
                                const questionContainer = btn.closest('.eval-question');
                                if (questionContainer) {
                                    questionContainer.querySelectorAll('.rating-btn').forEach(b => b.classList.remove('selected'));
                                }
                                btn.classList.add('selected');
                            }
                        }
                    });
                });

                if (typeof latestEvaluation.comments === 'string' && latestEvaluation.comments.length > 0) {
                    const commentsEl = document.getElementById('evaluation-comments');
                    if (commentsEl) {
                        commentsEl.value = latestEvaluation.comments;
                    }
                }

                // Recalculate score based on prefilled answers
                calculateScore();
            } catch (prefillErr) {
                console.error('Error prefilling evaluation form:', prefillErr);
            }
        }
        
        // Handle rating selection
        ratingBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                // Find the radio input and check it
                const radio = btn.parentElement.querySelector('input[type="radio"]');
                radio.checked = true;
                
                // Update visual selection within the same question
                const questionContainer = btn.closest('.eval-question');
                questionContainer.querySelectorAll('.rating-btn').forEach(b => {
                    b.classList.remove('selected');
                });
                btn.classList.add('selected');
                
                // Calculate score
                calculateScore();
            });
        });
        
        function calculateScore() {
            const categoryScores = {};
            let totalQuestions = 0;
            let answeredQuestions = 0;
            
            // Calculate score for each category
            EVALUATION_CATEGORIES.forEach(cat => {
                let catTotal = 0;
                let catAnswered = 0;
                
                cat.questions.forEach(q => {
                    totalQuestions++;
                    const selected = document.querySelector(`input[name="${q.id}"]:checked`);
                    if (selected) {
                        // Convert 1-4 scale to score (1=100, 2=75, 3=50, 4=25)
                        const rating = parseInt(selected.value);
                        const questionScore = (5 - rating) * 25; // 1->100, 2->75, 3->50, 4->25
                        catTotal += questionScore;
                        catAnswered++;
                        answeredQuestions++;
                    }
                });
                
                // Average score for this category
                categoryScores[cat.id] = catAnswered > 0 ? Math.round(catTotal / catAnswered) : null;
            });
            
            // Update category score displays
            document.getElementById('score-competence').textContent = categoryScores.competence !== null ? categoryScores.competence : '-';
            document.getElementById('score-attendance').textContent = categoryScores.attendance !== null ? categoryScores.attendance : '-';
            document.getElementById('score-communication').textContent = categoryScores.communication !== null ? categoryScores.communication : '-';
            document.getElementById('score-personality').textContent = categoryScores.personality !== null ? categoryScores.personality : '-';
            
            if (answeredQuestions === 0) {
                scoreDisplay.textContent = '--';
                scoreBreakdown.style.display = 'none';
                return null;
            }
            
            // Calculate weighted total
            // Competence 40% + Attendance 30% + Communication 20% + Personality 10%
            let weightedScore = 0;
            let weightedTotal = 0;
            
            if (categoryScores.competence !== null) {
                weightedScore += categoryScores.competence * 0.40;
                weightedTotal += 40;
            }
            if (categoryScores.attendance !== null) {
                weightedScore += categoryScores.attendance * 0.30;
                weightedTotal += 30;
            }
            if (categoryScores.communication !== null) {
                weightedScore += categoryScores.communication * 0.20;
                weightedTotal += 20;
            }
            if (categoryScores.personality !== null) {
                weightedScore += categoryScores.personality * 0.10;
                weightedTotal += 10;
            }
            
            // Normalize if not all categories answered
            const finalScore = weightedTotal > 0 ? Math.round(weightedScore * (100 / weightedTotal)) : 0;
            
            scoreDisplay.textContent = finalScore;
            scoreBreakdown.style.display = 'block';
            
            // Return score only if all questions answered
            return answeredQuestions === totalQuestions ? finalScore : null;
        }
        
        // Save button handler (single final evaluation)
        document.getElementById('save-evaluation-btn')?.addEventListener('click', async () => {
            const score = calculateScore();
            const comments = document.getElementById('evaluation-comments').value.trim();

            if (score === null) {
                showError('Please answer all 15 questions');
                return;
            }

            // Build answers object for backend prefill storage
            const answers = {};
            EVALUATION_CATEGORIES.forEach(cat => {
                cat.questions.forEach(q => {
                    const selected = document.querySelector(`input[name="${q.id}"]:checked`);
                    if (selected) {
                        answers[q.id] = parseInt(selected.value, 10);
                    }
                });
            });

            try {
                const res = await client.post(`/companies/students/${student.student_id}/evaluations`, {
                    evaluation_score: score,
                    comments: comments,
                    answers: answers
                });

                modal.close();
                const category = res.data.category;
                showSuccess(`${student.first_name} ${student.last_name} final evaluation submitted: ${score}/100 (${category})`);

                // Reload table so updated evaluation_rank is shown
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
