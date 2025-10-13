import 'package:flutter/material.dart';
import '../components/custom_button.dart';
import '../styles/colors.dart';
import 'auth/login.dart';
import 'auth/signup.dart';

class LandingScreen extends StatelessWidget {
  const LandingScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsets.all(32),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Spacer(),
              // Logo
              Image.asset(
                'lib/img/logo/launchpad.png',
                width: 140,
                height: 140,
              ),
              const SizedBox(height: 32),
              // App name
              const Text(
                'LaunchPad',
                style: TextStyle(
                  fontSize: 36,
                  fontWeight: FontWeight.bold,
                  color: AppColors.primary,
                ),
              ),
              const SizedBox(height: 8),
              const Text(
                'OJT Tracking System',
                style: TextStyle(
                  fontSize: 16,
                  color: AppColors.textDark,
                ),
              ),
              const Spacer(),
              // Login button
              CustomButton(
                text: 'Login',
                onPressed: () {
                  Navigator.push(
                    context,
                    MaterialPageRoute(builder: (context) => const LoginScreen()),
                  );
                },
              ),
              const SizedBox(height: 16),
              // Sign Up button
              CustomButton(
                text: 'Sign Up',
                onPressed: () {
                  Navigator.push(
                    context,
                    MaterialPageRoute(builder: (context) => const SignUpScreen()),
                  );
                },
                isOutlined: true,
                color: AppColors.primary,
                textColor: AppColors.primary,
              ),
              const SizedBox(height: 32),
            ],
          ),
        ),
      ),
    );
  }
}

