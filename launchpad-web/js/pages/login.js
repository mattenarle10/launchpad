/**
 * Universal Login Page Logic
 * Handles both CDC and Company login based on URL parameter
 */

import { AuthAPI } from '../api/index.js';
import { showAlert } from '../components.js';

// Get login type from URL parameter (?type=cdc or ?type=company)
const urlParams = new URLSearchParams(window.location.search);
const loginType = urlParams.get('type') || 'cdc'; // Default to CDC

// Update page title and subtitle based on type
const pageTitle = document.title;
const pageSubtitle = document.getElementById('page-subtitle');
const loginTitle = document.getElementById('login-title');

if (loginType === 'cdc') {
    document.title = 'CDC Login - LaunchPad';
    pageSubtitle.textContent = 'Career Development Center';
    loginTitle.textContent = 'CDC Login';
} else if (loginType === 'company') {
    document.title = 'Company Login - LaunchPad';
    pageSubtitle.textContent = 'Partner Company';
    loginTitle.textContent = 'Company Login';
}

// Handle form submission
document.getElementById('login-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value;
    
    // Validation
    if (!username || !password) {
        showAlert('Please fill in all fields', 'error');
        return;
    }
    
    // Show loading
    const submitBtn = e.target.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Logging in...';
    
    try {
        // Call login API with appropriate user type
        const response = await AuthAPI.login(username, password, loginType);
        
        if (response.success) {
            showAlert('Login successful! Redirecting...', 'success');
            
            // Redirect based on user type
            setTimeout(() => {
                if (loginType === 'cdc') {
                    window.location.href = 'cdc/dashboard.html';
                } else if (loginType === 'company') {
                    window.location.href = 'pc/dashboard.html';
                }
            }, 1000);
        }
    } catch (error) {
        showAlert(error.message || 'Login failed. Please check your credentials.', 'error');
        submitBtn.disabled = false;
        submitBtn.textContent = 'Login';
    }
});

