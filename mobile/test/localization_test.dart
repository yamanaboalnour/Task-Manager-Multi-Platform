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

  testWidgets('manager sees all tasks and can open account management', (
    tester,
  ) async {
    final repository = _FakeTaskRepository(
      token: 'manager-token',
      authenticatedUser: {'id': 1, 'name': 'Manager', 'role': 'manager'},
    );
    final viewModel = TaskViewModel(repository);
    await tester.pumpWidget(TaskManagerApp(viewModel: viewModel));
    await tester.pumpAndSettle();

    expect(find.text('جميع المهام'), findsOneWidget);
    expect(find.text('مدير'), findsOneWidget);
    expect(find.byTooltip('إدارة المستخدمين'), findsOneWidget);

    await tester.tap(find.byTooltip('إدارة المستخدمين'));
    await tester.pumpAndSettle();
    expect(find.text('إدارة المستخدمين'), findsOneWidget);
  });

  testWidgets('worker has no user-management navigation', (tester) async {
    final viewModel = TaskViewModel(
      _FakeTaskRepository(
        token: 'worker-token',
        authenticatedUser: {'id': 2, 'name': 'Worker', 'role': 'worker'},
      ),
    );
    await tester.pumpWidget(TaskManagerApp(viewModel: viewModel));
    await tester.pumpAndSettle();

    expect(find.text('مهامي'), findsOneWidget);
    expect(find.byTooltip('إدارة المستخدمين'), findsNothing);
  });

  testWidgets(
    'expired API token clears the local session and returns to login',
    (tester) async {
      final repository = _FakeTaskRepository(
        token: 'expired-token',
        authenticatedUser: {'id': 2, 'name': 'Worker', 'role': 'worker'},
        taskStatusCode: 401,
      );
      final viewModel = TaskViewModel(repository);
      await tester.pumpWidget(TaskManagerApp(viewModel: viewModel));
      await tester.pumpAndSettle();

      expect(repository.tokenCleared, isTrue);
      expect(viewModel.isAuthenticated, isFalse);
      expect(find.text('مرحبًا بعودتك'), findsOneWidget);
    },
  );
}

class _FakeTaskRepository implements TaskRepository {
  _FakeTaskRepository({
    this.token,
    this.authenticatedUser = const <String, dynamic>{},
    this.taskStatusCode,
  });

  final String? token;
  final Map<String, dynamic> authenticatedUser;
  final int? taskStatusCode;
  bool tokenCleared = false;

  @override
  Future<void> clearToken() async {
    tokenCleared = true;
  }

  @override
  Future<Map<String, dynamic>> currentUser(String token) async =>
      authenticatedUser;

  @override
  Future<List<Task>> getTasks(String token) async {
    if (taskStatusCode != null) {
      throw ApiException('session expired', statusCode: taskStatusCode);
    }
    return <Task>[];
  }

  @override
  Future<List<Map<String, dynamic>>> getUsers(String token) async => [];

  @override
  Future<Map<String, dynamic>> createUser(
    String token, {
    required String name,
    required String email,
    required String password,
    required String role,
  }) async => <String, dynamic>{};

  @override
  Future<Map<String, dynamic>> updateUser(
    String token,
    int id, {
    required String name,
    required String email,
    required String? password,
    required String role,
  }) async => <String, dynamic>{};

  @override
  Future<String?> readApiBaseUrl() async => 'http://10.0.2.2:8000';

  @override
  Future<String?> readToken() async => token;

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
    int? userId,
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
