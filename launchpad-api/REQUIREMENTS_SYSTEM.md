# Student Requirements System

## Overview
The Requirements System allows students to submit three types of OJT requirement documents:
1. **Pre-Deployment** - Documents required before starting OJT
2. **Deployment** - Documents required during OJT deployment
3. **Final Requirements** - Documents required at the end of OJT

## Database Schema

### Table: `student_requirements`
```sql
CREATE TABLE student_requirements (
    requirement_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    requirement_type ENUM('pre_deployment', 'deployment', 'final_requirements') NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_size INT NOT NULL,
    description TEXT DEFAULT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES verified_students(student_id) ON DELETE CASCADE,
    INDEX idx_student_type (student_id, requirement_type),
    INDEX idx_type (requirement_type),
    INDEX idx_submitted (submitted_at)
);
```

## File Storage Structure
```
uploads/
└── requirements/
    ├── pre_deployment/
    │   └── requirement_1_pre_deployment_1234567890.pdf
    ├── deployment/
    │   └── requirement_2_deployment_1234567891.pdf
    └── final_requirements/
        └── requirement_3_final_requirements_1234567892.pdf
```

## API Endpoints

### Student Endpoints

#### 1. Submit Requirement
**Endpoint:** `POST /students/:id/requirements/submit`  
**Auth:** Student (must be own ID)  
**Content-Type:** `multipart/form-data`

**Request Body:**
```
requirement_type: "pre_deployment" | "deployment" | "final_requirements"
requirement_file: File (PDF, Word, JPEG, PNG, WebP - max 10MB)
description: "Optional description" (optional)
```

**Response:**
```json
{
  "success": true,
  "message": "Requirement uploaded successfully",
  "data": {
    "requirement_id": 1,
    "requirement_type": "pre_deployment",
    "file_name": "my_document.pdf",
    "message": "Requirement submitted successfully!"
  }
}
```

#### 2. Get Student's Requirements
**Endpoint:** `GET /students/:id/requirements`  
**Auth:** Student (must be own ID)  
**Query Params:** `type` (optional) - Filter by requirement type

**Response:**
```json
{
  "success": true,
  "message": "Requirements retrieved successfully",
  "data": {
    "all_requirements": [
      {
        "requirement_id": 1,
        "requirement_type": "pre_deployment",
        "file_name": "my_document.pdf",
        "file_path": "requirement_1_pre_deployment_1234567890.pdf",
        "file_size": 1048576,
        "file_size_mb": 1.0,
        "description": "My pre-deployment documents",
        "submitted_at": "2025-10-26 12:00:00"
      }
    ],
    "grouped_by_type": {
      "pre_deployment": [...],
      "deployment": [...],
      "final_requirements": [...]
    },
    "total_count": 3
  }
}
```

#### 3. Delete Requirement
**Endpoint:** `DELETE /students/:id/requirements/:requirement_id`  
**Auth:** Student (must be own ID)

**Response:**
```json
{
  "success": true,
  "message": "Requirement deleted successfully",
  "data": {
    "requirement_id": 1,
    "message": "Requirement deleted successfully"
  }
}
```

### CDC/Admin Endpoints

#### 4. Get Student's Requirements (CDC View)
**Endpoint:** `GET /admin/students/:student_id/requirements`  
**Auth:** CDC  
**Query Params:** `type` (optional) - Filter by requirement type

**Response:**
```json
{
  "success": true,
  "message": "Requirements retrieved successfully",
  "data": {
    "student_info": {
      "student_id": 1,
      "id_num": "0800999",
      "first_name": "Hehe",
      "last_name": "Cruz",
      "course": "IT",
      "company_name": "Ubiquity"
    },
    "all_requirements": [...],
    "grouped_by_type": {
      "pre_deployment": [...],
      "deployment": [...],
      "final_requirements": [...]
    },
    "counts_by_type": {
      "pre_deployment": 2,
      "deployment": 1,
      "final_requirements": 0
    },
    "total_count": 3
  }
}
```

#### 5. Get All Requirements Overview (CDC View)
**Endpoint:** `GET /admin/requirements`  
**Auth:** CDC  
**Query Params:** `type` (optional) - Filter by requirement type

**Response:**
```json
{
  "success": true,
  "message": "Requirements overview retrieved successfully",
  "data": {
    "students": [
      {
        "student_id": 1,
        "id_num": "0800999",
        "first_name": "Hehe",
        "last_name": "Cruz",
        "full_name": "Hehe Cruz",
        "course": "IT",
        "company_name": "Ubiquity",
        "requirements_count": {
          "pre_deployment": 2,
          "deployment": 1,
          "final_requirements": 0,
          "total": 3
        },
        "last_submission": "2025-10-26 12:00:00"
      }
    ],
    "total_students": 10,
    "statistics": {
      "students_with_requirements": 8,
      "total_files": 25,
      "by_type": {
        "pre_deployment": 10,
        "deployment": 8,
        "final_requirements": 7
      }
    },
    "filter_applied": null
  }
}
```

## Usage in Flutter App

### 1. Upload Requirement
```dart
Future<void> uploadRequirement({
  required int studentId,
  required String requirementType, // 'pre_deployment', 'deployment', 'final_requirements'
  required File file,
  String? description,
}) async {
  final request = http.MultipartRequest(
    'POST',
    Uri.parse('$baseUrl/students/$studentId/requirements/submit'),
  );
  
  request.headers['Authorization'] = 'Bearer $token';
  request.fields['requirement_type'] = requirementType;
  if (description != null) {
    request.fields['description'] = description;
  }
  
  request.files.add(await http.MultipartFile.fromPath(
    'requirement_file',
    file.path,
  ));
  
  final response = await request.send();
  final responseData = await response.stream.bytesToString();
  // Handle response
}
```

### 2. Get Requirements
```dart
Future<Map<String, dynamic>> getRequirements(int studentId, {String? type}) async {
  final uri = type != null 
    ? Uri.parse('$baseUrl/students/$studentId/requirements?type=$type')
    : Uri.parse('$baseUrl/students/$studentId/requirements');
    
  final response = await http.get(
    uri,
    headers: {'Authorization': 'Bearer $token'},
  );
  
  return json.decode(response.body);
}
```

### 3. Delete Requirement
```dart
Future<void> deleteRequirement(int studentId, int requirementId) async {
  final response = await http.delete(
    Uri.parse('$baseUrl/students/$studentId/requirements/$requirementId'),
    headers: {'Authorization': 'Bearer $token'},
  );
  // Handle response
}
```

## UI/UX Recommendations for Flutter App

### Requirements Page Structure
```
📱 Requirements Screen
├── Pre-Deployment Tab
│   ├── Upload Button
│   └── List of uploaded files
│       ├── File 1 (with delete option)
│       └── File 2 (with delete option)
├── Deployment Tab
│   ├── Upload Button
│   └── List of uploaded files
└── Final Requirements Tab
    ├── Upload Button
    └── List of uploaded files
```

### Key Features to Implement:
1. **Tabbed Interface** - Three tabs for each requirement type
2. **File Upload** - With file picker (PDF, Images, Word docs)
3. **File Preview** - Show thumbnail/icon, filename, size, upload date
4. **Delete Option** - Allow students to remove wrong uploads
5. **Description Field** - Optional text description for each upload
6. **Progress Indicator** - Show upload progress
7. **File Type Icons** - Different icons for PDF, images, etc.
8. **Empty State** - Clear message when no files uploaded

### CDC Dashboard Features:
1. **Student List** - Show all students with requirement counts
2. **Filter by Type** - View only students who submitted specific types
3. **File Download** - CDC can download/view submitted files
4. **Statistics Dashboard** - Overall submission statistics

## Migration Instructions

### Step 1: Run Database Migration
```bash
mysql -u your_user -p launchpad_db < migrations/add_student_requirements_table.sql
```

### Step 2: Verify Upload Folders
The folders will be created automatically on first upload, but you can create them manually:
```bash
mkdir -p uploads/requirements/pre_deployment
mkdir -p uploads/requirements/deployment
mkdir -p uploads/requirements/final_requirements
chmod 777 uploads/requirements/ -R
```

### Step 3: Test Endpoints
Use Postman or similar tool to test the endpoints with sample data.

## Security Considerations

1. **File Type Validation** - Only allow PDF, Word, and image files
2. **File Size Limit** - Maximum 10MB per file
3. **Authentication** - Students can only access their own requirements
4. **Path Traversal Protection** - File paths are sanitized
5. **Database Constraints** - Foreign key ensures data integrity

## Future Enhancements

1. **File Versioning** - Keep history of replaced files
2. **Status Tracking** - Add approval/rejection workflow
3. **Notifications** - Alert students about missing requirements
4. **Deadlines** - Set submission deadlines for each type
5. **Templates** - Provide downloadable requirement templates
6. **Bulk Download** - CDC can download all files as ZIP
7. **File Preview** - In-app document viewer

## Support

For issues or questions, contact the development team or check the main API documentation.
