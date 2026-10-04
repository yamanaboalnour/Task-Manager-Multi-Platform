import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:task_manager_mobile/data/task_repository.dart';
import 'package:task_manager_mobile/main.dart';
import 'package:task_manager_mobile/models/task.dart';
import 'package:task_manager_mobile/view_models/task_view_model.dart';

void main() {
  testWidgets('Arabic is the default locale and the app uses RTL', (
    tester,
  ) async {
    final viewModel = TaskViewModel(_FakeTaskRepository());
    await tester.pumpWidget(TaskManagerApp(viewModel: viewModel));
    await tester.pumpAndSettle();

    expect(
      tester.widget<MaterialApp>(find.byType(MaterialApp)).locale,
      const Locale('ar'),
    );
    expect(find.text('مرحبًا بعودتك'), findsOneWidget);
    final direction = tester.widget<Directionality>(
      find
          .ancestor(
            of: find.text('مرحبًا بعودتك'),
            matching: find.byType(Directionality),
          )
          .first,
    );
    expect(direction.textDirection, TextDirection.rtl);

    final apiUrlField = tester.widget<TextField>(
      find.descendant(
        of: find.byType(TextFormField).first,
        matching: find.byType(TextField),
      ),
    );
    expect(apiUrlField.textDirection, TextDirection.ltr);
  });

  testWidgets('registration validation messages are localized', (tester) async {
    final viewModel = TaskViewModel(_FakeTaskRepository());
    await tester.pumpWidget(TaskManagerApp(viewModel: viewModel));
    await tester.pumpAndSettle();

    await tester.tap(find.text('مستخدم جديد؟ أنشئ حسابًا'));
    await tester.pumpAndSettle();
    final createAccountButton = find.text('إنشاء حساب').last;
    await tester.ensureVisible(createAccountButton);
    await tester.pumpAndSettle();
    await tester.tap(createAccountButton);
    await tester.pumpAndSettle();

    expect(find.text('الاسم مطلوب.'), findsOneWidget);
  });
}

class _FakeTaskRepository implements TaskRepository {
  @override
  Future<void> clearToken() async {}

  @override
  Future<Map<String, dynamic>> currentUser(String token) async =>
      <String, dynamic>{};

  @override
  Future<List<Task>> getTasks(String token) async => <Task>[];

  @override
  Future<String?> readApiBaseUrl() async => 'http://10.0.2.2:8000';

  @override
  Future<String?> readToken() async => null;

  @override
  Future<void> saveApiBaseUrl(String value) async {}

  @override
  Future<void> saveToken(String token) async {}

  @override
  Future<Map<String, dynamic>> authenticate({
    required String endpoint,
    required Map<String, String> body,
  }) async => <String, dynamic>{};

  @override
  Future<Task> createTask(
    String token, {
    required String title,
    required String description,
  }) async => throw UnimplementedError();

  @override
  Future<void> deleteTask(String token, int id) async {}

  @override
  Future<void> logout(String token) async {}

  @override
  Future<Task> toggleTask(String token, int id) async =>
      throw UnimplementedError();

  @override
  Future<Task> updateTask(
    String token,
    int id, {
    required String title,
    required String description,
  }) async => throw UnimplementedError();
}
