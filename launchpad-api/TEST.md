# 🧪 LaunchPad API Testing Guide

## 🚀 Setup Steps

### 1. Import Database
1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Click "Import" tab
3. Choose file: `database.sql`
4. Click "Go"
5. You should see `launchpad_db` database created with 3 tables:
   - `unverified_students`
   - `verified_students`
   - `cdc_users`

### 2. Test Base URL
```
http://localhost/LaunchPad/launchpad-api/public
```

---

## 📋 Test Accounts

### CDC Admin
- **Username**: `cdc_admin`
- **Password**: `admin123`
- **User Type**: `cdc`

### Verified Student (for login testing)
- **ID Number**: `2021-00001`
- **Password**: `student123`
- **User Type**: `student`

---

## 🧪 Postman Tests (In Order!)

### ✅ Test 1: Health Check
**GET** `http://localhost/LaunchPad/launchpad-api/public/health`

**Expected Response:**
```json
{
  "success": true,
  "data": {
    "status": "healthy",
    "version": "v1",
    "timestamp": "2025-10-11T..."
  },
  "message": "LaunchPad API is running! 🚀"
}
```

---

### ✅ Test 2: Student Registration
**POST** `http://localhost/LaunchPad/launchpad-api/public/students/register`

**Body Type:** `form-data`

**Fields:**
| Key | Value | Type |
|-----|-------|------|
| email | `newstudent@test.com` | Text |
| id_number | `2021-12345` | Text |
| first_name | `Maria` | Text |
| last_name | `Santos` | Text |
| course | `IT` | Text |
| contact_num | `09171234567` | Text |
| password | `password123` | Text |
| company_name | `ABC Company` | Text |
| id_photo | (select an image file) | File |

**Expected Response:**
```json
{
  "success": true,
  "data": {
    "student_id": 1,
    "status": "pending",
    "message": "Registration submitted! Waiting for admin approval."
  },
  "message": "Registration successful",
  "timestamp": "..."
}
```

---

### ✅ Test 3: CDC Admin Login
**POST** `http://localhost/LaunchPad/launchpad-api/public/auth/login`

**Headers:**
```
Content-Type: application/json
```

**Body (raw JSON):**
```json
{
  "username": "cdc_admin",
  "password": "admin123",
  "userType": "cdc"
}
```

**Expected Response:**
```json
{
  "success": true,
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "user": {
      "id": 1,
      "username": "cdc_admin",
      "email": "cdc@launchpad.com",
      ...
    },
    "expiresIn": 86400
  },
  "message": "Login successful"
}
```

**💾 SAVE THE TOKEN!** Copy it for next tests.

---

### ✅ Test 4: View Unverified Students (CDC Only)
**GET** `http://localhost/LaunchPad/launchpad-api/public/admin/unverified/students`

**Headers:**
```
Authorization: Bearer YOUR_CDC_TOKEN_HERE
```

**Expected Response:**
```json
{
  "success": true,
  "data": [
    {
      "student_id": 1,
      "id_num": "2021-12345",
      "first_name": "Maria",
      "last_name": "Santos",
      "email": "newstudent@test.com",
      "course": "IT",
      "id_photo": "id_2021-12345_1234567890.jpg",
      "created_at": "..."
    }
  ]
}
```

---

### ✅ Test 5: Verify Student (CDC Only)
**POST** `http://localhost/LaunchPad/launchpad-api/public/admin/verify/students/1`

Replace `1` with the student_id from Test 4.

**Headers:**
```
Authorization: Bearer YOUR_CDC_TOKEN_HERE
```

**Expected Response:**
```json
{
  "success": true,
  "data": {
    "student_id": 1
  },
  "message": "Student verified successfully"
}
```

---

### ✅ Test 6: Student Login (After Verification)
**POST** `http://localhost/LaunchPad/launchpad-api/public/auth/login`

**Body (raw JSON):**
```json
{
  "username": "2021-12345",
  "password": "password123",
  "userType": "student"
}
```

**Expected Response:**
```json
{
  "success": true,
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "user": {
      "student_id": 1,
      "id_num": "2021-12345",
      ...
    }
  },
  "message": "Login successful"
}
```

---

### ✅ Test 7: Get Student Profile
**GET** `http://localhost/LaunchPad/launchpad-api/public/students/1`

**Headers:**
```
Authorization: Bearer YOUR_STUDENT_TOKEN_HERE
```

**Expected Response:**
```json
{
  "success": true,
  "data": {
    "student_id": 1,
    "id_num": "2021-12345",
    "first_name": "Maria",
    "last_name": "Santos",
    "email": "newstudent@test.com",
    "course": "IT",
    ...
  }
}
```

---

## 🏢 Phase 2: Company Registration & Verification

### ✅ Test 8: Company Registration
**POST** `http://localhost/LaunchPad/launchpad-api/public/companies/register`

**Body Type:** `form-data`

**Fields:**
| Key | Value | Type |
|-----|-------|------|
| company_name | `TechStart Inc` | Text |
| username | `techstart` | Text |
| email | `contact@techstart.com` | Text |
| contact_num | `09171234567` | Text |
| address | `456 Tech Ave, Cebu City` | Text |
| website | `https://techstart.com` | Text |
| password | `password123` | Text |
| company_logo | (optional image file) | File |
| moa_document | (optional PDF/Word) | File |

**Expected Response:**
```json
{
  "success": true,
  "data": {
    "company_id": 1,
    "status": "pending",
    "message": "Company registration submitted! Waiting for CDC approval."
  },
  "message": "Registration successful",
  "timestamp": "..."
}
```

---

### ✅ Test 9: View Unverified Companies (CDC Only)
**GET** `http://localhost/LaunchPad/launchpad-api/public/admin/unverified/companies`

**Headers:**
```
Authorization: Bearer YOUR_CDC_TOKEN_HERE
```

**Expected Response:**
```json
{
  "success": true,
  "data": [
    {
      "company_id": 1,
      "company_name": "TechStart Inc",
      "username": "techstart",
      "email": "contact@techstart.com",
      "address": "456 Tech Ave, Cebu City",
      "company_logo": "logo_techstart_1234567890.png",
      "moa_document": "moa_techstart_1234567890.pdf",
      "created_at": "..."
    }
  ]
}
```

---

### ✅ Test 10: Verify Company (CDC Only)
**POST** `http://localhost/LaunchPad/launchpad-api/public/admin/verify/companies/1`

Replace `1` with the company_id from Test 9.

**Headers:**
```
Authorization: Bearer YOUR_CDC_TOKEN_HERE
```

**Expected Response:**
```json
{
  "success": true,
  "data": {
    "company_id": 1
  },
  "message": "Company verified successfully"
}
```

---

### ✅ Test 11: Company Login (After Verification)
**POST** `http://localhost/LaunchPad/launchpad-api/public/auth/login`

**Body (raw JSON):**
```json
{
  "username": "techstart",
  "password": "password123",
  "userType": "company"
}
```

**Expected Response:**
```json
{
  "success": true,
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "user": {
      "company_id": 1,
      "company_name": "TechStart Inc",
      "username": "techstart",
      "email": "contact@techstart.com",
      ...
    },
    "expiresIn": 86400
  },
  "message": "Login successful"
}
```

---

### ✅ Test 12: Get Company Profile
**GET** `http://localhost/LaunchPad/launchpad-api/public/companies/1`

No auth required (public endpoint).

**Expected Response:**
```json
{
  "success": true,
  "data": {
    "company_id": 1,
    "company_name": "TechStart Inc",
    "email": "contact@techstart.com",
    "contact_num": "09171234567",
    "address": "456 Tech Ave, Cebu City",
    "website": "https://techstart.com",
    "company_logo": "logo_techstart_1234567890.png",
    "verified_at": "..."
  }
}
```

---

### ✅ Test 13: List All Companies
**GET** `http://localhost/LaunchPad/launchpad-api/public/companies`

No auth required (public endpoint).

**Optional Query Params:**
- `page=1`
- `pageSize=20`

**Expected Response:**
```json
{
  "success": true,
  "data": [
    {
      "company_id": 1,
      "company_name": "TechStart Inc",
      "email": "contact@techstart.com",
      ...
    },
    {
      "company_id": 2,
      "company_name": "Acme Corp",
      ...
    }
  ],
  "pagination": {
    "page": 1,
    "pageSize": 20,
    "total": 2,
    "totalPages": 1
  }
}
```

---

### ✅ Test 14: Test Pre-loaded Company Account
**POST** `http://localhost/LaunchPad/launchpad-api/public/auth/login`

**Body (raw JSON):**
```json
{
  "username": "acme_corp",
  "password": "company123",
  "userType": "company"
}
```

This account is already in the database for quick testing!

---

## ✅ Testing Checklist

### Phase 1: Students ✅
- [ ] Health check returns 200 - ALL GOODS
- [ ] Student can register with ID photo
- [ ] Duplicate email shows error
- [ ] Duplicate ID number shows error
- [ ] Invalid course shows error
- [ ] CDC can login
- [ ] CDC can view unverified students
- [ ] CDC can verify student
- [ ] Verified student can login
- [ ] Student can view own profile

### Phase 2: Companies ✅
- [x] Company can register with logo & MOA
- [x] Duplicate email shows error
- [x] Duplicate username shows error
- [x] CDC can view unverified companies
- [x] CDC can verify company
- [x] CDC can reject company
- [x] Verified company can login
- [x] Can view company profile
- [x] Can list all companies

### Phase 3: OJT Hours Tracking (Report-Based) 🚧
- [ ] Student submits daily report with file
- [ ] Student can view report history
- [ ] Cannot submit future dates
- [ ] Cannot submit duplicate dates
- [ ] CDC views pending reports
- [ ] CDC can approve reports (adds hours)
- [ ] CDC can reject reports (with reason)
- [ ] Student can view OJT progress
- [ ] CDC can view all students' progress
- [ ] CDC can view dashboard stats

---

## 📊 Phase 3: OJT Hours Tracking (Report-Based Approval)

### ✅ Test 15: View Student OJT Progress
**GET** `http://localhost/LaunchPad/launchpad-api/public/students/1/ojt`

**Headers:**
```
Authorization: Bearer YOUR_STUDENT_TOKEN
```

**Expected Response:**
```json
{
  "success": true,
  "data": {
    "progress": {
      "progress_id": 1,
      "student_id": 1,
      "required_hours": 500,
      "completed_hours": 120,
      "status": "in_progress",
      "start_date": "2025-01-15",
      "end_date": null,
      "completion_percentage": 24,
      "remaining_hours": 380
    },
    "hours_log": [
      {
  
    ],
    "total_logs": 0
  }
}
```

---

### ✅ Test 16: Submit Daily Report
**POST** `http://localhost/LaunchPad/launchpad-api/public/students/1/reports/daily`

**Headers:**
```
Authorization: Bearer YOUR_STUDENT_TOKEN
```

**Body Type:** `form-data`

**Fields:**
| Key | Value | Type |
|-----|-------|------|
| report_date | `2025-10-11` | Text |
| hours_requested | `8` | Text |
| description | `Backend API development` | Text |
| activity_type | `Development` | Text |
| report_file | (PDF/Image file) | File |

**Expected Response:**
```json
{
  "success": true,
  "data": {
    "report_id": 4,
    "status": "pending",
    "message": "Report submitted! Waiting for CDC approval."
  },
  "message": "Report submitted successfully"
}
```

**Validation Tests:**
- Try > 24 hours → should error
- Try 0 hours → should error
- Try future date → should error
- Try same date twice → should error
- Missing file → should error

---

### ✅ Test 17: View Student Daily Reports
**GET** `http://localhost/LaunchPad/launchpad-api/public/students/1/reports/daily`

**Headers:**
```
Authorization: Bearer YOUR_STUDENT_TOKEN
```

**Optional Query Params:**
- `status=pending` (all, pending, approved, rejected)

**Expected Response:**
```json
{
  "success": true,
  "data": {
    "reports": [
      {
        "report_id": 4,
        "student_id": 1,
        "report_date": "2025-10-11",
        "hours_requested": 8,
        "description": "Backend API development",
        "activity_type": "Development",
        "report_file": "daily_report_1_2025-10-11_1234567890.pdf",
        "status": "pending",
        "submitted_at": "..."
      },
      {
        "report_id": 3,
        "student_id": 1,
        "report_date": "2025-01-17",
        "hours_requested": 7.5,
        "description": "Backend API development",
        "status": "pending",
        ...
      }
    ],
    "summary": {
      "total": 3,
      "pending": 2,
      "approved": 2,
      "rejected": 0
    }
  }
}
```

---

---

### ✅ Test 18: CDC Views Pending Reports
**GET** `http://localhost/LaunchPad/launchpad-api/public/admin/reports/pending`

**Headers:**
```
Authorization: Bearer YOUR_CDC_TOKEN
```

**Optional Query Params:**
- `page=1`
- `pageSize=20`

**Expected Response:**
```json
{
  "success": true,
  "data": [
    {
      "report_id": 4,
      "student_id": 1,
      "report_date": "2025-10-11",
      "hours_requested": 8,
      "description": "Backend API development",
      "activity_type": "Development",
      "report_file": "daily_report_1_2025-10-11_1234567890.pdf",
      "status": "pending",
      "id_num": "2021-00001",
      "first_name": "Juan",
      "last_name": "Dela Cruz",
      "email": "juan@student.com",
      "course": "IT",
      "company_name": "Ingent",
      "submitted_at": "..."
    }
  ],
  "pagination": {
    "page": 1,
    "pageSize": 20,
    "total": 1,
    "totalPages": 1
  }
}
```

---

### ✅ Test 19: CDC Approves Report (Hours Added!)
**POST** `http://localhost/LaunchPad/launchpad-api/public/admin/reports/4/review`

Replace `4` with the report_id.

**Headers:**
```
Authorization: Bearer YOUR_CDC_TOKEN
Content-Type: application/json
```

**Body (raw JSON):**
```json
{
  "action": "approve"
}
```

**Expected Response:**
```json
{
  "success": true,
  "data": {
    "report_id": 4,
    "action": "approved",
    "hours_added": 8,
    "new_total_hours": 24,
    "completion_percentage": 4.8,
    "status": "in_progress"
  },
  "message": "Report approved and hours added to student progress"
}
```

---

### ✅ Test 20: CDC Rejects Report
**POST** `http://localhost/LaunchPad/launchpad-api/public/admin/reports/4/review`

**Headers:**
```
Authorization: Bearer YOUR_CDC_TOKEN
Content-Type: application/json
```

**Body (raw JSON):**
```json
{
  "action": "reject",
  "rejection_reason": "Insufficient details about tasks performed"
}
```

**Expected Response:**
```json
{
  "success": true,
  "data": {
    "report_id": 4,
    "action": "rejected",
    "reason": "Insufficient details about tasks performed"
  },
  "message": "Report rejected"
}
```

---

### ✅ Test 21: CDC Views All OJT Progress
**GET** `http://localhost/LaunchPad/launchpad-api/public/admin/ojt/progress`

**Headers:**
```
Authorization: Bearer YOUR_CDC_TOKEN
```

**Optional Query Params:**
- `status=in_progress` (filter: not_started, in_progress, completed, all)
- `page=1`
- `pageSize=20`

**Expected Response:**
```json
{
  "success": true,
  "data": [
    {
      "progress_id": 1,
      "student_id": 1,
      "required_hours": 500,
      "completed_hours": 128,
      "status": "in_progress",
      "start_date": "2025-01-15",
      "id_num": "2021-00001",
      "first_name": "Juan",
      "last_name": "Dela Cruz",
      "email": "juan@student.com",
      "course": "IT",
      "company_name": "Ingent",
      "completion_percentage": 25.6,
      "remaining_hours": 372
    }
  ],
  "pagination": {
    "page": 1,
    "pageSize": 20,
    "total": 1,
    "totalPages": 1
  }
}
```

---

### ✅ Test 22: CDC Dashboard Stats
**GET** `http://localhost/LaunchPad/launchpad-api/public/admin/ojt/stats`

**Headers:**
```
Authorization: Bearer YOUR_CDC_TOKEN
```

**Expected Response:**
```json
{
  "success": true,
  "data": {
    "total_students": 5,
    "students_with_progress": 3,
    "total_hours_logged": 856,
    "average_completion": 42.5,
    "status_breakdown": {
      "not_started": 2,
      "in_progress": 2,
      "completed": 1
    },
    "recent_logs": 15,
    "top_performers": [
      {
        "id_num": "2021-00001",
        "first_name": "Juan",
        "last_name": "Dela Cruz",
        "completed_hours": 500,
        "status": "completed"
      }
    ]
  }
}
```

---

## 🐛 Common Errors

### "Database connection failed"
✅ Start MySQL in XAMPP
✅ Import database.sql

### "Endpoint not found"
✅ Check URL is correct
✅ Make sure Apache is running

### "Failed to upload file"
✅ Check `uploads/` folder exists
✅ Set folder permissions: `chmod -R 777 uploads/`

### "Unauthorized"
✅ Copy full token from login response
✅ Use format: `Bearer <token>` with space

