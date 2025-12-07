import client from '../api/client.js';
import { showSuccess, showError } from '../utils/notifications.js';

const form = document.getElementById('cdc-register-form');
const submitBtn = document.getElementById('submit-btn');
const passwordInput = document.getElementById('password');
const confirmPasswordInput = document.getElementById('confirm_password');
const passwordMatchHint = document.getElementById('password-match');
const passwordRequirementsEl = document.getElementById('password-requirements');

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
    passwordInput.addEventListener('input', () => {
        updatePasswordMatchHint();
        updatePasswordRequirements(passwordInput.value);
    });
    confirmPasswordInput.addEventListener('input', updatePasswordMatchHint);
}

function updatePasswordRequirements(password) {
    if (!passwordRequirementsEl) return true;

    const requirements = {
        length: password.length >= 8,
        uppercase: /[A-Z]/.test(password),
        lowercase: /[a-z]/.test(password),
        number: /[0-9]/.test(password),
        special: /[!@#$%^&*()_+\-=[\]{};':"\\|,.<>\/?]/.test(password)
    };

    Object.entries(requirements).forEach(([key, met]) => {
        const el = passwordRequirementsEl.querySelector(`.requirement[data-check="${key}"]`);
        if (!el) return;
        const icon = el.querySelector('.check-icon');
        if (met) {
            el.classList.add('met');
            if (icon) icon.textContent = '✓';
        } else {
            el.classList.remove('met');
            if (icon) icon.textContent = '○';
        }
    });

    return Object.values(requirements).every(v => v);
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

    if (!updatePasswordRequirements(password)) {
        showError('Please meet all password requirements shown below the password field.');
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
