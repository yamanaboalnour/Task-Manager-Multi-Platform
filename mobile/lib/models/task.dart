class Task {
  const Task({
    required this.id,
    required this.title,
    required this.description,
    required this.isCompleted,
  });

  final int id;
  final String title;
  final String? description;
  final bool isCompleted;

  factory Task.fromJson(Map<String, dynamic> json) {
    return Task(
      id: json['id'] as int,
      title: json['title'] as String,
      description: json['description'] as String?,
      isCompleted: json['is_completed'] as bool? ?? false,
    );
  }
}
