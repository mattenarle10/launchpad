/**
 * Company Registration Page Logic
 */

import CompanyAPI from '../api/company.js';
import { showAlert } from '../components.js';

// File preview handlers
const logoInput = document.getElementById('company_logo');
const moaInput = document.getElementById('moa_document');
const logoPreview = document.getElementById('logo-preview');
const moaPreview = document.getElementById('moa-preview');

// Logo preview
logoInput.addEventListener('change', (e) => {
    const file = e.target.files[0];
    if (file) {
        if (file.size > 10 * 1024 * 1024) {
            showAlert('Logo file too large. Maximum size is 10MB', 'error');
            logoInput.value = '';
            return;
        }
        
        const reader = new FileReader();
        reader.onload = (e) => {
            logoPreview.innerHTML = `
                <img src="${e.target.result}" alt="Logo Preview" style="max-width: 200px; max-height: 200px; border-radius: 8px;">
                <p>${file.name}</p>
            `;
        };
        reader.readAsDataURL(file);
    } else {
        logoPreview.innerHTML = '';
    }
});

// MOA preview
moaInput.addEventListener('change', (e) => {
    const file = e.target.files[0];
    if (file) {
        if (file.size > 10 * 1024 * 1024) {
            showAlert('MOA file too large. Maximum size is 10MB', 'error');
            moaInput.value = '';
            return;
        }
        
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = (e) => {
                moaPreview.innerHTML = `
                    <img src="${e.target.result}" alt="MOA Preview" style="max-width: 200px; max-height: 200px; border-radius: 8px;">
                    <p>${file.name}</p>
                `;
            };
            reader.readAsDataURL(file);
        } else {
            moaPreview.innerHTML = `<p>📄 ${file.name}</p>`;
        }
    } else {
        moaPreview.innerHTML = '';
    }
});

// Handle form submission
document.getElementById('register-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    // Get form values
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    // Validate password match
    if (password !== confirmPassword) {
        showAlert('Passwords do not match', 'error');
        return;
    }
    
    // Validate password length
    if (password.length < 8) {
        showAlert('Password must be at least 8 characters', 'error');
        return;
    }
    
    // Create FormData
    const formData = new FormData();
    formData.append('company_name', document.getElementById('company_name').value);
    formData.append('username', document.getElementById('username').value);
    formData.append('email', document.getElementById('email').value);
    formData.append('contact_num', document.getElementById('contact_num').value);
    formData.append('address', document.getElementById('address').value);
    formData.append('website', document.getElementById('website').value);
    formData.append('password', password);
    
    // Add files if selected
    if (logoInput.files[0]) {
        formData.append('company_logo', logoInput.files[0]);
    }
    if (moaInput.files[0]) {
        formData.append('moa_document', moaInput.files[0]);
    }
    
    // Show loading
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.disabled = true;
    submitBtn.textContent = 'Registering...';
    
    try {
        const response = await CompanyAPI.register(formData);
        
        if (response.success) {
            showAlert('Registration successful! Waiting for CDC approval. You will be able to login once approved.', 'success');
            
            // Redirect to login after 3 seconds
            setTimeout(() => {
                window.location.href = 'login.html?type=company';
            }, 3000);
        }
    } catch (error) {
        showAlert(error.message || 'Registration failed. Please try again.', 'error');
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    }
});

