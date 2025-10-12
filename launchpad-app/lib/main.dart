import 'package:flutter/material.dart';

void main() => runApp(const LaunchPadApp());

class LaunchPadApp extends StatelessWidget {
  const LaunchPadApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'LaunchPad',
      theme: ThemeData(
        colorScheme: ColorScheme.fromSeed(seedColor: const Color(0xFF395886)),
        useMaterial3: true,
      ),
      home: const _BlankScreen(),
      debugShowCheckedModeBanner: false,
    );
  }
}

class _BlankScreen extends StatelessWidget {
  const _BlankScreen();

  @override
  Widget build(BuildContext context) {
    return const Scaffold(
      backgroundColor: Colors.white,
      body: Center(
        child: Text('LaunchPad', style: TextStyle(fontSize: 22)),
      ),
    );
  }
}
