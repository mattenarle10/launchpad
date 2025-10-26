import 'package:flutter/material.dart';
import 'package:file_picker/file_picker.dart';
import 'package:dio/dio.dart';
import 'dart:io';
import '../../services/api/client.dart';
import '../../services/api/endpoints/student.dart';
import '../../models/requirement.dart';
import '../../components/requirement_card.dart';
import '../../components/empty_requirements.dart';

class RequirementsScreen extends StatefulWidget {
  const RequirementsScreen({super.key});

  @override
  State<RequirementsScreen> createState() => _RequirementsScreenState();
}

class _RequirementsScreenState extends State<RequirementsScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;
  final StudentApi _studentApi = StudentApi(ApiClient.I);
  
  Map<String, List<Requirement>> _requirements = {
    'pre_deployment': [],
    'deployment': [],
    'final_requirements': [],
  };
  
  bool _isLoading = true;
  int? _studentId;
  bool _isUploading = false;

  final List<Map<String, dynamic>> _tabs = [
    {
      'key': 'pre_deployment',
      'label': 'Pre-Deployment',
      'icon': Icons.assignment_outlined,
    },
    {
      'key': 'deployment',
      'label': 'Deployment',
      'icon': Icons.rocket_launch_outlined,
    },
    {
      'key': 'final_requirements',
      'label': 'Final',
      'icon': Icons.check_circle_outline,
    },
  ];

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 3, vsync: this);
    _loadStudentData();
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  Future<void> _loadStudentData() async {
    try {
      final user = await ApiClient.I.getCurrentUser();
      if (user != null && user['student_id'] != null) {
        setState(() {
          _studentId = user['student_id'];
        });
        await _loadRequirements();
      } else {
        setState(() {
          _isLoading = false;
        });
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('Failed to load student data')),
          );
        }
      }
    } catch (e) {
      setState(() {
        _isLoading = false;
      });
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error: $e')),
        );
      }
    }
  }

  Future<void> _loadRequirements() async {
    if (_studentId == null) return;

    setState(() {
      _isLoading = true;
    });

    try {
      final response = await _studentApi.getRequirements(_studentId!);
      final data = RequirementsData.fromJson(response['data']);

      if (mounted) {
        setState(() {
          _requirements = data.groupedByType;
          _isLoading = false;
        });
      }
    } catch (e) {
      print('Error loading requirements: $e');
      if (mounted) {
        setState(() {
          _isLoading = false;
        });
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Failed to load requirements: $e')),
        );
      }
    }
  }

  Future<void> _pickAndUploadFile(String requirementType) async {
    if (_studentId == null) return;

    try {
      final result = await FilePicker.platform.pickFiles(
        type: FileType.custom,
        allowedExtensions: ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'webp'],
      );

      if (result == null) return;

      final file = File(result.files.single.path!);
      
      // Show description dialog (optional - can be skipped)
      final description = await _showDescriptionDialog();
      
      // If user cancelled the dialog (tapped outside or back), stop
      if (description == null) {
        return;
      }

      // Show uploading indicator
      setState(() {
        _isUploading = true;
      });

      // Create FormData
      final formData = FormData.fromMap({
        'requirement_type': requirementType,
        'requirement_file': await MultipartFile.fromFile(
          file.path,
          filename: result.files.single.name,
        ),
        if (description != null && description.isNotEmpty)
          'description': description,
      });

      // Debug info
      print('=== UPLOAD ATTEMPT ===');
      print('Student ID: $_studentId');
      print('Requirement Type: $requirementType');
      print('File Name: ${result.files.single.name}');
      print('File Size: ${result.files.single.size} bytes');
      print('File Path: ${file.path}');
      print('Has Description: ${description?.isNotEmpty ?? false}');
      print('API Endpoint: /students/$_studentId/requirements/submit');
      print('=====================');

      // Upload
      final response = await _studentApi.submitRequirement(_studentId!, formData);

      setState(() {
        _isUploading = false;
      });

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(response['message'] ?? 'File uploaded successfully!'),
            backgroundColor: Colors.green,
          ),
        );
        
        // Reload requirements
        await _loadRequirements();
      }
    } on DioException catch (e) {
      setState(() {
        _isUploading = false;
      });
      
      print('=== DETAILED UPLOAD ERROR ===');
      print('Error Type: ${e.type}');
      print('Error Message: ${e.message}');
      print('Status Code: ${e.response?.statusCode}');
      print('Response Data: ${e.response?.data}');
      print('Request URL: ${e.requestOptions.uri}');
      print('Request Headers: ${e.requestOptions.headers}');
      print('============================');
      
      String errorMessage = 'Upload failed: ';
      
      switch (e.type) {
        case DioExceptionType.connectionTimeout:
          errorMessage += 'Connection timeout. Is XAMPP running?';
          break;
        case DioExceptionType.sendTimeout:
          errorMessage += 'Send timeout. File too large or slow connection';
          break;
        case DioExceptionType.receiveTimeout:
          errorMessage += 'Receive timeout. Server not responding';
          break;
        case DioExceptionType.badResponse:
          errorMessage += 'Server error: ${e.response?.statusCode} - ${e.response?.data}';
          break;
        case DioExceptionType.cancel:
          errorMessage += 'Request cancelled';
          break;
        case DioExceptionType.connectionError:
          errorMessage += 'Cannot connect to server. Check:\n1. XAMPP is running\n2. API URL: ${e.requestOptions.uri.host}:${e.requestOptions.uri.port}\n3. Using 10.0.2.2 for emulator';
          break;
        case DioExceptionType.unknown:
          errorMessage += 'Unknown error: ${e.message}';
          break;
        default:
          errorMessage += e.toString();
      }
      
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(errorMessage),
            backgroundColor: Colors.red,
            duration: const Duration(seconds: 5),
          ),
        );
      }
    } catch (e, stackTrace) {
      setState(() {
        _isUploading = false;
      });
      
      print('=== UNEXPECTED ERROR ===');
      print('Error: $e');
      print('Stack trace: $stackTrace');
      print('=======================');
      
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Unexpected error: $e'),
            backgroundColor: Colors.red,
            duration: const Duration(seconds: 5),
          ),
        );
      }
    }
  }

  Future<String?> _showDescriptionDialog() async {
    String description = '';
    return showDialog<String>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Add Description (Optional)'),
        content: TextField(
          onChanged: (value) => description = value,
          decoration: const InputDecoration(
            hintText: 'Enter description...',
            border: OutlineInputBorder(),
          ),
          maxLines: 3,
          maxLength: 200,
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, ''),
            style: TextButton.styleFrom(
              foregroundColor: const Color(0xFF6B7280),
            ),
            child: const Text('Skip'),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(context, description),
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFF4A6491),
              foregroundColor: Colors.white,
            ),
            child: const Text(
              'Continue',
              style: TextStyle(
                color: Colors.white,
                fontWeight: FontWeight.w600,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Future<void> _deleteRequirement(Requirement requirement) async {
    if (_studentId == null) return;

    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Delete File'),
        content: Text('Are you sure you want to delete "${requirement.fileName}"?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Cancel'),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(context, true),
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.red,
            ),
            child: const Text('Delete'),
          ),
        ],
      ),
    );

    if (confirmed != true) return;

    try {
      await _studentApi.deleteRequirement(_studentId!, requirement.requirementId);
      
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('File deleted successfully'),
            backgroundColor: Colors.green,
          ),
        );
        
        // Reload requirements
        await _loadRequirements();
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Delete failed: $e'),
            backgroundColor: Colors.red,
          ),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF8F9FB),
      appBar: AppBar(
        title: const Text(
          'Requirements',
          style: TextStyle(
            fontWeight: FontWeight.bold,
            color: Colors.white,
          ),
        ),
        backgroundColor: const Color(0xFF4A6491),
        elevation: 0,
        iconTheme: const IconThemeData(color: Colors.white),
        bottom: TabBar(
          controller: _tabController,
          indicatorColor: Colors.white,
          indicatorWeight: 3,
          labelColor: Colors.white,
          unselectedLabelColor: Colors.white70,
          tabs: _tabs.map((tab) {
            return Tab(
              icon: Icon(tab['icon'] as IconData, size: 20),
              text: tab['label'] as String,
            );
          }).toList(),
        ),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : Stack(
              children: [
                TabBarView(
                  controller: _tabController,
                  children: _tabs.map((tab) {
                    final requirementType = tab['key'] as String;
                    return _buildRequirementTab(requirementType);
                  }).toList(),
                ),
                if (_isUploading)
                  Container(
                    color: Colors.black54,
                    child: const Center(
                      child: Card(
                        child: Padding(
                          padding: EdgeInsets.all(24.0),
                          child: Column(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              CircularProgressIndicator(),
                              SizedBox(height: 16),
                              Text('Uploading file...'),
                            ],
                          ),
                        ),
                      ),
                    ),
                  ),
              ],
            ),
    );
  }

  Widget _buildRequirementTab(String requirementType) {
    final requirements = _requirements[requirementType] ?? [];

    if (requirements.isEmpty) {
      return EmptyRequirements(
        requirementType: requirementType,
        onUpload: () => _pickAndUploadFile(requirementType),
      );
    }

    return RefreshIndicator(
      onRefresh: _loadRequirements,
      child: Column(
        children: [
          // Upload Button Header
          Container(
            padding: const EdgeInsets.all(16),
            child: ElevatedButton.icon(
              onPressed: () => _pickAndUploadFile(requirementType),
              icon: const Icon(Icons.upload_file, color: Colors.white),
              label: const Text(
                'Upload New File',
                style: TextStyle(
                  color: Colors.white,
                  fontWeight: FontWeight.w600,
                  fontSize: 16,
                ),
              ),
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFF4A6491),
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(
                  horizontal: 24,
                  vertical: 14,
                ),
                elevation: 2,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
              ),
            ),
          ),
          
          // File Count
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16),
            child: Row(
              children: [
                Text(
                  '${requirements.length} file${requirements.length == 1 ? '' : 's'}',
                  style: const TextStyle(
                    fontSize: 14,
                    color: Color(0xFF6B7280),
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ],
            ),
          ),
          
          const SizedBox(height: 8),
          
          // Requirements List
          Expanded(
            child: ListView.builder(
              padding: const EdgeInsets.all(16),
              itemCount: requirements.length,
              itemBuilder: (context, index) {
                final requirement = requirements[index];
                return RequirementCard(
                  requirement: requirement,
                  onDelete: () => _deleteRequirement(requirement),
                  onTap: () {
                    // TODO: Implement file preview/download
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(
                        content: Text('File preview coming soon!'),
                      ),
                    );
                  },
                );
              },
            ),
          ),
        ],
      ),
    );
  }
}
