import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../styles/colors.dart';
import '../../services/api/client.dart';
import '../../services/api/endpoints/jobs.dart';
import '../../components/toast.dart';

class JobsScreen extends StatefulWidget {
  const JobsScreen({super.key});

  @override
  State<JobsScreen> createState() => _JobsScreenState();
}

class _JobsScreenState extends State<JobsScreen> {
  List<dynamic> _jobs = [];
  List<dynamic> _filteredJobs = [];
  bool _isLoading = true;
  String _searchQuery = '';
  String? _selectedTag;
  Set<String> _allTags = {};
  final TextEditingController _searchController = TextEditingController();

  @override
  void initState() {
    super.initState();
    _loadJobs();
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _loadJobs() async {
    setState(() => _isLoading = true);

    try {
      final jobsApi = JobsApi(ApiClient.I);
      final jobs = await jobsApi.getAllJobs();
      
      if (mounted) {
        setState(() {
          _jobs = jobs;
          _filteredJobs = jobs;
          _isLoading = false;
          _extractTags();
        });
      }
    } catch (e) {
      print('Error loading jobs: $e');
      if (mounted) {
        setState(() => _isLoading = false);
        Toast.error(context, 'Failed to load job opportunities');
      }
    }
  }

  void _extractTags() {
    _allTags.clear();
    for (var job in _jobs) {
      if (job['tags'] != null && job['tags'].toString().isNotEmpty) {
        final tags = job['tags'].toString().split(',');
        for (var tag in tags) {
          _allTags.add(tag.trim());
        }
      }
    }
  }

  void _filterJobs() {
    setState(() {
      _filteredJobs = _jobs.where((job) {
        // Search filter
        final matchesSearch = _searchQuery.isEmpty ||
            job['title'].toString().toLowerCase().contains(_searchQuery.toLowerCase()) ||
            job['company_name'].toString().toLowerCase().contains(_searchQuery.toLowerCase()) ||
            (job['location']?.toString().toLowerCase().contains(_searchQuery.toLowerCase()) ?? false);

        // Tag filter
        final matchesTag = _selectedTag == null ||
            (job['tags'] != null &&
                job['tags']
                    .toString()
                    .split(',')
                    .map((t) => t.trim())
                    .contains(_selectedTag));

        return matchesSearch && matchesTag;
      }).toList();
    });
  }

  Future<void> _launchApplicationUrl(String url) async {
    try {
      final uri = Uri.parse(url);
      if (await canLaunchUrl(uri)) {
        await launchUrl(
          uri,
          mode: LaunchMode.externalApplication,
        );
      } else {
        if (mounted) {
          Toast.error(context, 'Could not open application link');
        }
      }
    } catch (e) {
      print('Error launching URL: $e');
      if (mounted) {
        Toast.error(context, 'Failed to open link');
      }
    }
  }

  void _showJobDetails(Map<String, dynamic> job) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) => DraggableScrollableSheet(
        initialChildSize: 0.9,
        minChildSize: 0.5,
        maxChildSize: 0.95,
        builder: (_, controller) => Container(
          decoration: const BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
          ),
          child: Column(
            children: [
              // Handle bar
              Container(
                margin: const EdgeInsets.symmetric(vertical: 12),
                width: 40,
                height: 4,
                decoration: BoxDecoration(
                  color: Colors.grey[300],
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
              Expanded(
                child: ListView(
                  controller: controller,
                  padding: const EdgeInsets.all(20),
                  children: [
                    // Job Title
                    Text(
                      job['title'] ?? '',
                      style: const TextStyle(
                        fontSize: 24,
                        fontWeight: FontWeight.bold,
                        color: Color(0xFF3D5A7E),
                      ),
                    ),
                    const SizedBox(height: 8),
                    
                    // Company Name
                    Row(
                      children: [
                        const Icon(
                          Icons.business,
                          size: 18,
                          color: Color(0xFF6B7280),
                        ),
                        const SizedBox(width: 6),
                        Text(
                          job['company_name'] ?? '',
                          style: const TextStyle(
                            fontSize: 16,
                            color: Color(0xFF6B7280),
                            fontWeight: FontWeight.w500,
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 20),
                    
                    // Job Details
                    _buildDetailRow(
                      Icons.work_outline,
                      'Job Type',
                      job['job_type'] ?? 'N/A',
                    ),
                    const SizedBox(height: 12),
                    
                    if (job['location'] != null)
                      _buildDetailRow(
                        Icons.location_on_outlined,
                        'Location',
                        job['location'],
                      ),
                    if (job['location'] != null)
                      const SizedBox(height: 12),
                    
                    if (job['salary_range'] != null)
                      _buildDetailRow(
                        Icons.attach_money,
                        'Salary Range',
                        job['salary_range'],
                      ),
                    if (job['salary_range'] != null)
                      const SizedBox(height: 20),
                    
                    // Tech Specializations
                    if (job['tags'] != null && job['tags'].toString().isNotEmpty) ...[
                      const Text(
                        'Tech Specializations',
                        style: TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.w600,
                          color: Color(0xFF3D5A7E),
                        ),
                      ),
                      const SizedBox(height: 8),
                      Wrap(
                        spacing: 8,
                        runSpacing: 8,
                        children: job['tags']
                            .toString()
                            .split(',')
                            .map((tag) => _buildTagChip(tag.trim()))
                            .toList(),
                      ),
                      const SizedBox(height: 20),
                    ],
                    
                    // Description
                    const Text(
                      'Description',
                      style: TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.w600,
                        color: Color(0xFF3D5A7E),
                      ),
                    ),
                    const SizedBox(height: 8),
                    Text(
                      job['description'] ?? '',
                      style: const TextStyle(
                        fontSize: 14,
                        color: Color(0xFF374151),
                        height: 1.6,
                      ),
                    ),
                    const SizedBox(height: 20),
                    
                    // Requirements
                    if (job['requirements'] != null) ...[
                      const Text(
                        'Requirements',
                        style: TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.w600,
                          color: Color(0xFF3D5A7E),
                        ),
                      ),
                      const SizedBox(height: 8),
                      Text(
                        job['requirements'],
                        style: const TextStyle(
                          fontSize: 14,
                          color: Color(0xFF374151),
                          height: 1.6,
                        ),
                      ),
                      const SizedBox(height: 20),
                    ],
                    
                    // Apply Now Button
                    if (job['application_url'] != null && job['application_url'].toString().isNotEmpty)
                      SizedBox(
                        width: double.infinity,
                        child: ElevatedButton.icon(
                          onPressed: () => _launchApplicationUrl(job['application_url']),
                          icon: const Icon(Icons.open_in_new, size: 20),
                          label: const Text(
                            'Apply Now',
                            style: TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: const Color(0xFF4A6491),
                            foregroundColor: Colors.white,
                            padding: const EdgeInsets.symmetric(vertical: 16),
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(12),
                            ),
                            elevation: 2,
                          ),
                        ),
                      ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildDetailRow(IconData icon, String label, String value) {
    return Row(
      children: [
        Icon(
          icon,
          size: 20,
          color: const Color(0xFF4A6491),
        ),
        const SizedBox(width: 8),
        Text(
          '$label: ',
          style: const TextStyle(
            fontSize: 14,
            fontWeight: FontWeight.w600,
            color: Color(0xFF6B7280),
          ),
        ),
        Expanded(
          child: Text(
            value,
            style: const TextStyle(
              fontSize: 14,
              color: Color(0xFF374151),
            ),
          ),
        ),
      ],
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      body: SafeArea(
        child: Column(
          children: [
            // Header
            Padding(
              padding: const EdgeInsets.all(16.0),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      const Text(
                        'Job Opportunities',
                        style: TextStyle(
                          fontSize: 24,
                          fontWeight: FontWeight.bold,
                          color: Color(0xFF3D5A7E),
                        ),
                      ),
                      const Spacer(),
                      IconButton(
                        icon: const Icon(
                          Icons.refresh,
                          color: Color(0xFF4A6491),
                        ),
                        onPressed: _loadJobs,
                      ),
                    ],
                  ),
                  const SizedBox(height: 12),
                  
                  // Search Bar
                  TextField(
                    controller: _searchController,
                    onChanged: (value) {
                      setState(() => _searchQuery = value);
                      _filterJobs();
                    },
                    decoration: InputDecoration(
                      hintText: 'Search jobs, companies, locations...',
                      prefixIcon: const Icon(Icons.search, color: Color(0xFF6B7280)),
                      suffixIcon: _searchQuery.isNotEmpty
                          ? IconButton(
                              icon: const Icon(Icons.clear, color: Color(0xFF6B7280)),
                              onPressed: () {
                                _searchController.clear();
                                setState(() => _searchQuery = '');
                                _filterJobs();
                              },
                            )
                          : null,
                      filled: true,
                      fillColor: Colors.white,
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                        borderSide: BorderSide(color: Colors.grey[300]!),
                      ),
                      enabledBorder: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                        borderSide: BorderSide(color: Colors.grey[300]!),
                      ),
                      focusedBorder: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                        borderSide: const BorderSide(color: Color(0xFF4A6491), width: 2),
                      ),
                      contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                    ),
                  ),
                  const SizedBox(height: 12),
                  
                  // Tag Filter Dropdown
                  if (_allTags.isNotEmpty)
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: Colors.grey[300]!),
                        boxShadow: [
                          BoxShadow(
                            color: Colors.black.withOpacity(0.05),
                            blurRadius: 4,
                            offset: const Offset(0, 2),
                          ),
                        ],
                      ),
                      child: DropdownButtonHideUnderline(
                        child: DropdownButton<String?>(
                          value: _selectedTag,
                          isExpanded: true,
                          hint: Row(
                            children: [
                              Icon(
                                Icons.filter_list,
                                color: const Color(0xFF8B5CF6),
                                size: 20,
                              ),
                              const SizedBox(width: 8),
                              const Text(
                                'Filter by Specialization',
                                style: TextStyle(
                                  color: Color(0xFF6B7280),
                                  fontSize: 14,
                                  fontWeight: FontWeight.w500,
                                ),
                              ),
                            ],
                          ),
                          icon: Icon(
                            Icons.keyboard_arrow_down_rounded,
                            color: const Color(0xFF4A6491),
                            size: 24,
                          ),
                          dropdownColor: Colors.white,
                          borderRadius: BorderRadius.circular(12),
                          elevation: 8,
                          style: const TextStyle(
                            color: Color(0xFF374151),
                            fontSize: 14,
                            fontWeight: FontWeight.w500,
                          ),
                          selectedItemBuilder: (BuildContext context) {
                            return [
                              Row(
                                children: [
                                  Icon(
                                    Icons.filter_list,
                                    color: const Color(0xFF8B5CF6),
                                    size: 20,
                                  ),
                                  const SizedBox(width: 8),
                                  const Text(
                                    'All Specializations',
                                    style: TextStyle(
                                      color: Color(0xFF374151),
                                      fontSize: 14,
                                      fontWeight: FontWeight.w500,
                                    ),
                                  ),
                                ],
                              ),
                              ..._allTags.map((tag) => Row(
                                    children: [
                                      Container(
                                        width: 8,
                                        height: 8,
                                        decoration: const BoxDecoration(
                                          color: Color(0xFF8B5CF6),
                                          shape: BoxShape.circle,
                                        ),
                                      ),
                                      const SizedBox(width: 12),
                                      Expanded(
                                        child: Text(
                                          tag,
                                          style: const TextStyle(
                                            color: Color(0xFF374151),
                                            fontSize: 14,
                                            fontWeight: FontWeight.w600,
                                          ),
                                          overflow: TextOverflow.ellipsis,
                                        ),
                                      ),
                                    ],
                                  )),
                            ];
                          },
                          items: [
                            DropdownMenuItem<String?>(
                              value: null,
                              child: Row(
                                children: [
                                  Icon(
                                    Icons.clear_all,
                                    color: const Color(0xFF6B7280),
                                    size: 18,
                                  ),
                                  const SizedBox(width: 8),
                                  const Text(
                                    'All Specializations',
                                    style: TextStyle(
                                      fontWeight: FontWeight.w600,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                            ..._allTags.map((tag) => DropdownMenuItem<String?>(
                                  value: tag,
                                  child: Container(
                                    padding: const EdgeInsets.symmetric(vertical: 4),
                                    child: Row(
                                      children: [
                                        Container(
                                          padding: const EdgeInsets.all(6),
                                          decoration: BoxDecoration(
                                            color: const Color(0xFF8B5CF6).withOpacity(0.1),
                                            borderRadius: BorderRadius.circular(6),
                                          ),
                                          child: const Icon(
                                            Icons.tag,
                                            color: Color(0xFF8B5CF6),
                                            size: 14,
                                          ),
                                        ),
                                        const SizedBox(width: 10),
                                        Expanded(
                                          child: Text(
                                            tag,
                                            style: const TextStyle(
                                              fontSize: 13,
                                              fontWeight: FontWeight.w500,
                                            ),
                                            overflow: TextOverflow.ellipsis,
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                )),
                          ],
                          onChanged: (value) {
                            setState(() => _selectedTag = value);
                            _filterJobs();
                          },
                        ),
                      ),
                    ),
                ],
              ),
            ),
            
            // Jobs List
            Expanded(
              child: _isLoading
                  ? const Center(child: CircularProgressIndicator())
                  : _filteredJobs.isEmpty
                      ? Center(
                          child: Column(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Icon(
                                Icons.work_outline,
                                size: 64,
                                color: Colors.grey[400],
                              ),
                              const SizedBox(height: 16),
                              Text(
                                _jobs.isEmpty
                                    ? 'No job opportunities available'
                                    : 'No jobs match your search',
                                style: TextStyle(
                                  fontSize: 16,
                                  color: Colors.grey[600],
                                ),
                              ),
                              if (_jobs.isNotEmpty && _filteredJobs.isEmpty) ...[
                                const SizedBox(height: 8),
                                TextButton(
                                  onPressed: () {
                                    _searchController.clear();
                                    setState(() {
                                      _searchQuery = '';
                                      _selectedTag = null;
                                    });
                                    _filterJobs();
                                  },
                                  child: const Text('Clear Filters'),
                                ),
                              ],
                            ],
                          ),
                        )
                      : RefreshIndicator(
                          onRefresh: _loadJobs,
                          child: ListView.builder(
                            padding: const EdgeInsets.symmetric(horizontal: 16),
                            itemCount: _filteredJobs.length,
                            itemBuilder: (context, index) {
                              final job = _filteredJobs[index];
                              return _buildJobCard(job);
                            },
                          ),
                        ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildJobCard(Map<String, dynamic> job) {
    final hasApplicationUrl = job['application_url'] != null && 
                              job['application_url'].toString().isNotEmpty;
    
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      elevation: 2,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(12),
      ),
      child: InkWell(
        onTap: () => _showJobDetails(job),
        borderRadius: BorderRadius.circular(12),
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Header Row with Title and Apply Icon
              Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Job Title
                  Expanded(
                    child: Text(
                      job['title'] ?? '',
                      style: const TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.w600,
                        color: Color(0xFF3D5A7E),
                      ),
                    ),
                  ),
                  // Apply Now indicator
                  if (hasApplicationUrl)
                    Container(
                      padding: const EdgeInsets.all(6),
                      decoration: BoxDecoration(
                        color: const Color(0xFF10B981).withOpacity(0.1),
                        borderRadius: BorderRadius.circular(8),
                        border: Border.all(
                          color: const Color(0xFF10B981),
                          width: 1.5,
                        ),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          const Icon(
                            Icons.open_in_new,
                            size: 14,
                            color: Color(0xFF10B981),
                          ),
                          const SizedBox(width: 4),
                          const Text(
                            'Apply',
                            style: TextStyle(
                              fontSize: 11,
                              fontWeight: FontWeight.w700,
                              color: Color(0xFF10B981),
                            ),
                          ),
                        ],
                      ),
                    ),
                ],
              ),
              const SizedBox(height: 8),
              
              // Company Name
              Row(
                children: [
                  const Icon(
                    Icons.business,
                    size: 16,
                    color: Color(0xFF6B7280),
                  ),
                  const SizedBox(width: 4),
                  Text(
                    job['company_name'] ?? '',
                    style: const TextStyle(
                      fontSize: 14,
                      color: Color(0xFF6B7280),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 12),
              
              // Job Type and Location
              Wrap(
                spacing: 8,
                runSpacing: 8,
                children: [
                  _buildChip(
                    job['job_type'] ?? '',
                    const Color(0xFF3B82F6),
                  ),
                  if (job['location'] != null)
                    _buildChip(
                      job['location'],
                      const Color(0xFF10B981),
                    ),
                ],
              ),
              
              // Tech Specializations Tags
              if (job['tags'] != null && job['tags'].toString().isNotEmpty) ...[
                const SizedBox(height: 8),
                Wrap(
                  spacing: 6,
                  runSpacing: 6,
                  children: job['tags']
                      .toString()
                      .split(',')
                      .take(3)
                      .map((tag) => _buildTagChip(tag.trim()))
                      .toList(),
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildChip(String label, Color color) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: color.withOpacity(0.1),
        borderRadius: BorderRadius.circular(12),
      ),
      child: Text(
        label,
        style: TextStyle(
          fontSize: 12,
          fontWeight: FontWeight.w500,
          color: color,
        ),
      ),
    );
  }

  Widget _buildTagChip(String tag) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
      decoration: BoxDecoration(
        color: const Color(0xFF8B5CF6).withOpacity(0.1),
        borderRadius: BorderRadius.circular(8),
        border: Border.all(
          color: const Color(0xFF8B5CF6).withOpacity(0.3),
          width: 1,
        ),
      ),
      child: Text(
        tag,
        style: const TextStyle(
          fontSize: 11,
          fontWeight: FontWeight.w600,
          color: Color(0xFF8B5CF6),
        ),
      ),
    );
  }
}
