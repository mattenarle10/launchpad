# 🚀 LaunchPad API

Modern RESTful API backend for the LaunchPad OJT Tracking System.

## 📁 Current Structure

```
launchpad-api/
├── config/
│   ├── database.php          # Database connection
│   └── constants.php          # App constants
├── lib/
│   ├── response.php           # API response helper
│   ├── cors.php               # CORS middleware
│   └── auth.php               # JWT authentication
├── routes/
│   ├── auth/
│   │   ├── login.php          # User login
│   │   ├── logout.php         # User logout
│   │   └── refresh.php        # Refresh token
│   ├── students/
│   │   ├── register.php       # Student registration
│   │   ├── get-all.php        # List all students
│   │   ├── get-one.php        # Get student profile
│   │   ├── get-notifications.php
│   │   ├── get-reports.php
│   │   └── create-report.php
│   ├── admin/
│   │   ├── get-unverified-students.php
│   │   ├── verify-student.php
│   │   └── reject-student.php
│   └── companies/             # Coming soon!
├── uploads/
│   ├── student_ids/           # Student ID photos
│   ├── reports/               # Student reports
│   └── images/                # Profile pictures
├── public/
│   └── index.php              # Entry point & router
├── .htaccess                  # Apache routing
├── database.sql               # Database schema
└── TEST.md                    # Testing guide
```

## 🛠️ Tech Stack

- **PHP 8.0+** with modern features
- **MySQL/MariaDB** for data persistence
- **JWT** for stateless authentication
- **RESTful** API design

## 🚀 Getting Started

1. **Import Database**
   - Open phpMyAdmin: `http://localhost/phpmyadmin`
   - Import `database.sql`

2. **Set Permissions**
   ```bash
   chmod -R 777 uploads/
   ```

3. **Access API**
   - Base URL: `http://localhost/LaunchPad/launchpad-api/public`
   - Health check: `GET /health`

## 📡 Available Endpoints

### ✅ Authentication
- `POST /auth/login` - Login (CDC/Student/Company)
- `POST /auth/logout` - Logout
- `POST /auth/refresh` - Refresh token

### ✅ Students
- `POST /students/register` - Register new student (no auth)
- `GET /students` - List all students (CDC only)
- `GET /students/:id` - Get student profile
- `GET /students/:id/notifications` - Get notifications
- `GET /students/:id/reports` - Get submitted reports
- `POST /students/:id/reports` - Submit report (multipart)

### ✅ Admin (CDC)
- `GET /admin/unverified/students` - List pending students
- `POST /admin/verify/students/:id` - Approve student
- `DELETE /admin/reject/students/:id` - Reject student

### 🚧 Coming Soon
- Companies registration & verification
- Job postings
- OJT hours tracking
- Notifications system
- Student evaluations

## 🔒 Authentication

Protected endpoints require Bearer token:

```
Authorization: Bearer <your_jwt_token>
```

## 📦 Response Format

```json
{
  "success": true,
  "data": { ... },
  "message": "Operation successful",
  "timestamp": "2025-10-11T12:00:00+08:00"
}
```

## 🧪 Testing

See `TEST.md` for Postman setup and testing flows.

**Test Accounts:**
- CDC Admin: `cdc_admin` / `admin123`
- Test Student: `2021-00001` / `student123`

---

**Phase 1 Complete: Student Registration & Verification ✅**  
**Phase 2 In Progress: Company Registration 🚧**
