import 'dart:io';
import 'package:flutter/material.dart';
import 'package:dio/dio.dart';
import 'package:file_picker/file_picker.dart';
import '../../styles/colors.dart';
import '../../components/custom_text_field.dart';
import '../../components/custom_button.dart';
import '../../components/toast.dart';
import '../../services/api/client.dart';
import '../../services/api/endpoints/student.dart';

class ReportScreen extends StatefulWidget {
  const ReportScreen({super.key});

  @override
  State<ReportScreen> createState() => _ReportScreenState();
}

class _ReportScreenState extends State<ReportScreen> {
  final _formKey = GlobalKey<FormState>();
  final _descriptionController = TextEditingController();
  final _activityTypeController = TextEditingController();
  
  File? _reportFile;
  String? _reportFileName;
  DateTime _selectedDate = DateTime.now();
  bool _isSubmitting = false;

  @override
  void dispose() {
    _descriptionController.dispose();
    _activityTypeController.dispose();
    super.dispose();
  }

  Future<void> _pickReportFile() async {
    try {
      final result = await FilePicker.platform.pickFiles(
        type: FileType.custom,
        allowedExtensions: ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'],
      );

      if (result != null && result.files.single.path != null) {
        setState(() {
          _reportFile = File(result.files.single.path!);
          _reportFileName = result.files.single.name;
        });
      }
    } catch (e) {
      if (mounted) {
        Toast.error(context, 'Error picking file: $e');
      }
    }
  }

  Future<void> _selectDate() async {
    final DateTime? picked = await showDatePicker(
      context: context,
      initialDate: _selectedDate,
      firstDate: DateTime.now().subtract(const Duration(days: 30)),
      lastDate: DateTime.now(),
      builder: (context, child) {
        return Theme(
          data: Theme.of(context).copyWith(
            colorScheme: const ColorScheme.light(
              primary: Color(0xFF4A6491),
            ),
          ),
          child: child!,
        );
      },
    );
    if (picked != null && picked != _selectedDate) {
      setState(() {
        _selectedDate = picked;
      });
    }
  }

  Future<void> _submitReport() async {
    if (!_formKey.currentState!.validate()) return;

    if (_reportFile == null) {
      Toast.error(context, 'Please upload your daily report file');
      return;
    }

    setState(() => _isSubmitting = true);

    try {
      final user = await ApiClient.I.getCurrentUser();
      if (user == null || user['student_id'] == null) {
        throw Exception('User not found');
      }

      // Format date as YYYY-MM-DD
      final dateStr = '${_selectedDate.year}-${_selectedDate.month.toString().padLeft(2, '0')}-${_selectedDate.day.toString().padLeft(2, '0')}';

      final formData = FormData.fromMap({
        'report_date': dateStr,
        'hours_requested': '8', // Default 8 hours, CDC will adjust
        'description': _descriptionController.text.trim().isEmpty 
            ? 'Daily OJT Activities' 
            : _descriptionController.text.trim(),
        'activity_type': _activityTypeController.text.trim().isEmpty 
            ? 'Daily Activities' 
            : _activityTypeController.text.trim(),
        'report_file': await MultipartFile.fromFile(
          _reportFile!.path,
          filename: _reportFileName,
        ),
      });

      final studentApi = StudentApi(ApiClient.I);
      await studentApi.submitDailyReport(user['student_id'], formData);

      if (!mounted) return;

      Toast.success(context, 'Report submitted! Waiting for CDC approval.');
      
      // Clear form
      setState(() {
        _descriptionController.clear();
        _activityTypeController.clear();
        _reportFile = null;
        _reportFileName = null;
        _selectedDate = DateTime.now();
      });

      // Go back after 1.5 seconds
      Future.delayed(const Duration(milliseconds: 1500), () {
        if (mounted) Navigator.pop(context);
      });
    } catch (e) {
      if (mounted) {
        Toast.error(context, e.toString().replaceAll('Exception: ', ''));
      }
    } finally {
      if (mounted) {
        setState(() => _isSubmitting = false);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: Color(0xFF4A6491)),
          onPressed: () => Navigator.pop(context),
        ),
        title: const Text(
          'Submit Daily Report',
          style: TextStyle(
            color: Color(0xFF4A6491),
            fontSize: 18,
            fontWeight: FontWeight.w600,
          ),
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20.0),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const SizedBox(height: 8),

              // Date Selector
              const Text(
                'Report Date',
                style: TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.w600,
                  color: Color(0xFF374151),
                ),
              ),
              const SizedBox(height: 8),
              GestureDetector(
                onTap: _selectDate,
                child: Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: const Color(0xFFE5E7EB)),
                  ),
                  child: Row(
                    children: [
                      const Icon(Icons.calendar_today, color: Color(0xFF6B7280), size: 20),
                      const SizedBox(width: 12),
                      Text(
                        '${_selectedDate.year}-${_selectedDate.month.toString().padLeft(2, '0')}-${_selectedDate.day.toString().padLeft(2, '0')}',
                        style: const TextStyle(
                          fontSize: 16,
                          color: Color(0xFF111827),
                        ),
                      ),
                      const Spacer(),
                      const Icon(Icons.arrow_drop_down, color: Color(0xFF6B7280)),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 20),

              // Activity Type (Optional)
              CustomTextField(
                label: 'Activity Type (Optional)',
                controller: _activityTypeController,
              ),
              const SizedBox(height: 16),

              // Description (Optional)
              CustomTextField(
                label: 'Description (Optional)',
                controller: _descriptionController,
                maxLines: 4,
              ),
              const SizedBox(height: 20),

              // File Upload
              const Text(
                'Upload Report File',
                style: TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.w600,
                  color: Color(0xFF374151),
                ),
              ),
              const SizedBox(height: 8),
              GestureDetector(
                onTap: _pickReportFile,
                child: Container(
                  height: 120,
                  decoration: BoxDecoration(
                    color: Colors.white,
                    border: Border.all(
                      color: _reportFile != null 
                          ? const Color(0xFF10B981) 
                          : const Color(0xFFE0E4E8),
                      width: 2,
                    ),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(
                          _reportFile != null
                              ? Icons.check_circle
                              : Icons.cloud_upload_outlined,
                          size: 40,
                          color: _reportFile != null
                              ? const Color(0xFF10B981)
                              : const Color(0xFF4A6491),
                        ),
                        const SizedBox(height: 12),
                        Text(
                          _reportFileName ?? 'Upload PDF, Word, or Image',
                          style: TextStyle(
                            color: _reportFile != null
                                ? const Color(0xFF10B981)
                                : const Color(0xFF6B7280),
                            fontSize: 14,
                            fontWeight: FontWeight.w500,
                          ),
                          textAlign: TextAlign.center,
                        ),
                        if (_reportFile == null)
                          const Padding(
                            padding: EdgeInsets.only(top: 4),
                            child: Text(
                              'PDF, DOC, DOCX, JPG, PNG',
                              style: TextStyle(
                                color: Color(0xFF9CA3AF),
                                fontSize: 12,
                              ),
                            ),
                          ),
                      ],
                    ),
                  ),
                ),
              ),
              const SizedBox(height: 32),

              // Submit Button
              CustomButton(
                text: 'Submit Report',
                onPressed: _submitReport,
                isLoading: _isSubmitting,
              ),
            ],
          ),
        ),
      ),
    );
  }
}
