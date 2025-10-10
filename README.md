# 🚀 LaunchPad OJT Tracker

A complete system for managing On-the-Job Training (OJT) for schools, students, and partner companies.

## 📖 What is this?

LaunchPad is a tracking and management system that helps:
- **Schools(CDC)** - Monitor student OJT progress and approve companies
- **Students** - Track their training hours, submit reports, and find job opportunities
- **Companies** - Post job openings, evaluate student performance, and manage OJT trainees

## 🏗️ Project Structure

```
LaunchPad/
├── Main/           # Landing page and home screens
├── CDC/            # Career Development Center portal
├── PC/             # Program Coordinator portal
├── LaunchPad.sql   # Database setup file
├── launchpad-app/     # Flutter mobile app (coming soon!)
└── Index.php       # Entry point
```

## 🛠️ Tech Stack

**Web App (Current)**
- PHP for backend logic
- MySQL database
- XAMPP (Apache + MySQL + PHP)
- phpMyAdmin for database management

**Mobile App (Upcoming)**
- Flutter for cross-platform mobile development
- Will connect to the same XAMPP backend using local API URLs

## 🎯 Main Features

### For Students
- ✅ Register and wait for school approval
- ✅ Track OJT hours (done vs required)
- ✅ Submit progress reports
- ✅ Browse job opportunities from partner companies
- ✅ Receive notifications and deadlines
- ✅ View performance evaluations

### For Companies
- ✅ Register and get verified by the school
- ✅ Post job opportunities for students
- ✅ Evaluate student performance
- ✅ Manage company profile and information

### For School Admins
- ✅ Approve or reject company registrations
- ✅ Approve or reject student registrations
- ✅ Send notifications to students
- ✅ Monitor overall OJT progress

## 🚀 Getting Started

### Setting Up the Web App

1. **Install XAMPP**
   - Download and install XAMPP for your operating system
   - Start Apache and MySQL services

2. **Set Up the Database**
   - Open phpMyAdmin (usually at `http://localhost/phpmyadmin`)
   - Create a new database called `launchpad`
   - Import the `LaunchPad.sql` file

3. **Configure the Project**
   - Copy this project folder to your XAMPP `htdocs` directory
   - Update database connection settings in the Config files
   - Make sure file upload folders have write permissions

4. **Access the App**
   - Open your browser and go to `http://localhost/LaunchPad`
   - You should see the landing page!

### Setting Up the Mobile App (Coming Soon!)

1. **Install Flutter**
   - Download Flutter SDK
   - Set up your IDE (VS Code or Android Studio)

2. **Configure API Connection**
   - Update API base URL to point to your local XAMPP server
   - For Android emulator: usually `http://10.0.2.2/LaunchPad`
   - For iOS simulator: usually `http://localhost/LaunchPad`
   - For physical device: use your computer's local IP address

3. **Run the App**
   - Navigate to the `mobile-app` folder
   - Run `flutter pub get` to install dependencies
   - Run `flutter run` to launch the app

## 📱 Monorepo Setup

This project uses a monorepo structure, meaning both the web app and mobile app live in the same repository. This makes it easier to:
- Share API endpoints and documentation
- Track changes across platforms
- Keep everything in sync
- Collaborate with your team

## 🤝 Contributing

This is a school project, so feel free to:
- Report bugs or issues
- Suggest new features
- Improve documentation
- Add test cases

## 📝 Notes

- Make sure XAMPP is running whenever you want to use the web app or test the mobile app
- The database file (`LaunchPad.sql`) contains the complete schema for all features
- File uploads (like student COR, company MOA, profile pics) are stored locally
- The mobile app will use the same backend as the web app - no separate API needed!

---
