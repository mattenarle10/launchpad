# 🚀 LaunchPad

**OJT Management System** — Track training hours, submit reports, and connect students with companies.

---

## 📦 What's Inside

```
LaunchPad/
├── launchpad-api/     # PHP REST API backend
├── launchpad-web/     # Web dashboard (CDC portal)
└── launchpad-app/     # Flutter mobile app (Students & Companies)
```

---

## 🛠️ Tech Stack

| Layer | Technology |
|-------|-----------|
| **Backend** | PHP 7.4+, MySQL |
| **Web** | Vanilla JS, HTML/CSS |
| **Mobile** | Flutter (Dart) |
| **Server** | Apache (XAMPP/Hostinger) |

---

## ⚡ Quick Start

### 1. Database Setup
```bash
# Create database in phpMyAdmin
CREATE DATABASE launchpad;

# Import schema
mysql -u root launchpad < launchpad-api/migrations/schema.sql
```

### 2. Backend Configuration
```bash
# Edit launchpad-api/.env
DB_HOST=localhost
DB_NAME=launchpad
DB_USER=root
DB_PASSWORD=

JWT_SECRET=your_secret_key_here
```

### 3. Run Web App
```bash
# Place in XAMPP htdocs/
# Access at: http://localhost/LaunchPad/launchpad-web/pages/login.html
```

### 4. Run Mobile App
```bash
cd launchpad-app
flutter pub get
flutter run
```

---

## 👥 User Roles

| Role | Access |
|------|--------|
| **CDC** | Approve reports, manage users, send notifications |
| **Students** | Submit daily reports, track OJT hours, apply to jobs |
| **Companies** | Post jobs, evaluate students, manage trainees |

---

## 📱 Features

- ✅ Daily Time Record submission & approval
- ✅ Real-time OJT hours tracking
- ✅ Job posting & application system
- ✅ Push notifications (planned)
- ✅ File upload (reports, documents)
- ✅ Multi-platform (Web + Mobile)

---

## 🚀 Deployment

**Hostinger Setup:**
1. Upload `launchpad-api/` and `launchpad-web/` to `public_html/`
2. Create MySQL database via control panel
3. Import SQL schema
4. Update `.env` with production credentials
5. Enable SSL certificate

**Mobile App:**
1. Update API base URL in `launchpad-app/.env`
2. Build: `flutter build apk` (Android) or `flutter build ios` (iOS)
3. Distribute via Google Play / App Store
