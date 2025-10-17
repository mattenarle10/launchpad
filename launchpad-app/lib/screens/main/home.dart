import 'package:flutter/material.dart';
import '../../styles/colors.dart';
import '../../components/bottom-nav.dart';
import '../../components/menu.dart';
import '../../components/evaluation_history_modal.dart';
import '../../services/api/client.dart';
import '../../services/api/endpoints/student.dart';
import 'report.dart';
import 'profile.dart';
import 'jobs.dart';
import 'notifications.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  int _currentIndex = 1; // Home is default
  final PageController _pageController = PageController(initialPage: 1);
  Map<String, dynamic>? _userData;
  Map<String, dynamic>? _ojtProgress;
  Map<String, dynamic>? _evaluation;
  Map<String, dynamic>? _performance;
  bool _isLoading = true;
  bool _isLoadingProgress = false;
  bool _isLoadingEvaluation = false;
  bool _isLoadingPerformance = false;

  @override
  void initState() {
    super.initState();
    _loadUserData();
    _loadOjtProgress();
    _loadEvaluation();
    _loadPerformance();
  }

  @override
  void dispose() {
    _pageController.dispose();
    super.dispose();
  }

  Future<void> _loadUserData() async {
    try {
      final user = await ApiClient.I.getCurrentUser();
      setState(() {
        // Create a new map to trigger state update
        _userData = user != null ? Map<String, dynamic>.from(user) : null;
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _isLoading = false;
      });
    }
  }

  Future<void> _loadOjtProgress() async {
    setState(() {
      _isLoadingProgress = true;
    });

    try {
      final user = await ApiClient.I.getCurrentUser();
      if (user != null && user['student_id'] != null) {
        final studentApi = StudentApi(ApiClient.I);
        final response = await studentApi.getOjtProgress(user['student_id']);
        if (mounted) {
          setState(() {
            _ojtProgress = response['data']['progress'];
            _isLoadingProgress = false;
          });
        }
      } else {
        if (mounted) {
          setState(() {
            _isLoadingProgress = false;
            _ojtProgress = null;
          });
        }
      }
    } catch (e) {
      print('Error loading OJT progress: $e');
      if (mounted) {
        setState(() {
          _isLoadingProgress = false;
          _ojtProgress = null; // Set to null so it shows "Not Started"
        });
      }
    }
  }

  Future<void> _loadEvaluation() async {
    setState(() {
      _isLoadingEvaluation = true;
    });

    try {
      final studentApi = StudentApi(ApiClient.I);
      final response = await studentApi.getEvaluation();
      if (mounted) {
        setState(() {
          _evaluation = response['data'];
          _isLoadingEvaluation = false;
        });
      }
    } catch (e) {
      print('Error loading evaluation: $e');
      if (mounted) {
        setState(() {
          _isLoadingEvaluation = false;
          _evaluation = null;
        });
      }
    }
  }

  Future<void> _loadPerformance() async {
    setState(() {
      _isLoadingPerformance = true;
    });

    try {
      final studentApi = StudentApi(ApiClient.I);
      final response = await studentApi.getPerformance();
      if (mounted) {
        setState(() {
          _performance = response['data'];
          _isLoadingPerformance = false;
        });
      }
    } catch (e) {
      print('Error loading performance: $e');
      if (mounted) {
        setState(() {
          _isLoadingPerformance = false;
          _performance = null;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      body: PageView(
        controller: _pageController,
        onPageChanged: (index) {
          setState(() {
            _currentIndex = index;
          });
        },
        children: [
          const JobsScreen(),
          _buildHomePage(),
          const NotificationsScreen(),
        ],
      ),
      bottomNavigationBar: FloatingBottomNav(
        currentIndex: _currentIndex,
        onTap: (index) {
          _pageController.animateToPage(
            index,
            duration: const Duration(milliseconds: 300),
            curve: Curves.easeInOut,
          );
        },
      ),
    );
  }

  Widget _buildHomePage() {
    return SafeArea(
        child: Column(
          children: [
            // Custom Header
            Padding(
              padding: const EdgeInsets.all(16.0),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  // Menu Button
                  GestureDetector(
                    onTap: () => showMenuOverlay(context),
                    child: Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: const Color(0xFF4A6491),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Image.asset(
                        'lib/img/icon/menu.png',
                        width: 24,
                        height: 24,
                        color: Colors.white,
                      ),
                    ),
                  ),
                  // User Profile Button
                  GestureDetector(
                    onTap: () async {
                      await Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (context) => const ProfileScreen(),
                        ),
                      );
                      // Reload user data after returning from profile
                      _loadUserData();
                    },
                    child: Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 16,
                        vertical: 8,
                      ),
                      decoration: BoxDecoration(
                        color: const Color(0xFF4A6491),
                        borderRadius: BorderRadius.circular(24),
                      ),
                      child: Row(
                        children: [
                          Text(
                            _userData?['first_name'] ?? 'Student',
                            style: const TextStyle(
                              color: Colors.white,
                              fontWeight: FontWeight.w600,
                              fontSize: 16,
                            ),
                          ),
                          const SizedBox(width: 8),
                          Container(
                            width: 36,
                            height: 36,
                            decoration: BoxDecoration(
                              color: const Color(0xFFE8EBF1),
                              shape: BoxShape.circle,
                            ),
                            child: const Icon(
                              Icons.person,
                              color: Color(0xFF4A6491),
                              size: 20,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
            ),
            // Main Content
            Expanded(
              child: _isLoading
                  ? const Center(child: CircularProgressIndicator())
                  : RefreshIndicator(
                      onRefresh: () async {
                        await _loadUserData();
                        await _loadOjtProgress();
                        await _loadEvaluation();
                        await _loadPerformance();
                      },
                      child: SingleChildScrollView(
                        physics: const AlwaysScrollableScrollPhysics(),
                        padding: const EdgeInsets.all(20.0),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                          // Welcome Section
                          const Text(
                            'Welcome back,',
                            style: TextStyle(
                              fontSize: 16,
                              color: Color(0xFF6B7280),
                            ),
                          ),
                          const SizedBox(height: 4),
                          Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                '${_userData?['first_name'] ?? ''} ${_userData?['last_name'] ?? ''}',
                                style: const TextStyle(
                                  fontSize: 28,
                                  fontWeight: FontWeight.bold,
                                  color: Color(0xFF3D5A7E),
                                ),
                              ),
                              if (_userData?['specialization'] != null && _userData!['specialization'].toString().isNotEmpty) ...[
                                const SizedBox(height: 8),
                                _buildSpecializationTags(),
                              ],
                            ],
                          ),
                          const SizedBox(height: 16),
                          
                          // Info Cards Row
                          Row(
                            children: [
                              // Course Badge
                              Expanded(
                                child: Container(
                                  padding: const EdgeInsets.all(12),
                                  decoration: BoxDecoration(
                                    color: const Color(0xFFE8EFF9),
                                    borderRadius: BorderRadius.circular(12),
                                  ),
                                  child: Row(
                                    children: [
                                      const Icon(
                                        Icons.school,
                                        size: 20,
                                        color: Color(0xFF4A6491),
                                      ),
                                      const SizedBox(width: 8),
                                      Expanded(
                                        child: Text(
                                          _userData?['course'] ?? 'N/A',
                                          style: const TextStyle(
                                            fontSize: 14,
                                            color: Color(0xFF4A6491),
                                            fontWeight: FontWeight.w600,
                                          ),
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                              ),
                              const SizedBox(width: 12),
                              // ID Badge
                              Expanded(
                                child: Container(
                                  padding: const EdgeInsets.all(12),
                                  decoration: BoxDecoration(
                                    color: const Color(0xFFF3F4F6),
                                    borderRadius: BorderRadius.circular(12),
                                  ),
                                  child: Row(
                                    children: [
                                      const Icon(
                                        Icons.badge,
                                        size: 20,
                                        color: Color(0xFF6B7280),
                                      ),
                                      const SizedBox(width: 8),
                                      Expanded(
                                        child: Text(
                                          _userData?['id_num'] ?? 'N/A',
                                          style: const TextStyle(
                                            fontSize: 14,
                                            color: Color(0xFF6B7280),
                                            fontWeight: FontWeight.w600,
                                          ),
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 12),
                          
                          // Company Card
                          if (_userData?['company_name'] != null)
                            Container(
                              width: double.infinity,
                              padding: const EdgeInsets.all(16),
                              decoration: BoxDecoration(
                                gradient: const LinearGradient(
                                  colors: [Color(0xFF4A6491), Color(0xFF3D5A7E)],
                                ),
                                borderRadius: BorderRadius.circular(12),
                              ),
                              child: Row(
                                children: [
                                  const Icon(
                                    Icons.business,
                                    color: Colors.white,
                                    size: 24,
                                  ),
                                  const SizedBox(width: 12),
                                  Expanded(
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        const Text(
                                          'Company',
                                          style: TextStyle(
                                            fontSize: 12,
                                            color: Colors.white70,
                                          ),
                                        ),
                                        const SizedBox(height: 2),
                                        Text(
                                          _userData?['company_name'] ?? 'N/A',
                                          style: const TextStyle(
                                            fontSize: 16,
                                            color: Colors.white,
                                            fontWeight: FontWeight.bold,
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          const SizedBox(height: 24),
                          
                          // OJT Progress Section
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              const Text(
                                'OJT Progress',
                                style: TextStyle(
                                  fontSize: 20,
                                  fontWeight: FontWeight.bold,
                                  color: Color(0xFF3D5A7E),
                                ),
                              ),
                              TextButton.icon(
                                onPressed: () {
                                  Navigator.push(
                                    context,
                                    MaterialPageRoute(
                                      builder: (context) => const ReportScreen(),
                                    ),
                                  );
                                },
                                icon: const Icon(Icons.add_circle, size: 20),
                                label: const Text('Submit Report'),
                                style: TextButton.styleFrom(
                                  foregroundColor: const Color(0xFF4A6491),
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 16),
                          
                          // Progress Card
                          _buildProgressCard(),
                          const SizedBox(height: 20),
                          
                          // Stats Grid
                          _buildStatsGrid(),
                          ],
                        ),
                      ),
                    ),
            ),
          ],
        ),
    );
  }

  Widget _buildProgressCard() {
    // Show "Not Started" state if no progress data
    if (_isLoadingProgress) {
      return Container(
        padding: const EdgeInsets.all(24),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.05),
              blurRadius: 10,
              offset: const Offset(0, 4),
            ),
          ],
        ),
        child: const Center(child: CircularProgressIndicator()),
      );
    }

    if (_ojtProgress == null) {
      return Container(
        padding: const EdgeInsets.all(24),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.05),
              blurRadius: 10,
              offset: const Offset(0, 4),
            ),
          ],
        ),
        child: Column(
          children: [
            Icon(
              Icons.hourglass_empty,
              size: 48,
              color: Colors.grey[400],
            ),
            const SizedBox(height: 16),
            const Text(
              'Not Started',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.bold,
                color: Color(0xFF6B7280),
              ),
            ),
            const SizedBox(height: 8),
            Text(
              'Your OJT progress will appear here once you start',
              textAlign: TextAlign.center,
              style: TextStyle(
                fontSize: 13,
                color: Colors.grey[600],
              ),
            ),
          ],
        ),
      );
    }

    // Handle both string and number types from API
    final completedHours = double.tryParse(_ojtProgress?['completed_hours']?.toString() ?? '0') ?? 0.0;
    final requiredHours = double.tryParse(_ojtProgress?['required_hours']?.toString() ?? '500') ?? 500.0;
    final remainingHours = requiredHours - completedHours;
    final percentage = (completedHours / requiredHours * 100).clamp(0, 100);
    final status = _ojtProgress?['status'] ?? 'not_started';

    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.05),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                status == 'not_started' ? 'Not Started' : status == 'completed' ? 'Completed' : 'In Progress',
                style: TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.bold,
                  color: status == 'completed' ? const Color(0xFF10B981) : status == 'in_progress' ? const Color(0xFF3B82F6) : const Color(0xFF6B7280),
                ),
              ),
              Text(
                '${percentage.toStringAsFixed(1)}%',
                style: const TextStyle(
                  fontSize: 24,
                  fontWeight: FontWeight.bold,
                  color: Color(0xFF4A6491),
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),
          // Progress Bar
          ClipRRect(
            borderRadius: BorderRadius.circular(8),
            child: LinearProgressIndicator(
              value: percentage / 100,
              minHeight: 12,
              backgroundColor: const Color(0xFFE5E7EB),
              valueColor: AlwaysStoppedAnimation<Color>(
                status == 'completed' ? const Color(0xFF10B981) : const Color(0xFF4A6491),
              ),
            ),
          ),
          const SizedBox(height: 16),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text(
                    'Completed',
                    style: TextStyle(
                      fontSize: 12,
                      color: Color(0xFF6B7280),
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    '${completedHours.toStringAsFixed(0)} hrs',
                    style: const TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                      color: Color(0xFF10B981),
                    ),
                  ),
                ],
              ),
              Column(
                crossAxisAlignment: CrossAxisAlignment.end,
                children: [
                  const Text(
                    'Remaining',
                    style: TextStyle(
                      fontSize: 12,
                      color: Color(0xFF6B7280),
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    '${remainingHours.toStringAsFixed(0)} hrs',
                    style: const TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                      color: Color(0xFFEF4444),
                    ),
                  ),
                ],
              ),
            ],
          ),
          const SizedBox(height: 12),
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: const Color(0xFFF3F4F6),
              borderRadius: BorderRadius.circular(8),
            ),
            child: Row(
              children: [
                const Icon(
                  Icons.access_time,
                  size: 16,
                  color: Color(0xFF6B7280),
                ),
                const SizedBox(width: 8),
                Text(
                  'Required: ${requiredHours.toStringAsFixed(0)} hours',
                  style: const TextStyle(
                    fontSize: 13,
                    color: Color(0xFF6B7280),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Color _getPerformanceColor(String performance) {
    switch (performance) {
      case 'Excellent':
        return const Color(0xFF10B981); // Green
      case 'Good':
        return const Color(0xFF3B82F6); // Blue
      case 'Satisfactory':
        return const Color(0xFFF59E0B); // Orange
      case 'Needs Improvement':
        return const Color(0xFFEF4444); // Red
      case 'Poor':
        return const Color(0xFF991B1B); // Dark Red
      default:
        return const Color(0xFF6B7280); // Gray
    }
  }

  Widget _buildStatsGrid() {
    return Row(
      children: [
        Expanded(
          child: GestureDetector(
            onTap: () => EvaluationHistoryModal.show(context),
            child: _buildStatCard(
              icon: Icons.assessment,
              title: 'Evaluation',
              value: _evaluation != null && _evaluation!['evaluation_rank'] != null 
                  ? '${_evaluation!['evaluation_rank']}/100'
                  : 'N/A',
              subtitle: _evaluation != null && _evaluation!['evaluation_rank'] != null
                  ? 'Tap to view history'
                  : 'Not yet evaluated',
              color: _evaluation != null && _evaluation!['evaluation_rank'] != null
                ? (_evaluation!['evaluation_rank'] >= 80 
                    ? const Color(0xFF10B981) 
                    : _evaluation!['evaluation_rank'] >= 60
                        ? const Color(0xFFF59E0B)
                        : const Color(0xFFEF4444))
                : const Color(0xFF3B82F6),
            ),
          ),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: _buildStatCard(
            icon: Icons.star,
            title: 'Performance',
            value: _performance != null && _performance!['performance_score'] != null
                ? _performance!['performance_score']
                : 'N/A',
            subtitle: _performance != null && _performance!['performance_score'] != null
                ? 'Company Assessment'
                : 'Not yet assessed',
            color: _performance != null && _performance!['performance_score'] != null
                ? _getPerformanceColor(_performance!['performance_score'])
                : const Color(0xFFF59E0B),
          ),
        ),
      ],
    );
  }

  Widget _buildStatCard({
    required IconData icon,
    required String title,
    required String value,
    required String subtitle,
    required Color color,
  }) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.05),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            padding: const EdgeInsets.all(8),
            decoration: BoxDecoration(
              color: color.withOpacity(0.1),
              borderRadius: BorderRadius.circular(8),
            ),
            child: Icon(
              icon,
              color: color,
              size: 20,
            ),
          ),
          const SizedBox(height: 12),
          Text(
            title,
            style: const TextStyle(
              fontSize: 12,
              color: Color(0xFF6B7280),
            ),
          ),
          const SizedBox(height: 4),
          Text(
            value,
            style: const TextStyle(
              fontSize: 20,
              fontWeight: FontWeight.bold,
              color: Color(0xFF3D5A7E),
            ),
          ),
          const SizedBox(height: 4),
          Text(
            subtitle,
            style: const TextStyle(
              fontSize: 11,
              color: Color(0xFF9CA3AF),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSpecializationTags() {
    final specializationString = _userData!['specialization'].toString();
    final specializations = specializationString.split(',').map((e) => e.trim()).toList();
    
    // Show max 2 tags, then "+X more"
    final displayCount = specializations.length > 2 ? 2 : specializations.length;
    final remaining = specializations.length - displayCount;
    
    return Wrap(
      spacing: 6,
      runSpacing: 6,
      children: [
        ...specializations.take(displayCount).map((spec) {
          return Container(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
            decoration: BoxDecoration(
              color: const Color(0xFF4A6491).withOpacity(0.1),
              borderRadius: BorderRadius.circular(12),
              border: Border.all(
                color: const Color(0xFF4A6491).withOpacity(0.3),
                width: 1,
              ),
            ),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                const Icon(
                  Icons.star,
                  size: 12,
                  color: Color(0xFF4A6491),
                ),
                const SizedBox(width: 4),
                Flexible(
                  child: Text(
                    spec,
                    style: const TextStyle(
                      fontSize: 11,
                      color: Color(0xFF4A6491),
                      fontWeight: FontWeight.w600,
                    ),
                    overflow: TextOverflow.ellipsis,
                    maxLines: 1,
                  ),
                ),
              ],
            ),
          );
        }).toList(),
        if (remaining > 0)
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
            decoration: BoxDecoration(
              color: const Color(0xFF6B7280).withOpacity(0.1),
              borderRadius: BorderRadius.circular(12),
              border: Border.all(
                color: const Color(0xFF6B7280).withOpacity(0.3),
                width: 1,
              ),
            ),
            child: Text(
              '+$remaining more',
              style: const TextStyle(
                fontSize: 11,
                color: Color(0xFF6B7280),
                fontWeight: FontWeight.w600,
              ),
            ),
          ),
      ],
    );
  }
}

