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
- [ ] Health check returns 200
- [ ] Student can register with ID photo
- [ ] Duplicate email shows error
- [ ] Duplicate ID number shows error
- [ ] Invalid course shows error
- [ ] CDC can login
- [ ] CDC can view unverified students
- [ ] CDC can verify student
- [ ] Verified student can login
- [ ] Student can view own profile

### Phase 2: Companies 🚧
- [ ] Company can register with logo & MOA
- [ ] Duplicate email shows error
- [ ] Duplicate username shows error
- [ ] CDC can view unverified companies
- [ ] CDC can verify company
- [ ] CDC can reject company
- [ ] Verified company can login
- [ ] Can view company profile
- [ ] Can list all companies
- [ ] Pre-loaded account works (acme_corp)

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

---

**Ready to test! Start with Test 1! 🚀**

