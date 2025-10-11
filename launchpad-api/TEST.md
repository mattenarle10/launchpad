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

## ✅ Testing Checklist

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

