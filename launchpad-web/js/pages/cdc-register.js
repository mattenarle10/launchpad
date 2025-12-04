import client from '../api/client.js';
import { showSuccess, showError } from '../utils/notifications.js';

const form = document.getElementById('cdc-register-form');
const submitBtn = document.getElementById('submit-btn');
const passwordInput = document.getElementById('password');
const confirmPasswordInput = document.getElementById('confirm_password');
const passwordMatchHint = document.getElementById('password-match');

function updatePasswordMatchHint() {
    if (!passwordInput || !confirmPasswordInput || !passwordMatchHint) return;
    const pwd = passwordInput.value;
    const cfm = confirmPasswordInput.value;

    if (!pwd && !cfm) {
        passwordMatchHint.textContent = '';
        return;
    }

    if (pwd === cfm && pwd.length >= 8) {
        passwordMatchHint.textContent = 'Passwords match';
        passwordMatchHint.style.color = '#d1fae5';
    } else {
        passwordMatchHint.textContent = 'Passwords do not match';
        passwordMatchHint.style.color = '#fde2e2';
    }
}

if (passwordInput && confirmPasswordInput) {
    passwordInput.addEventListener('input', updatePasswordMatchHint);
    confirmPasswordInput.addEventListener('input', updatePasswordMatchHint);
}

form?.addEventListener('submit', async (e) => {
    e.preventDefault();

    const firstName = document.getElementById('first_name').value.trim();
    const lastName = document.getElementById('last_name').value.trim();
    const email = document.getElementById('email').value.trim();
    const username = document.getElementById('username').value.trim();
    const password = passwordInput.value;
    const confirmPassword = confirmPasswordInput.value;

    if (!firstName || !lastName || !email || !username || !password || !confirmPassword) {
        showError('Please fill in all required fields');
        return;
    }

    if (password.length < 8) {
        showError('Password must be at least 8 characters');
        return;
    }

    if (password !== confirmPassword) {
        showError('Passwords do not match');
        return;
    }

    submitBtn.disabled = true;
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Submitting...';

    try {
        const payload = {
            first_name: firstName,
            last_name: lastName,
            email,
            username,
            password
        };

        const response = await client.post('/cdc/register', payload, { skipAuth: true });

        if (response.success) {
            showSuccess('Registration submitted! Please wait for CDC admin approval.');
            form.reset();
            updatePasswordMatchHint();
        }
    } catch (error) {
        showError(error.message || 'Registration failed. Please try again.');
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    }
});
