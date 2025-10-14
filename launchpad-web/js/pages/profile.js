/**
 * Profile Page
 * View and edit user profile (CDC or Company)
 */

import { loadSidebar } from '../components.js';
import { initUserDropdown } from './dropdown.js';
import client from '../api/client.js';
import { showSuccess, showError } from '../utils/notifications.js';

let profileData = null;

document.addEventListener('DOMContentLoaded', async () => {
    // Check authentication
    if (!client.isAuthenticated()) {
        showError('Please login to access this page');
        setTimeout(() => window.location.href = 'login.html', 1500);
        return;
    }

    // Load sidebar (no active page since profile is not in sidebar)
    await loadSidebar();

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
    // Note: profile page lives under /pages/, so images base is ../images/
    const imgBase = '../images/logo/';
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

    document.getElementById('cdc-username').value = profile.username || '';
    document.getElementById('cdc-email').value = profile.email || '';
}

function showCompanyForm(profile) {
    document.getElementById('cdc-form').style.display = 'none';
    document.getElementById('company-form').style.display = 'block';

    document.getElementById('company-username').value = profile.username || '';
    document.getElementById('company-name').value = profile.company_name || '';
    document.getElementById('company-address').value = profile.company_address || '';
    document.getElementById('company-industry').value = profile.industry || '';
    document.getElementById('company-size').value = profile.company_size || '';
    document.getElementById('company-website').value = profile.company_website || '';
    document.getElementById('company-description').value = profile.description || '';
    document.getElementById('contact-person').value = profile.contact_person || '';
    document.getElementById('contact-email').value = profile.contact_email || '';
    document.getElementById('contact-phone').value = profile.contact_phone || '';
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
                contact_person: document.getElementById('contact-person').value.trim(),
                contact_email: document.getElementById('contact-email').value.trim(),
                contact_phone: document.getElementById('contact-phone').value.trim(),
                industry: document.getElementById('company-industry').value.trim(),
                company_size: document.getElementById('company-size').value.trim(),
                description: document.getElementById('company-description').value.trim()
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
