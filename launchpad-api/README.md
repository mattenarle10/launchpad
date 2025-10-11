# 🚀 LaunchPad API

Modern RESTful API backend for the LaunchPad OJT Tracking System.

## 📁 Structure

```
launchpad-api/
├── config/
│   ├── database.php          # Database connection & credentials
│   └── constants.php          # App-wide constants
├── src/
│   ├── Controllers/          # Request handlers
│   ├── Models/               # Database models
│   ├── Middleware/           # Auth, CORS, validation
│   └── Utils/                # Helpers, response formatters
├── routes/
│   ├── api.php               # Main API routes
│   ├── auth.php              # Authentication routes
│   ├── students.php          # Student endpoints
│   ├── companies.php         # Company endpoints
│   └── admin.php             # CDC/PC admin endpoints
├── uploads/                  # File storage (reports, images)
├── public/
│   └── index.php             # Entry point
├── .htaccess                 # Apache routing
├── composer.json             # PHP dependencies
└── database.sql              # Database schema
```

## 🛠️ Tech Stack

- **PHP 8.0+** with modern features
- **MySQL/MariaDB** for data persistence
- **JWT** for stateless authentication
- **RESTful** API design

## 🚀 Getting Started

1. **Install Dependencies**
   ```bash
   cd launchpad-api
   composer install
   ```

2. **Configure Database**
   - Create database: `launchpad_db`
   - Import `database.sql`
   - Update credentials in `config/database.php`

3. **Set Permissions**
   ```bash
   chmod -R 777 uploads/
   ```

4. **Access API**
   - Base URL: `http://localhost/LaunchPad/launchpad-api/public`
   - Health check: `GET /health`

## 📡 API Endpoints

### Authentication
- `POST /auth/login` - Login (CDC/PC/Student/Company)
- `POST /auth/logout` - Logout
- `POST /auth/refresh` - Refresh token

### Students
- `GET /students` - List all students (admin)
- `GET /students/:id` - Get student details
- `POST /students` - Register student
- `PUT /students/:id` - Update student
- `DELETE /students/:id` - Delete student
- `GET /students/:id/ojt` - Get OJT progress
- `PUT /students/:id/ojt` - Update OJT hours
- `GET /students/:id/notifications` - Get notifications
- `GET /students/:id/reports` - Get submitted reports
- `POST /students/:id/reports` - Submit report (multipart)

### Companies
- `GET /companies` - List companies
- `GET /companies/:id` - Get company details
- `POST /companies` - Register company
- `PUT /companies/:id` - Update company
- `DELETE /companies/:id` - Delete company
- `GET /companies/:id/students` - List assigned students
- `POST /companies/:id/evaluate` - Submit student evaluation

### Job Postings
- `GET /jobs` - List all jobs
- `GET /jobs/:id` - Get job details
- `POST /jobs` - Create job posting (company)
- `PUT /jobs/:id` - Update job posting
- `DELETE /jobs/:id` - Delete job posting

### Admin (CDC/PC)
- `POST /admin/verify/students/:id` - Verify student
- `POST /admin/verify/companies/:id` - Verify company
- `DELETE /admin/reject/students/:id` - Reject student
- `DELETE /admin/reject/companies/:id` - Reject company
- `POST /admin/notifications` - Broadcast notification
- `GET /admin/stats` - Dashboard statistics

## 🔒 Authentication

All protected endpoints require Bearer token:

```bash
Authorization: Bearer <your_jwt_token>
```

## 📦 Response Format

```json
{
  "success": true,
  "data": { ... },
  "message": "Operation successful",
  "timestamp": "2025-10-11T12:00:00Z"
}
```

## 🧪 Development

- Enable error reporting in `config/constants.php`
- Check logs in `logs/` directory
- Use Postman collection (coming soon)

---

**Built with ❤️ for modern PHP development**

