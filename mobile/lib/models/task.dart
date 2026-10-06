class Task {
  const Task({
    required this.id,
    required this.title,
    required this.description,
    required this.isCompleted,
    this.userId,
    this.userName,
  });

  final int id;
  final String title;
  final String? description;
  final bool isCompleted;
  final int? userId;
  final String? userName;

  factory Task.fromJson(Map<String, dynamic> json) {
    return Task(
      id: json['id'] as int,
      title: json['title'] as String,
      description: json['description'] as String?,
      isCompleted: json['is_completed'] as bool? ?? false,
      userId: int.tryParse('${json['user_id'] ?? ''}'),
      userName: (json['user'] as Map<String, dynamic>?)?['name'] as String?,
    );
  }
}
