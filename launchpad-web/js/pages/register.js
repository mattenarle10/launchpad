/**
 * Company Registration - 3 Step Form
 */

import CompanyAPI from '../api/company.js';
import { showAlert } from '../components.js';

// State
let currentStep = 1;
const totalSteps = 3;
const formData = {};

// Step titles
const stepTitles = {
    1: 'Step 1: Company Information',
    2: 'Step 2: Account Setup',
    3: 'Step 3: Documents'
};

// DOM Elements
const stepTitle = document.getElementById('step-title');
const backBtn = document.getElementById('back-btn');
const nextBtn = document.getElementById('next-btn');
const stepNavigation = document.getElementById('step-navigation');
const successNavigation = document.getElementById('success-navigation');
const passwordInput = document.getElementById('password');
const confirmPasswordInput = document.getElementById('confirm_password');
const passwordMatchHint = document.getElementById('password-match');

// Show specific step
function showStep(step) {
    // Hide all steps
    document.querySelectorAll('.step-content').forEach(el => el.classList.add('hidden'));
    
    // Show current step
    document.getElementById(`step-${step}`).classList.remove('hidden');
    
    // Update stepper
    document.querySelectorAll('.step-item').forEach((item, index) => {
        item.classList.remove('active', 'completed');
        if (index + 1 < step) item.classList.add('completed');
        if (index + 1 === step) item.classList.add('active');
    });
    
    // Update title
    stepTitle.textContent = stepTitles[step];
    
    // Update buttons
    backBtn.classList.toggle('hidden', step === 1);
    nextBtn.textContent = step === totalSteps ? 'Submit Registration' : 'Next →';
}

// Validate current step
function validateStep(step) {
    const stepEl = document.getElementById(`step-${step}`);
    const inputs = stepEl.querySelectorAll('input[required], textarea[required]');
    
    for (const input of inputs) {
        if (!input.value.trim()) {
            showAlert(`Please fill in all required fields`, 'error');
            input.focus();
            return false;
        }
    }
    
    // Step 2 specific validation
    if (step === 2) {
        const password = passwordInput.value;
        const confirmPassword = confirmPasswordInput.value;
        
        if (password.length < 8) {
            showAlert('Password must be at least 8 characters', 'error');
            return false;
        }
        
        if (password !== confirmPassword) {
            showAlert('Passwords do not match', 'error');
            return false;
        }
    }
    
    return true;
}

// Toggle password visibility
function setupPasswordToggle(buttonId, inputEl) {
    const button = document.getElementById(buttonId);
    if (!button || !inputEl) return;
    button.addEventListener('click', () => {
        const isPassword = inputEl.type === 'password';
        inputEl.type = isPassword ? 'text' : 'password';
        const eyeOpen = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>';
        const eyeOff = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a10.07 10.07 0 012.457-3.99m3.247-2.18A9.956 9.956 0 0112 5c4.477 0 8.268 2.943 9.542 7a10.07 10.07 0 01-4.043 5.197M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18"/></svg>';
        button.innerHTML = isPassword ? eyeOff : eyeOpen;
    });
}

// Live password match feedback
function setupPasswordMatch() {
    if (!passwordInput || !confirmPasswordInput || !passwordMatchHint) return;
    function updateHint() {
        const pwd = passwordInput.value;
        const cfm = confirmPasswordInput.value;
        if (!pwd && !cfm) { passwordMatchHint.textContent = ''; return; }
        if (cfm.length === 0) { passwordMatchHint.textContent = ''; return; }
        if (pwd === cfm && pwd.length >= 8) {
            passwordMatchHint.textContent = 'Passwords match';
            passwordMatchHint.style.color = '#d1fae5';
        } else {
            passwordMatchHint.textContent = 'Passwords do not match';
            passwordMatchHint.style.color = '#fde2e2';
        }
    }
    passwordInput.addEventListener('input', updateHint);
    confirmPasswordInput.addEventListener('input', updateHint);
}

// Save step data
function saveStepData(step) {
    const stepEl = document.getElementById(`step-${step}`);
    const inputs = stepEl.querySelectorAll('input, textarea');
    
    inputs.forEach(input => {
        if (input.type === 'file') {
            if (input.files[0]) formData[input.id] = input.files[0];
        } else {
            formData[input.id] = input.value;
        }
    });
}

// Next step
async function nextStep() {
    if (!validateStep(currentStep)) return;
    
    saveStepData(currentStep);
    
    if (currentStep === totalSteps) {
        await submitRegistration();
    } else {
        currentStep++;
        showStep(currentStep);
    }
}

// Previous step
function prevStep() {
    if (currentStep > 1) {
        currentStep--;
        showStep(currentStep);
    }
}

// Submit registration
async function submitRegistration() {
    nextBtn.disabled = true;
    nextBtn.textContent = 'Submitting...';
    
    try {
        // Create FormData
        const apiFormData = new FormData();
        apiFormData.append('company_name', formData.company_name);
        apiFormData.append('email', formData.email);
        apiFormData.append('contact_num', formData.contact_num || '');
        apiFormData.append('address', formData.address);
        apiFormData.append('website', formData.website || '');
        apiFormData.append('username', formData.username);
        apiFormData.append('password', formData.password);
        
        if (formData.company_logo) apiFormData.append('company_logo', formData.company_logo);
        if (formData.moa_document) apiFormData.append('moa_document', formData.moa_document);
        
        const response = await CompanyAPI.register(apiFormData);
        
        if (response.success) {
            // Show success state
            document.querySelectorAll('.step-content').forEach(el => el.classList.add('hidden'));
            document.getElementById('success-state').classList.remove('hidden');
            document.getElementById('stepper').style.display = 'none';
            stepTitle.style.display = 'none';
            stepNavigation.classList.add('hidden');
            successNavigation.classList.remove('hidden');
        }
    } catch (error) {
        showAlert(error.message || 'Registration failed. Please try again.', 'error');
        nextBtn.disabled = false;
        nextBtn.textContent = 'Submit Registration';
    }
}

// File preview handlers
function setupFilePreview(inputId, previewId) {
    const input = document.getElementById(inputId);
    const preview = document.getElementById(previewId);
    
    input.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (!file) {
            preview.innerHTML = '';
            return;
        }
        
        if (file.size > 10 * 1024 * 1024) {
            showAlert('File too large. Maximum size is 10MB', 'error');
            input.value = '';
            return;
        }
        
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = (e) => {
                preview.innerHTML = `<img src="${e.target.result}" alt="Preview"><p>${file.name}</p>`;
            };
            reader.readAsDataURL(file);
        } else {
            preview.innerHTML = `<p>📄 ${file.name}</p>`;
        }
    });
}

// Event listeners
backBtn.addEventListener('click', prevStep);
nextBtn.addEventListener('click', nextStep);

// Setup file previews
setupFilePreview('company_logo', 'logo-preview');
setupFilePreview('moa_document', 'moa-preview');

// Initialize
showStep(1);
setupPasswordToggle('toggle-password', passwordInput);
setupPasswordToggle('toggle-confirm-password', confirmPasswordInput);
setupPasswordMatch();

