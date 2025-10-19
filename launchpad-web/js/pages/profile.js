/**
 * Profile Page
 * View and edit user profile (CDC or Company)
 */

import { loadSidebar } from '../components.js';
import { initUserDropdown } from './dropdown.js';
import client from '../api/client.js';
import { showSuccess, showError } from '../utils/notifications.js';
import { getSidebarMode } from '../utils/sidebar-helper.js';

let profileData = null;

document.addEventListener('DOMContentLoaded', async () => {
    // Check authentication
    if (!client.isAuthenticated()) {
        showError('Please login to access this page');
        setTimeout(() => window.location.href = 'login.html', 1500);
        return;
    }

    // Load sidebar (no active page). Use 'pc' sidebar for partner companies
    await loadSidebar(undefined, getSidebarMode());

    // Initialize dropdown
    initUserDropdown(
        'user-menu-toggle',
        'user-dropdown',
        () => {
            client.clearAuth();
            window.location.href = 'login.html';
        }
    );

    // Load profile
    await loadProfile();

    // Setup form submission
    document.getElementById('profile-form')?.addEventListener('submit', handleSubmit);

    // Logo upload handlers (company only)
    const fileInput = document.getElementById('company-logo-file');
    const fileNameEl = document.getElementById('company-logo-filename');
    const previewEl = document.getElementById('company-logo-preview');
    const uploadBtn = document.getElementById('upload-logo-btn');

    if (fileInput) {
        fileInput.addEventListener('change', () => {
            const f = fileInput.files?.[0];
            if (!f) return;
            fileNameEl && (fileNameEl.textContent = f.name);
            const reader = new FileReader();
            reader.onload = (e) => {
                if (previewEl && typeof e.target?.result === 'string') previewEl.src = e.target.result;
            };
            reader.readAsDataURL(f);
        });
    }

    if (uploadBtn) {
        uploadBtn.addEventListener('click', async () => {
            const f = fileInput?.files?.[0];
            if (!f) {
                showError('Please choose a logo image first');
                return;
            }
            const fd = new FormData();
            fd.append('logo', f);
            try {
                const res = await client.post('/profile/logo', fd);
                const file = res.data?.company_logo;
                if (file) {
                    const pathPrefix = window.location.hostname === 'localhost' ? '/LaunchPad' : '';
                    const absolute = `${window.location.origin}${pathPrefix}/launchpad-api/uploads/company_logos/${file}`;
                    if (previewEl) previewEl.src = absolute;
                    showSuccess('Company logo updated');
                } else {
                    showSuccess('Logo uploaded');
                }
            } catch (err) {
                console.error('Logo upload failed:', err);
                showError('Failed to upload logo: ' + err.message);
            }
        });
    }
});

async function loadProfile() {
    try {
        const response = await client.get('/profile');
        profileData = response.data;

        // Update header
        updateHeader(profileData);

        // Show appropriate form based on role
        if (profileData.role === 'cdc') {
            showCDCForm(profileData);
        } else if (profileData.role === 'company') {
            showCompanyForm(profileData);
        }

    } catch (error) {
        console.error('Error loading profile:', error);
        showError('Failed to load profile: ' + error.message);
    }
}

function updateHeader(profile) {
    let nameText = 'User';
    let roleText = 'Loading...';

    // Choose avatar image based on role
    // Use absolute base to avoid relative path issues
    const pathPrefix = window.location.hostname === 'localhost' ? '/LaunchPad' : '';
    const imgBase = `${pathPrefix}/launchpad-web/images/logo/`;
    const avatarFile = profile.role === 'cdc'
        ? 'cdc-avatar.png'
        : (profile.role === 'company' ? 'launchpad.png' : 'pc.png');
    const avatarSrc = imgBase + avatarFile;

    if (profile.role === 'cdc') {
        nameText = profile.username;
        roleText = 'Career Development Centre';
    } else if (profile.role === 'company') {
        nameText = profile.company_name;
        roleText = 'Partner Company';
    }

    // Update avatar images
    const headerImg = document.getElementById('header-avatar');
    const badgeImg = document.getElementById('badge-avatar');
    const dropdownImg = document.getElementById('dropdown-avatar');
    if (headerImg) headerImg.src = avatarSrc;
    if (badgeImg) badgeImg.src = avatarSrc;
    if (dropdownImg) dropdownImg.src = avatarSrc;

    // Update name and role
    const headerName = document.getElementById('header-name');
    const headerRole = document.getElementById('header-role');
    const dropdownName = document.getElementById('dropdown-user-name');
    if (headerName) headerName.textContent = nameText;
    if (headerRole) headerRole.textContent = roleText;
    if (dropdownName) dropdownName.textContent = nameText;
}

function showCDCForm(profile) {
    document.getElementById('cdc-form').style.display = 'block';
    document.getElementById('company-form').style.display = 'none';

    // Enable CDC form fields, disable company fields
    toggleFormInputs('cdc-form', true);
    toggleFormInputs('company-form', false);

    document.getElementById('cdc-username').value = profile.username || '';
    document.getElementById('cdc-email').value = profile.email || '';
}

function showCompanyForm(profile) {
    document.getElementById('cdc-form').style.display = 'none';
    document.getElementById('company-form').style.display = 'block';

    // Enable company form fields, disable CDC fields
    toggleFormInputs('company-form', true);
    toggleFormInputs('cdc-form', false);

    document.getElementById('company-username').value = profile.username || '';
    document.getElementById('company-name').value = profile.company_name || '';
    document.getElementById('company-address').value = profile.company_address || '';
    document.getElementById('company-website').value = profile.company_website || '';
    document.getElementById('contact-email').value = profile.contact_email || '';
    document.getElementById('contact-phone').value = profile.contact_phone || '';

    // Set logo preview if present
    const previewEl = document.getElementById('company-logo-preview');
    const fileNameEl = document.getElementById('company-logo-filename');
    if (profile.company_logo && previewEl) {
        const pathPrefix = window.location.hostname === 'localhost' ? '/LaunchPad' : '';
        const absolute = `${window.location.origin}${pathPrefix}/launchpad-api/uploads/company_logos/${profile.company_logo}`;
        previewEl.src = absolute;
        if (fileNameEl) fileNameEl.textContent = profile.company_logo;
    }
}

// Disable/enable inputs inside a form container to prevent HTML5 validation on hidden forms
function toggleFormInputs(containerId, enabled) {
    const container = document.getElementById(containerId);
    if (!container) return;
    const controls = container.querySelectorAll('input, select, textarea, button');
    controls.forEach(ctrl => {
        if (enabled) {
            ctrl.removeAttribute('disabled');
        } else {
            ctrl.setAttribute('disabled', 'disabled');
            // Also remove required to avoid validation errors
            ctrl.removeAttribute('required');
        }
    });
}

async function handleSubmit(e) {
    e.preventDefault();

    const saveBtn = document.getElementById('save-btn');
    saveBtn.disabled = true;
    saveBtn.textContent = 'Saving...';

    try {
        let updateData = {};

        if (profileData.role === 'cdc') {
            updateData = {
                email: document.getElementById('cdc-email').value.trim()
            };
        } else if (profileData.role === 'company') {
            updateData = {
                company_name: document.getElementById('company-name').value.trim(),
                company_address: document.getElementById('company-address').value.trim(),
                company_website: document.getElementById('company-website').value.trim(),
                contact_email: document.getElementById('contact-email').value.trim(),
                contact_phone: document.getElementById('contact-phone').value.trim()
            };
        }

        await client.put('/profile', updateData);
        showSuccess('Profile updated successfully!');

        // Reload profile to get updated data
        setTimeout(() => loadProfile(), 1000);

    } catch (error) {
        console.error('Error updating profile:', error);
        showError('Failed to update profile: ' + error.message);
    } finally {
        saveBtn.disabled = false;
        saveBtn.textContent = 'Save Changes';
    }
}
