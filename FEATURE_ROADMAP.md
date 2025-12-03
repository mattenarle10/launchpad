# 🚀 LaunchPad Feature Roadmap

---

## 🧪 TESTING GUIDE - What We Built So Far

### Run Migrations First!
```sql
-- Run in phpMyAdmin (launchpad_db):

-- 1. Add semester/academic year to students
ALTER TABLE `verified_students` 
ADD COLUMN `semester` ENUM('1st', '2nd', 'summer') DEFAULT '1st',
ADD COLUMN `academic_year` VARCHAR(20) DEFAULT '2024-2025';

ALTER TABLE `unverified_students`
ADD COLUMN `semester` ENUM('1st', '2nd', 'summer') DEFAULT '1st',
ADD COLUMN `academic_year` VARCHAR(20) DEFAULT '2024-2025';

-- 2. Add company validation fields for DTR
ALTER TABLE `daily_reports`
ADD COLUMN `hours_approved` DECIMAL(5,2) NULL COMMENT 'Hours approved by company',
ADD COLUMN `company_validated` TINYINT(1) DEFAULT 0,
ADD COLUMN `company_validated_at` TIMESTAMP NULL,
ADD COLUMN `company_validated_by` INT NULL,
ADD COLUMN `company_remarks` TEXT NULL;
```

---

### ✅ Phase 1: Password Features - HOW TO TEST

#### 1.1 Password Complexity (Registration)
**Mobile App:**
1. Open app → Register
2. Try weak password like `12345678` → Should FAIL
3. Try strong password like `Test@123` → Should PASS
4. Requirements: 8+ chars, uppercase, lowercase, number, special char

**Web (Company Registration):**
1. Go to register page
2. Same password rules apply

#### 1.2 Change Password
**Mobile App:**
1. Login as student
2. Menu → Change Password
3. Enter current password
4. Enter new password (must meet complexity)
5. Confirm → Should update

**Web Dashboard (CDC/PC):**
1. Login as CDC or Company
2. Go to Profile page
3. Click "Change Password" button
4. Fill modal form → Submit

---

### ✅ Phase 2: Student Filtering - HOW TO TEST

#### 2.1 Semester/Year Filters
**CDC Dashboard:**
1. Login as CDC → Students page
2. See new dropdowns: "All Semesters", "All Academic Years"
3. Filter by 1st/2nd/Summer semester
4. Filter by academic year (e.g., 2024-2025)

**PC Dashboard:**
1. Login as Company → Students page
2. Same filters available

#### 2.2 API Filters
```
GET /students?semester=1st&academic_year=2024-2025&course=IT&status=in_progress
```

---

### ✅ Phase 3: Flexible DTR Hours - HOW TO TEST

#### 3.1 Student Submits Flexible Hours (Mobile App)
**Before:** Hours was fixed at 8
**Now:** Student can enter any hours (0.1 - 24)

1. Login as student in app
2. Go to Report → Submit Daily Report
3. See new "Hours Worked" input field (default: 8)
4. Change to any value (e.g., 10, 4.5, 12)
5. Upload file → Submit
6. Message: "Waiting for company approval"

#### 3.2 DTR Flow (UPDATED!)
```
OLD FLOW:
Student submits (8 hrs fixed) → CDC approves → Hours added

NEW FLOW:
Student submits (flexible hrs) → PC approves/rejects (+/- hours) → Final hours added
                               ↳ CDC can still approve/reject (for monitoring)
```

**Key Change:** PC (Partner Company) is now the PRIMARY approver for DTR.
- PC sees pending DTR, approves/rejects, and sets final hours
- CDC can still approve/reject but mainly monitors
- Hours are added to OJT progress upon PC approval

#### 3.3 PC DTR Management (API Ready ✅)
**API Endpoints:**
```
GET /companies/dtr/pending
- Get all pending DTR reports for company's students
- Shows student name, hours requested, date

GET /companies/students/:id/dtr?month=12&year=2025
- View specific student's DTR for the month
- Shows hours_requested vs hours_approved

POST /companies/dtr/:report_id/validate
Body (Approve): { "action": "approve", "hours_approved": 7.5, "remarks": "Left early" }
Body (Reject):  { "action": "reject", "rejection_reason": "Incomplete report" }
- PC approves/rejects DTR directly
- Can adjust hours (+/-) on approval
- Updates OJT progress automatically
```

---

## 📱 Expected Behavior Summary

| Feature | Who | What Happens |
|---------|-----|--------------|
| Register | Student/Company | Password must be complex (8+ chars, upper, lower, number, special) |
| Change Password | All users | Can change via app menu or web profile |
| Submit DTR | Student (App) | Can request 0.1-24 hours (not fixed 8) |
| **Approve/Reject DTR** | **PC (Primary)** | **Approves/rejects, sets final hours (+/-)** |
| Approve DTR | CDC (Monitor) | Can still approve/reject, but PC is primary |
| Filter Students | CDC/PC (Web) | Filter by semester, academic year, course, status |

---

## Current Database Schema Summary
| Table | Purpose |
|-------|---------|
| `cdc_users` | CDC admin accounts |
| `verified_students` | Approved students with OJT details |
| `unverified_students` | Pending student registrations |
| `verified_companies` | Approved partner companies (PC) |
| `unverified_companies` | Pending company registrations |
| `daily_reports` | Student DTR submissions (pending/approved/rejected) |
| `ojt_progress` | Student OJT hours tracking |
| `student_evaluations` | Company evaluations (first_half/second_half per month) |
| `evaluation_history` | Historical evaluation records |
| `student_requirements` | Pre-deployment/deployment/final requirements |
| `job_opportunities` | Job postings from companies |
| `notifications` | System notifications |
| `notification_recipients` | Notification delivery tracking |

---

## Phase 1: Password & Authentication ✅
- [x] **1.1** Add password complexity validation helper in API
- [x] **1.2** Enforce password complexity on student registration
- [x] **1.3** Enforce password complexity on company registration  
- [x] **1.4** Create change password API endpoint
- [x] **1.5** Add change password in Flutter app (student)
- [x] **1.6** Add change password in web dashboard (CDC/PC)

---

## Phase 2: Student Filtering & Completed History
- [x] **2.1** Add semester/academic year columns to verified_students (migration created)
- [x] **2.2** Update student registration to capture semester/year
- [x] **2.3** Add semester/year filter to get-all students API
- [x] **2.4** Update CDC students page with filter dropdowns
- [x] **2.5** Update PC students page with filter dropdowns
- [x] **2.6** Create get-completed-students API endpoint
- [ ] **2.7** Add completed students history page in CDC dashboard

---

## Phase 3: Flexible DTR Hours (Student App) ✅
- [x] **3.1** Add company validation fields to daily_reports (migration)
- [x] **3.2** Update submit-daily-report API for flexible hours (not fixed 8)
- [x] **3.3** Update Flutter report screen with hours input field
- [x] **3.4** Create PC endpoint to validate/approve DTR hours
- [ ] **3.5** Add PC DTR validation page with +/- hours adjustment (web UI)

---

## Phase 4: PC (Partner Company) Features
- [x] **4.1** Add company_validated fields to daily_reports (done in 3.1)
- [x] **4.2** Create get-student-dtr API for company view
- [x] **4.3** Create validate-dtr API for company approval
- [ ] **4.4** Add DTR view page in PC dashboard with +/- hours (web UI)
- [ ] **4.5** Update evaluation schema for NAO format criteria (later)
- [ ] **4.6** Create NAO-format evaluation submission API (later)
- [ ] **4.7** Update PC evaluation form with NAO format
- [ ] **4.8** Create get-completed-students API for companies
- [ ] **4.9** Add completed students tab in PC dashboard

---

## Phase 5: Better Reports
- [ ] **5.1** Create PDF report generation endpoint
- [ ] **5.2** Add DTR summary report template
- [ ] **5.3** Add OJT completion certificate template
- [ ] **5.4** Add evaluation summary report template
- [ ] **5.5** Update CDC dashboard with report generation UI
- [ ] **5.6** Add export to Excel functionality

---

## Migration Files Location
- `launchpad-api/migrations/add_semester_academic_year.sql`
- `launchpad-api/migrations/add_flexible_dtr_fields.sql`

See **Testing Guide** at top for the SQL to run.

---

## Commit Convention
Format: `type(scope): message`
- `feat(api)` - API changes
- `feat(app)` - Flutter app changes
- `feat(web)` - Web dashboard changes
- `feat(db)` - Database migrations
- `fix(scope)` - Bug fixes

---

## 📝 Commits Made (Dec 3, 2025)

### Phase 1: Password & Auth
1. `feat(api): add password complexity validation with regex rules`
2. `feat(api): enforce password complexity on student registration`
3. `feat(api): enforce password complexity on company registration`
4. `feat(api): add change password endpoint with old password verification`
5. `feat(app): add change password screen for students`
6. `feat(web): add change password modal for CDC/PC users`

### Phase 2: Student Filtering
7. `feat(db): add semester and academic year columns migration`
8. `feat(api): capture semester and academic year on student registration`
9. `feat(api): add semester and academic year filters to students list`
10. `feat(web): add semester/year filter dropdowns to CDC students page`
11. `feat(web): add semester/year filter dropdowns to PC students page`
12. `feat(api): add endpoint to get OJT completed students history`

### Phase 3: Flexible DTR Hours
13. `feat(db): add company validation fields for DTR approval`
14. `feat(api): allow flexible hours in daily report submission`
15. `feat(app): add flexible hours input for DTR submission`
16. `feat(api): add DTR endpoint for company to view student hours`
17. `feat(api): add DTR validation endpoint for PC approval with +/- hours`
