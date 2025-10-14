import 'package:flutter/material.dart';
import '../services/api/client.dart';
import '../screens/landing.dart';
import '../screens/main/report.dart';
import '../screens/main/about.dart';

class MenuOverlay extends StatelessWidget {
  const MenuOverlay({super.key});

  Future<void> _handleLogout(BuildContext context) async {
    // Close the menu first
    Navigator.pop(context);
    
    // Clear authentication
    await ApiClient.I.clearAuth();
    
    // Navigate to landing page and clear all previous routes
    if (context.mounted) {
      Navigator.pushAndRemoveUntil(
        context,
        MaterialPageRoute(builder: (context) => const LandingScreen()),
        (route) => false,
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.black54,
      child: Center(
        child: Container(
          width: MediaQuery.of(context).size.width * 0.75,
          padding: const EdgeInsets.symmetric(vertical: 24, horizontal: 20),
          decoration: BoxDecoration(
            color: const Color(0xFF4A6491),
            borderRadius: BorderRadius.circular(24),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withOpacity(0.3),
                blurRadius: 20,
                offset: const Offset(0, 8),
              ),
            ],
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Text(
                'Menu',
                style: TextStyle(
                  color: Colors.white,
                  fontSize: 26,
                  fontWeight: FontWeight.bold,
                ),
              ),
              const SizedBox(height: 24),
              _buildMenuItem(
                context,
                'About',
                Icons.info_outline,
                () {
                  Navigator.pop(context);
                  Navigator.push(
                    context,
                    MaterialPageRoute(builder: (context) => const AboutScreen()),
                  );
                },
              ),
              const SizedBox(height: 12),
              _buildMenuItem(
                context,
                'Report',
                Icons.description_outlined,
                () {
                  Navigator.pop(context);
                  Navigator.push(
                    context,
                    MaterialPageRoute(builder: (context) => const ReportScreen()),
                  );
                },
              ),
              const SizedBox(height: 12),
              _buildMenuItem(
                context,
                'Saved',
                Icons.bookmark_outline,
                () {
                  Navigator.pop(context);
                  // TODO: Navigate to Saved page
                },
              ),
              const SizedBox(height: 12),
              _buildMenuItem(
                context,
                'Logout',
                Icons.logout_outlined,
                () => _handleLogout(context),
              ),
              const SizedBox(height: 20),
              TextButton(
                onPressed: () => Navigator.pop(context),
                style: TextButton.styleFrom(
                  backgroundColor: const Color(0xFF8AAEE0),
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(horizontal: 40, vertical: 12),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(24),
                  ),
                ),
                child: const Text(
                  'Close',
                  style: TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildMenuItem(
    BuildContext context,
    String label,
    IconData icon,
    VoidCallback onTap,
  ) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(24),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
        decoration: BoxDecoration(
          color: const Color(0xFFB1C9EF),
          borderRadius: BorderRadius.circular(24),
        ),
        child: Row(
          children: [
            Text(
              label,
              style: const TextStyle(
                color: Color(0xFF3D5A7E),
                fontSize: 16,
                fontWeight: FontWeight.w600,
              ),
            ),
            const Spacer(),
            Icon(
              icon,
              color: const Color(0xFF3D5A7E),
              size: 20,
            ),
          ],
        ),
      ),
    );
  }
}

void showMenuOverlay(BuildContext context) {
  showDialog(
    context: context,
    barrierColor: Colors.transparent,
    builder: (context) => const MenuOverlay(),
  );
}

