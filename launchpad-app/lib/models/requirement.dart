class Requirement {
  final int requirementId;
  final String requirementType;
  final String fileName;
  final String filePath;
  final int fileSize;
  final double fileSizeMb;
  final String? description;
  final DateTime submittedAt;

  Requirement({
    required this.requirementId,
    required this.requirementType,
    required this.fileName,
    required this.filePath,
    required this.fileSize,
    required this.fileSizeMb,
    this.description,
    required this.submittedAt,
  });

  factory Requirement.fromJson(Map<String, dynamic> json) {
    return Requirement(
      requirementId: json['requirement_id'] as int,
      requirementType: json['requirement_type'] as String,
      fileName: json['file_name'] as String,
      filePath: json['file_path'] as String,
      fileSize: json['file_size'] as int,
      fileSizeMb: (json['file_size_mb'] as num).toDouble(),
      description: json['description'] as String?,
      submittedAt: DateTime.parse(json['submitted_at'] as String),
    );
  }

  String get typeDisplayName {
    switch (requirementType) {
      case 'pre_deployment':
        return 'Pre-Deployment';
      case 'deployment':
        return 'Deployment';
      case 'final_requirements':
        return 'Final Requirements';
      default:
        return requirementType;
    }
  }

  String get formattedFileSize {
    if (fileSizeMb < 1) {
      return '${(fileSize / 1024).toStringAsFixed(0)} KB';
    }
    return '${fileSizeMb.toStringAsFixed(2)} MB';
  }

  String get fileExtension {
    return fileName.split('.').last.toUpperCase();
  }
}

class RequirementsData {
  final List<Requirement> allRequirements;
  final Map<String, List<Requirement>> groupedByType;
  final int totalCount;

  RequirementsData({
    required this.allRequirements,
    required this.groupedByType,
    required this.totalCount,
  });

  factory RequirementsData.fromJson(Map<String, dynamic> json) {
    final all = (json['all_requirements'] as List)
        .map((e) => Requirement.fromJson(e as Map<String, dynamic>))
        .toList();

    final grouped = (json['grouped_by_type'] as Map<String, dynamic>).map(
      (key, value) => MapEntry(
        key,
        (value as List)
            .map((e) => Requirement.fromJson(e as Map<String, dynamic>))
            .toList(),
      ),
    );

    return RequirementsData(
      allRequirements: all,
      groupedByType: grouped,
      totalCount: json['total_count'] as int,
    );
  }

  int getCountForType(String type) {
    return groupedByType[type]?.length ?? 0;
  }
}
