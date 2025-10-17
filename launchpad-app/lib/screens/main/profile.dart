import 'package:flutter/material.dart';
import '../../styles/colors.dart';
import '../../components/custom_text_field.dart';
import '../../components/custom_button.dart';
import '../../components/toast.dart';
import '../../services/api/client.dart';
import '../../services/api/endpoints/student.dart';

class ProfileScreen extends StatefulWidget {
  const ProfileScreen({super.key});

  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
  final _formKey = GlobalKey<FormState>();
  final _firstNameController = TextEditingController();
  final _lastNameController = TextEditingController();
  final _emailController = TextEditingController();
  final _contactController = TextEditingController();
  
  List<String> _selectedSpecializations = [];
  
  bool _isLoading = true;
  bool _isSaving = false;
  Map<String, dynamic>? _profileData;
  
  final List<String> _specializationOptions = [
    'Web Development',
    'Mobile Development',
    'UI/UX Design',
    'Data Science',
    'AI/ML',
    'Cybersecurity',
    'Cloud Computing',
    'DevOps',
    'Game Dev',
    'Database',
    'Networking',
    'QA/Testing',
    'System Admin',
    'Architecture',
  ];

  @override
  void initState() {
    super.initState();
    _loadProfile();
  }

  @override
  void dispose() {
    _firstNameController.dispose();
    _lastNameController.dispose();
    _emailController.dispose();
    _contactController.dispose();
    super.dispose();
  }

  Future<void> _loadProfile() async {
    setState(() => _isLoading = true);

    try {
      final studentApi = StudentApi(ApiClient.I);
      final response = await studentApi.getProfile();
      final profile = response['data'];

      setState(() {
        _profileData = profile;
        _firstNameController.text = profile['first_name'] ?? '';
        _lastNameController.text = profile['last_name'] ?? '';
        _emailController.text = profile['email'] ?? '';
        _contactController.text = profile['contact_num'] ?? '';
        
        // Parse specialization from comma-separated string
        if (profile['specialization'] != null && profile['specialization'].toString().isNotEmpty) {
          _selectedSpecializations = profile['specialization'].toString().split(',').map((e) => e.trim()).toList();
        }
        
        _isLoading = false;
      });
    } catch (e) {
      setState(() => _isLoading = false);
      if (mounted) {
        Toast.error(context, 'Failed to load profile');
      }
    }
  }

  Future<void> _saveProfile() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() => _isSaving = true);

    try {
      final studentApi = StudentApi(ApiClient.I);
      // Join specializations as comma-separated string
      final specializationString = _selectedSpecializations.isEmpty ? null : _selectedSpecializations.join(', ');
      
      await studentApi.updateProfile(
        firstName: _firstNameController.text.trim(),
        lastName: _lastNameController.text.trim(),
        email: _emailController.text.trim(),
        contactNum: _contactController.text.trim(),
        specialization: specializationString,
      );

      // Update cached user data and persist to SharedPreferences
      final currentUser = await ApiClient.I.getCurrentUser();
      if (currentUser != null) {
        // Update the user object
        currentUser['first_name'] = _firstNameController.text.trim();
        currentUser['last_name'] = _lastNameController.text.trim();
        currentUser['email'] = _emailController.text.trim();
        currentUser['contact_num'] = _contactController.text.trim();
        currentUser['specialization'] = specializationString;
        
        // Persist the updated user data
        await ApiClient.I.updateUser(currentUser);
      }

      if (mounted) {
        Toast.success(context, 'Profile updated successfully!');
        Navigator.pop(context);
      }
    } catch (e) {
      if (mounted) {
        Toast.error(context, e.toString().replaceAll('Exception: ', ''));
      }
    } finally {
      if (mounted) {
        setState(() => _isSaving = false);
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
          'Edit Profile',
          style: TextStyle(
            color: Color(0xFF4A6491),
            fontWeight: FontWeight.w600,
          ),
        ),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : SingleChildScrollView(
              padding: const EdgeInsets.all(20.0),
              child: Form(
                key: _formKey,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Profile Picture Placeholder
                    Center(
                      child: Column(
                        children: [
                          Container(
                            width: 100,
                            height: 100,
                            decoration: BoxDecoration(
                              color: const Color(0xFFE8EBF1),
                              shape: BoxShape.circle,
                            ),
                            child: const Icon(
                              Icons.person,
                              size: 50,
                              color: Color(0xFF4A6491),
                            ),
                          ),
                          const SizedBox(height: 8),
                          Text(
                            _profileData?['id_num'] ?? '',
                            style: const TextStyle(
                              fontSize: 14,
                              color: Color(0xFF6B7280),
                              fontWeight: FontWeight.w500,
                            ),
                          ),
                          const SizedBox(height: 4),
                          Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 12,
                              vertical: 4,
                            ),
                            decoration: BoxDecoration(
                              color: const Color(0xFFE8EFF9),
                              borderRadius: BorderRadius.circular(12),
                            ),
                            child: Text(
                              _profileData?['course'] ?? '',
                              style: const TextStyle(
                                fontSize: 12,
                                color: Color(0xFF4A6491),
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 32),

                    // Form Fields
                    const Text(
                      'Personal Information',
                      style: TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.w600,
                        color: Color(0xFF3D5A7E),
                      ),
                    ),
                    const SizedBox(height: 16),

                    CustomTextField(
                      controller: _firstNameController,
                      label: 'First Name',
                      validator: (value) {
                        if (value == null || value.isEmpty) {
                          return 'First name is required';
                        }
                        return null;
                      },
                    ),
                    const SizedBox(height: 16),

                    CustomTextField(
                      controller: _lastNameController,
                      label: 'Last Name',
                      validator: (value) {
                        if (value == null || value.isEmpty) {
                          return 'Last name is required';
                        }
                        return null;
                      },
                    ),
                    const SizedBox(height: 16),

                    CustomTextField(
                      controller: _emailController,
                      label: 'Email',
                      keyboardType: TextInputType.emailAddress,
                      validator: (value) {
                        if (value == null || value.isEmpty) {
                          return 'Email is required';
                        }
                        if (!value.contains('@')) {
                          return 'Please enter a valid email';
                        }
                        return null;
                      },
                    ),
                    const SizedBox(height: 16),

                    CustomTextField(
                      controller: _contactController,
                      label: 'Contact Number',
                      keyboardType: TextInputType.phone,
                      validator: (value) {
                        if (value == null || value.isEmpty) {
                          return 'Contact number is required';
                        }
                        return null;
                      },
                    ),
                    const SizedBox(height: 16),

                    // Specialization Selector
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'Specializations (Optional)',
                          style: TextStyle(
                            fontSize: 13,
                            color: Color(0xFF7A8BA0),
                            fontWeight: FontWeight.w500,
                          ),
                        ),
                        const SizedBox(height: 8),
                        const Text(
                          'Tap to select your areas of expertise',
                          style: TextStyle(
                            fontSize: 11,
                            color: Color(0xFF9CA3AF),
                          ),
                        ),
                        const SizedBox(height: 12),
                        Wrap(
                          spacing: 8,
                          runSpacing: 8,
                          children: _specializationOptions.map((spec) {
                            final isSelected = _selectedSpecializations.contains(spec);
                            return GestureDetector(
                              onTap: () {
                                setState(() {
                                  if (isSelected) {
                                    _selectedSpecializations.remove(spec);
                                  } else {
                                    _selectedSpecializations.add(spec);
                                  }
                                });
                              },
                              child: Container(
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 12,
                                  vertical: 8,
                                ),
                                decoration: BoxDecoration(
                                  color: isSelected
                                      ? const Color(0xFF4A6491)
                                      : Colors.white,
                                  borderRadius: BorderRadius.circular(20),
                                  border: Border.all(
                                    color: isSelected
                                        ? const Color(0xFF4A6491)
                                        : const Color(0xFFE0E4E8),
                                    width: 1.5,
                                  ),
                                ),
                                child: Row(
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    if (isSelected)
                                      const Padding(
                                        padding: EdgeInsets.only(right: 4),
                                        child: Icon(
                                          Icons.check_circle,
                                          size: 14,
                                          color: Colors.white,
                                        ),
                                      ),
                                    Text(
                                      spec,
                                      style: TextStyle(
                                        fontSize: 12,
                                        fontWeight: isSelected
                                            ? FontWeight.w600
                                            : FontWeight.w500,
                                        color: isSelected
                                            ? Colors.white
                                            : const Color(0xFF374151),
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            );
                          }).toList(),
                        ),
                      ],
                    ),
                    const SizedBox(height: 32),

                    // Save Button
                    CustomButton(
                      text: _isSaving ? 'Saving...' : 'Save Changes',
                      onPressed: _isSaving ? null : _saveProfile,
                    ),
                  ],
                ),
              ),
            ),
    );
  }
}
