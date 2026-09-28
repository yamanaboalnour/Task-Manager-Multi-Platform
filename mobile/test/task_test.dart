import 'package:flutter_test/flutter_test.dart';
import 'package:task_manager_mobile/models/task.dart';

void main() {
  group('Task.fromJson', () {
    test('reads task fields from the Laravel API response', () {
      final task = Task.fromJson({
        'id': 12,
        'title': 'Review the API',
        'description': 'Check mobile contract',
        'is_completed': true,
      });

      expect(task.id, 12);
      expect(task.title, 'Review the API');
      expect(task.description, 'Check mobile contract');
      expect(task.isCompleted, isTrue);
    });

    test('supports nullable descriptions and missing completion values', () {
      final task = Task.fromJson({'id': 13, 'title': 'New task'});

      expect(task.description, isNull);
      expect(task.isCompleted, isFalse);
    });
  });
}
