# LaunchPad Web Frontend

> Simple, minimal, and effective vanilla JS frontend for LaunchPad OJT Tracker

## 🎨 Design System

Following the legacy design colors:
- **Primary Blue**: `#395886`
- **Secondary Blue**: `#5784ba`
- **Background**: `#F0F3FA`
- **Font**: Poppins

## 📁 Project Structure

```
launchpad-web/
├── index.html              # Main entry point
├── assets/
│   ├── css/
│   │   └── main.css       # Global styles & utilities
│   ├── js/
│   │   ├── api/           # API modules (modular)
│   │   │   ├── client.js  # Base HTTP client
│   │   │   ├── auth.js    # Authentication endpoints
│   │   │   ├── student.js # Student endpoints
│   │   │   ├── company.js # Company endpoints
│   │   │   ├── cdc.js     # CDC admin endpoints
│   │   │   └── index.js   # Main API export
│   │   ├── router.js      # SPA routing
│   │   └── main.js        # App initialization
│   └── images/            # Images & logos
└── README.md
```

## 🚀 How to Use the API

### Import APIs

```javascript
// Import specific API
import { AuthAPI, StudentAPI, CompanyAPI, CDCAPI } from './api/index.js';

// Or import all at once
import API from './api/index.js';
```

### Authentication

```javascript
// Login
const response = await AuthAPI.login('username', 'password', 'student');
// user_type: 'cdc', 'student', or 'company'

// Logout
await AuthAPI.logout();

// Check if authenticated
if (AuthAPI.isAuthenticated()) {
    const user = AuthAPI.getCurrentUser();
}
```

### Student API

```javascript
// Register
const formData = new FormData();
formData.append('email', 'student@example.com');
formData.append('id_photo', file);
// ... other fields
await StudentAPI.register(formData);

// Get profile
await StudentAPI.getProfile(studentId);

// Get OJT progress
await StudentAPI.getOJTProgress(studentId);

// Submit daily report
const reportData = new FormData();
reportData.append('report_date', '2025-10-12');
reportData.append('hours_requested', 8);
reportData.append('report_file', file);
await StudentAPI.submitDailyReport(studentId, reportData);

// Get daily reports
await StudentAPI.getDailyReports(studentId, 'pending'); // or 'approved', 'rejected', null
```

### Company API

```javascript
// Register
const formData = new FormData();
formData.append('company_name', 'Acme Corp');
formData.append('company_logo', logoFile);
formData.append('moa_document', moaFile);
// ... other fields
await CompanyAPI.register(formData);

// Get profile
await CompanyAPI.getProfile(companyId);

// Get all companies
await CompanyAPI.getAll(page, pageSize);
```

### CDC Admin API

```javascript
// Student verification
await CDCAPI.getUnverifiedStudents();
await CDCAPI.verifyStudent(studentId);
await CDCAPI.rejectStudent(studentId);

// Company verification
await CDCAPI.getUnverifiedCompanies();
await CDCAPI.verifyCompany(companyId);
await CDCAPI.rejectCompany(companyId);

// Report management
await CDCAPI.getPendingReports();
await CDCAPI.reviewReport(reportId, 'approve');
await CDCAPI.reviewReport(reportId, 'reject', 'Insufficient details');

// OJT progress
await CDCAPI.getAllOJTProgress('in_progress'); // or 'completed', 'not_started', null
await CDCAPI.getOJTStats();
```

## 🎯 Features

- ✅ Modular API structure
- ✅ Simple SPA routing
- ✅ Reusable CSS utilities
- ✅ LocalStorage auth management
- ✅ FormData & JSON support
- ✅ Error handling
- ✅ Responsive design

## 🔧 Tech Stack

- **Pure HTML/CSS/JS** - No frameworks
- **ES6 Modules** - Clean imports/exports
- **Fetch API** - HTTP requests
- **LocalStorage** - Auth persistence
- **CSS Variables** - Themeable design

