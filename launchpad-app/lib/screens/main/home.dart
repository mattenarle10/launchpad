import 'package:flutter/material.dart';
import '../../styles/colors.dart';
import '../../components/bottom-nav.dart';
import '../../components/menu.dart';
import '../../services/api/client.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  int _currentIndex = 1; // Home is default
  Map<String, dynamic>? _userData;
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadUserData();
  }

  Future<void> _loadUserData() async {
    try {
      final user = await ApiClient.I.getCurrentUser();
      setState(() {
        _userData = user;
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      body: SafeArea(
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
                    onTap: () {
                      // TODO: Show user profile
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
                  : Padding(
                      padding: const EdgeInsets.all(24.0),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const SizedBox(height: 20),
                          const Text(
                            'Welcome,',
                            style: TextStyle(
                              fontSize: 20,
                              color: Color(0xFF6B7280),
                            ),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            '${_userData?['first_name'] ?? ''} ${_userData?['last_name'] ?? ''}',
                            style: const TextStyle(
                              fontSize: 32,
                              fontWeight: FontWeight.bold,
                              color: Color(0xFF3D5A7E),
                            ),
                          ),
                          const SizedBox(height: 8),
                          Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 16,
                              vertical: 8,
                            ),
                            decoration: BoxDecoration(
                              color: const Color(0xFFE8EFF9),
                              borderRadius: BorderRadius.circular(20),
                            ),
                            child: Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                const Icon(
                                  Icons.school,
                                  size: 16,
                                  color: Color(0xFF4A6491),
                                ),
                                const SizedBox(width: 6),
                                Text(
                                  _userData?['course'] ?? 'Student',
                                  style: const TextStyle(
                                    fontSize: 14,
                                    color: Color(0xFF4A6491),
                                    fontWeight: FontWeight.w600,
                                  ),
                                ),
                              ],
                            ),
                          ),
                          const SizedBox(height: 8),
                          if (_userData?['company_name'] != null)
                            Row(
                              children: [
                                const Icon(
                                  Icons.business,
                                  size: 18,
                                  color: Color(0xFF6B7280),
                                ),
                                const SizedBox(width: 6),
                                Expanded(
                                  child: Text(
                                    _userData?['company_name'] ?? '',
                                    style: const TextStyle(
                                      fontSize: 16,
                                      color: Color(0xFF6B7280),
                                    ),
                                  ),
                                ),
                              ],
                            ),
                          const SizedBox(height: 12),
                          if (_userData?['id_num'] != null)
                            Text(
                              'ID: ${_userData?['id_num']}',
                              style: const TextStyle(
                                fontSize: 14,
                                color: Color(0xFF9CA3AF),
                              ),
                            ),
                          const Spacer(),
                          Center(
                            child: Column(
                              children: [
                                Image.asset(
                                  'lib/img/logo/launchpad.png',
                                  width: 80,
                                  height: 80,
                                ),
                                const SizedBox(height: 16),
                                const Text(
                                  'Your OJT Dashboard',
                                  style: TextStyle(
                                    fontSize: 16,
                                    color: Color(0xFF9CA3AF),
                                  ),
                                ),
                              ],
                            ),
                          ),
                          const Spacer(),
                        ],
                      ),
                    ),
            ),
          ],
        ),
      ),
      bottomNavigationBar: FloatingBottomNav(
        currentIndex: _currentIndex,
        onTap: (index) {
          setState(() {
            _currentIndex = index;
          });
          // TODO: Handle navigation to different pages
          if (index == 0) {
            // Jobs page
          } else if (index == 1) {
            // Home page (current)
          } else if (index == 2) {
            // Notifications page
          }
        },
      ),
    );
  }
}

