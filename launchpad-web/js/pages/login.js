/**
 * Universal Login Page Logic
 * Handles both CDC and Company login based on URL parameter
 */

import { AuthAPI } from '../api/index.js';
import { showSuccess, showError, showWarning } from '../utils/notifications.js';

// Get login type from URL parameter (?type=cdc or ?type=company)
const urlParams = new URLSearchParams(window.location.search);
const rawType = urlParams.get('type');
const loginType = rawType ? rawType.toLowerCase() : 'cdc'; // Default to CDC, case-insensitive

// Update page title and subtitle based on type
const pageTitle = document.title;
const pageSubtitle = document.getElementById('page-subtitle');
const loginTitle = document.getElementById('login-title');

if (loginType === 'cdc') {
    document.title = 'CDC Login - LaunchPad';
    pageSubtitle.textContent = 'Career Development Center';
    loginTitle.textContent = 'CDC Login';
    // Hide register link for CDC
    const registerLink = document.getElementById('register-link');
    if (registerLink) registerLink.style.display = 'none';
} else if (loginType === 'company') {
    document.title = 'Company Login - LaunchPad';
    pageSubtitle.textContent = 'Partner Company';
    loginTitle.textContent = 'Company Login';
    // Register link stays visible for company
}

// Handle form submission
document.getElementById('login-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value;
    
    // Validation
    if (!username || !password) {
        showError('Please fill in all fields');
        return;
    }
    
    // Show loading
    const submitBtn = e.target.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Logging in...';
    
    try {
        // Call login API with appropriate user type
        console.log('Attempting login:', { username, userType: loginType });
        const response = await AuthAPI.login(username, password, loginType);
        console.log('Login response:', response);
        
        if (response.success) {
            showSuccess('Login successful! Redirecting...');
            
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
        console.error('Login error:', error);
        
        const errorMessage = error.message || 'Login failed';
        
        // Check for specific error types
        if (errorMessage.toLowerCase().includes('pending verification') || 
            errorMessage.toLowerCase().includes('pending approval')) {
            showWarning(errorMessage);
        } else if (errorMessage.toLowerCase().includes('invalid credentials') || 
                   errorMessage.toLowerCase().includes('unauthorized')) {
            showError('Invalid username or password. Please try again.');
        } else if (errorMessage.toLowerCase().includes('network') || 
                   errorMessage.toLowerCase().includes('fetch')) {
            showError('Network error. Please check your connection and try again.');
        } else {
            showError(errorMessage);
        }
        
        submitBtn.disabled = false;
        submitBtn.textContent = 'Login';
    }
});

