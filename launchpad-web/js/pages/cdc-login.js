/**
 * CDC Login Page Logic
 */

import { AuthAPI } from '../api/index.js';
import { showAlert, showLoading } from '../components.js';

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
        // Call login API
        const response = await AuthAPI.login(username, password, 'cdc');
        
        if (response.success) {
            showAlert('Login successful! Redirecting...', 'success');
            
            // Redirect to CDC dashboard
            setTimeout(() => {
                window.location.href = 'cdc-dashboard.html';
            }, 1000);
        }
    } catch (error) {
        showAlert(error.message || 'Login failed. Please check your credentials.', 'error');
        submitBtn.disabled = false;
        submitBtn.textContent = 'Login';
    }
});

