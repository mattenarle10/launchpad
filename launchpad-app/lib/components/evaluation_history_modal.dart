import 'package:flutter/material.dart';
import '../services/api/client.dart';
import '../services/api/endpoints/student.dart';

class EvaluationHistoryModal {
  static Future<void> show(BuildContext context) async {
    try {
      final studentApi = StudentApi(ApiClient.I);
      final response = await studentApi.getEvaluationHistory();
      
      print('=== Evaluation History Response ===');
      print('Full response: $response');
      
      // Handle both response structures
      final data = response['data'];
      final history = (data is Map && data.containsKey('history')) 
          ? (data['history'] as List?) ?? []
          : (data is List ? data : []);
      
      print('History count: ${history.length}');
      if (history.isEmpty) {
        print('⚠️ No evaluation history found');
      } else {
        print('✅ Found ${history.length} evaluations');
        print('First item: ${history.first}');
      }
      
      // Group evaluations by month
      final groupedEvaluations = <String, List<Map<String, dynamic>>>{};
      for (var eval in history) {
        final semester = (eval['semester'] ?? 'Unknown') as String;
        final academicYear = (eval['academic_year'] ?? '') as String;
        final key = academicYear.isNotEmpty ? '$semester Semester $academicYear' : semester;
        
        if (!groupedEvaluations.containsKey(key)) {
          groupedEvaluations[key] = [];
        }
        groupedEvaluations[key]!.add(eval as Map<String, dynamic>);
      }
      
      if (!context.mounted) return;
      
      showDialog(
        context: context,
        builder: (context) => AlertDialog(
          title: const Row(
            children: [
              Icon(Icons.history, color: Color(0xFF4A6491)),
              SizedBox(width: 8),
              Text('Evaluation History'),
            ],
          ),
          content: SizedBox(
            width: double.maxFinite,
            child: history.isEmpty
                ? const _EmptyState()
                : ListView.builder(
                    shrinkWrap: true,
                    itemCount: groupedEvaluations.length,
                    itemBuilder: (context, index) {
                      final monthKey = groupedEvaluations.keys.elementAt(index);
                      final monthEvals = groupedEvaluations[monthKey]!;
                      return _MonthGroup(
                        monthName: monthKey,
                        evaluations: monthEvals,
                      );
                    },
                  ),
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(context),
              child: const Text('Close'),
            ),
          ],
        ),
      );
    } catch (e) {
      print('Error loading evaluation history: $e');
      if (context.mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Failed to load evaluation history')),
        );
      }
    }
  }
}

class _EmptyState extends StatelessWidget {
  const _EmptyState();

  @override
  Widget build(BuildContext context) {
    return const Padding(
      padding: EdgeInsets.all(32.0),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(
            Icons.assessment_outlined,
            size: 64,
            color: Color(0xFF9CA3AF),
          ),
          SizedBox(height: 16),
          Text(
            'No evaluations yet',
            style: TextStyle(
              fontSize: 16,
              color: Color(0xFF6B7280),
            ),
          ),
        ],
      ),
    );
  }
}

class _MonthGroup extends StatelessWidget {
  final String monthName;
  final List<Map<String, dynamic>> evaluations;

  const _MonthGroup({
    required this.monthName,
    required this.evaluations,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(
          color: const Color(0xFFE5E7EB),
          width: 1.5,
        ),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.05),
            blurRadius: 4,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Month header
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: const Color(0xFF4A6491).withOpacity(0.05),
              borderRadius: const BorderRadius.only(
                topLeft: Radius.circular(11),
                topRight: Radius.circular(11),
              ),
            ),
            child: Row(
              children: [
                const Icon(
                  Icons.calendar_month,
                  size: 16,
                  color: Color(0xFF4A6491),
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: Text(
                    monthName,
                    style: const TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.bold,
                      color: Color(0xFF3D5A7E),
                    ),
                    overflow: TextOverflow.ellipsis,
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(
                    color: const Color(0xFF4A6491),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Text(
                    '${evaluations.length}/2',
                    style: const TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.bold,
                      color: Colors.white,
                    ),
                  ),
                ),
              ],
            ),
          ),
          
          // Evaluations list
          Padding(
            padding: const EdgeInsets.all(12),
            child: Column(
              children: evaluations.map((eval) => _CompactEvaluationItem(evaluation: eval)).toList(),
            ),
          ),
        ],
      ),
    );
  }
}

class _CompactEvaluationItem extends StatelessWidget {
  final Map<String, dynamic> evaluation;

  const _CompactEvaluationItem({required this.evaluation});

  @override
  Widget build(BuildContext context) {
    final score = evaluation['evaluation_rank'] as int;
    final performance = evaluation['performance_score'] as String;
    final periodName = (evaluation['period_name'] ?? 'Final') as String;
    
    final scoreColor = _getScoreColor(score);
    
    return Container(
      margin: const EdgeInsets.only(bottom: 8),
      padding: const EdgeInsets.all(10),
      decoration: BoxDecoration(
        color: const Color(0xFFF9FAFB),
        borderRadius: BorderRadius.circular(8),
        border: Border.all(
          color: const Color(0xFFE5E7EB),
          width: 1,
        ),
      ),
      child: Row(
        children: [
          // Period badge
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 4),
            decoration: BoxDecoration(
              color: const Color(0xFF4A6491).withOpacity(0.1),
              borderRadius: BorderRadius.circular(6),
            ),
            child: Text(
              periodName,
              style: const TextStyle(
                fontSize: 9,
                fontWeight: FontWeight.w600,
                color: Color(0xFF4A6491),
              ),
            ),
          ),
          const SizedBox(width: 8),
          
          // Score
          Expanded(
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Text(
                      '$score',
                      style: TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                        color: scoreColor,
                      ),
                    ),
                    Text(
                      performance,
                      style: TextStyle(
                        fontSize: 8,
                        fontWeight: FontWeight.w600,
                        color: scoreColor,
                      ),
                    ),
                  ],
                ),
                Icon(
                  Icons.check_circle,
                  color: scoreColor,
                  size: 18,
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Color _getScoreColor(int score) {
    if (score >= 80) {
      return const Color(0xFF10B981); // Green
    } else if (score >= 60) {
      return const Color(0xFFF59E0B); // Orange
    } else {
      return const Color(0xFFEF4444); // Red
    }
  }
}

class _EvaluationItem extends StatelessWidget {
  final Map<String, dynamic> evaluation;
  final int evaluationNumber;
  final int totalEvaluations;

  const _EvaluationItem({
    required this.evaluation,
    required this.evaluationNumber,
    required this.totalEvaluations,
  });

  @override
  Widget build(BuildContext context) {
    final score = evaluation['evaluation_rank'] as int;
    final performance = evaluation['performance_score'] as String;
    final companyName = evaluation['company_name'] as String;
    final feedback = evaluation['feedback'] as String?;
    final date = evaluation['evaluation_date'] as String;
    
    final scoreColor = _getScoreColor(score);
    
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      elevation: 2,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(12),
      ),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Evaluation number badge
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
              decoration: BoxDecoration(
                color: const Color(0xFF4A6491).withOpacity(0.1),
                borderRadius: BorderRadius.circular(12),
              ),
              child: Text(
                _getOrdinal(evaluationNumber) + ' Evaluation',
                style: const TextStyle(
                  fontSize: 11,
                  fontWeight: FontWeight.w600,
                  color: Color(0xFF4A6491),
                ),
              ),
            ),
            const SizedBox(height: 12),
            
            // Header: Company name and score
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Expanded(
                  child: Text(
                    companyName,
                    style: const TextStyle(
                      fontWeight: FontWeight.bold,
                      fontSize: 16,
                      color: Color(0xFF3D5A7E),
                    ),
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                  decoration: BoxDecoration(
                    color: scoreColor.withOpacity(0.1),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Text(
                    '$score/100',
                    style: TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.bold,
                      color: scoreColor,
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 8),
            
            // Performance badge
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
              decoration: BoxDecoration(
                color: _getPerformanceColor(performance).withOpacity(0.1),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Text(
                performance,
                style: TextStyle(
                  color: _getPerformanceColor(performance),
                  fontWeight: FontWeight.w600,
                  fontSize: 13,
                ),
              ),
            ),
            
            // Feedback section (if exists)
            if (feedback != null && feedback.isNotEmpty) ...[
              const SizedBox(height: 12),
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: const Color(0xFFF3F4F6),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Row(
                      children: [
                        Icon(
                          Icons.comment,
                          size: 14,
                          color: Color(0xFF6B7280),
                        ),
                        SizedBox(width: 4),
                        Text(
                          'Feedback',
                          style: TextStyle(
                            fontSize: 11,
                            fontWeight: FontWeight.w600,
                            color: Color(0xFF6B7280),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 4),
                    Text(
                      feedback,
                      style: const TextStyle(
                        fontSize: 12,
                        color: Color(0xFF374151),
                      ),
                    ),
                  ],
                ),
              ),
            ],
            
            // Date
            const SizedBox(height: 8),
            Row(
              children: [
                const Icon(
                  Icons.access_time,
                  size: 12,
                  color: Color(0xFF9CA3AF),
                ),
                const SizedBox(width: 4),
                Text(
                  _formatDate(date),
                  style: const TextStyle(
                    fontSize: 11,
                    color: Color(0xFF9CA3AF),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Color _getScoreColor(int score) {
    if (score >= 80) {
      return const Color(0xFF10B981); // Green
    } else if (score >= 60) {
      return const Color(0xFFF59E0B); // Orange
    } else {
      return const Color(0xFFEF4444); // Red
    }
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

  String _formatDate(String dateStr) {
    try {
      final date = DateTime.parse(dateStr);
      final now = DateTime.now();
      final difference = now.difference(date);

      if (difference.inDays == 0) {
        return 'Today';
      } else if (difference.inDays == 1) {
        return 'Yesterday';
      } else if (difference.inDays < 7) {
        return '${difference.inDays} days ago';
      } else {
        return '${date.day}/${date.month}/${date.year}';
      }
    } catch (e) {
      return dateStr;
    }
  }

  String _getOrdinal(int number) {
    if (number >= 11 && number <= 13) {
      return '${number}th';
    }
    switch (number % 10) {
      case 1:
        return '${number}st';
      case 2:
        return '${number}nd';
      case 3:
        return '${number}rd';
      default:
        return '${number}th';
    }
  }
}
