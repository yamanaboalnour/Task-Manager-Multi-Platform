import 'package:flutter/foundation.dart';

import '../data/task_repository.dart';
import '../l10n/generated/app_localizations_ar.dart';
import '../models/task.dart';

class TaskViewModel extends ChangeNotifier {
  TaskViewModel(this._repository);

  final TaskRepository _repository;
  String? _token;

  bool initialized = false;
  bool isLoading = false;
  bool isWorking = false;
  String apiBaseUrl = 'http://10.0.2.2:8000';
  String? error;
  Map<String, dynamic>? user;
  List<Task> tasks = const [];
  List<Map<String, dynamic>> users = const [];
  static final _strings = AppLocalizationsAr();

  bool get isAuthenticated => _token != null;
  bool get isManager => user?['role'] == 'manager';

  Future<void> initialize() async {
    try {
      apiBaseUrl = await _repository.readApiBaseUrl() ?? apiBaseUrl;
      _token = await _repository.readToken();
      if (_token != null) {
        user = await _repository.currentUser(_token!);
        await loadTasks();
        if (isManager) await loadUsers();
      }
    } on ApiException catch (exception) {
      await _clearExpiredSession(exception);
      error = exception.message;
    } catch (exception) {
      error = _strings.unexpectedError;
    } finally {
      initialized = true;
      notifyListeners();
    }
  }

  Future<bool> setApiBaseUrl(String value) async {
    try {
      await _repository.saveApiBaseUrl(value);
      apiBaseUrl = value.trim().replaceFirst(RegExp(r'/+$'), '');
      error = null;
      notifyListeners();
      return true;
    } on ApiException catch (exception) {
      error = exception.message;
      await _clearExpiredSession(exception);
      notifyListeners();
      return false;
    } catch (exception) {
      error = _strings.unexpectedError;
      notifyListeners();
      return false;
    }
  }

  Future<bool> authenticate({
    required String email,
    required String password,
    String? name,
    String? passwordConfirmation,
  }) async {
    isWorking = true;
    error = null;
    notifyListeners();
    try {
      final response = await _repository.authenticate(
        endpoint: name == null ? '/login' : '/register',
        body: {
          'name': ?name,
          'email': email,
          'password': password,
          'password_confirmation': ?passwordConfirmation,
          'device_name': 'task-manager-mobile',
        },
      );
      final token = response['token'];
      final responseUser = response['user'];
      if (token is! String || responseUser is! Map<String, dynamic>) {
        throw ApiException(_strings.invalidSignInResponse);
      }
      await _repository.saveToken(token);
      _token = token;
      user = responseUser;
      await loadTasks();
      if (isManager) await loadUsers();
      return error == null;
    } on ApiException catch (exception) {
      error = exception.message;
      await _clearExpiredSession(exception);
      return false;
    } catch (exception) {
      error = _strings.unexpectedError;
      return false;
    } finally {
      isWorking = false;
      notifyListeners();
    }
  }

  Future<void> loadTasks() async {
    final token = _token;
    if (token == null) return;
    isLoading = true;
    error = null;
    notifyListeners();
    try {
      tasks = await _repository.getTasks(token);
    } on ApiException catch (exception) {
      error = exception.message;
      await _clearExpiredSession(exception);
    } catch (exception) {
      error = _strings.unexpectedError;
    } finally {
      isLoading = false;
      notifyListeners();
    }
  }

  Future<void> loadUsers() async {
    final token = _token;
    if (token == null || !isManager) return;
    try {
      users = await _repository.getUsers(token);
      error = null;
    } on ApiException catch (exception) {
      error = exception.message;
      await _clearExpiredSession(exception);
    } catch (exception) {
      error = _strings.unexpectedError;
    }
    notifyListeners();
  }

  Future<bool> saveTask({
    int? id,
    required String title,
    required String description,
    int? userId,
  }) async {
    final token = _token;
    if (token == null) return false;
    isWorking = true;
    error = null;
    notifyListeners();
    try {
      final task = id == null
          ? await _repository.createTask(
              token,
              title: title,
              description: description,
              userId: userId,
            )
          : await _repository.updateTask(
              token,
              id,
              title: title,
              description: description,
            );
      _replaceTask(task);
      return true;
    } on ApiException catch (exception) {
      error = exception.message;
      await _clearExpiredSession(exception);
      return false;
    } catch (exception) {
      error = _strings.unexpectedError;
      return false;
    } finally {
      isWorking = false;
      notifyListeners();
    }
  }

  Future<bool> saveUser({
    int? id,
    required String name,
    required String email,
    required String role,
    String? password,
  }) async {
    final token = _token;
    if (token == null || !isManager) return false;
    isWorking = true;
    error = null;
    notifyListeners();
    try {
      if (id == null) {
        await _repository.createUser(
          token,
          name: name,
          email: email,
          password: password ?? '',
          role: role,
        );
      } else {
        await _repository.updateUser(
          token,
          id,
          name: name,
          email: email,
          password: password,
          role: role,
        );
      }
      await loadUsers();
      return error == null;
    } on ApiException catch (exception) {
      error = exception.message;
      await _clearExpiredSession(exception);
      return false;
    } catch (exception) {
      error = _strings.unexpectedError;
      return false;
    } finally {
      isWorking = false;
      notifyListeners();
    }
  }

  Future<void> toggleTask(Task task) async {
    final token = _token;
    if (token == null) return;
    try {
      _replaceTask(await _repository.toggleTask(token, task.id));
      error = null;
    } on ApiException catch (exception) {
      error = exception.message;
      await _clearExpiredSession(exception);
    } catch (exception) {
      error = _strings.unexpectedError;
    }
    notifyListeners();
  }

  Future<void> deleteTask(int id) async {
    final token = _token;
    if (token == null) return;
    try {
      await _repository.deleteTask(token, id);
      tasks = tasks.where((task) => task.id != id).toList();
      error = null;
    } on ApiException catch (exception) {
      error = exception.message;
      await _clearExpiredSession(exception);
    } catch (exception) {
      error = _strings.unexpectedError;
    }
    notifyListeners();
  }

  Future<void> logout() async {
    final token = _token;
    try {
      if (token != null) await _repository.logout(token);
    } on ApiException catch (exception) {
      error = exception.message;
      await _clearExpiredSession(exception);
    } catch (exception) {
      error = _strings.unexpectedError;
    } finally {
      try {
        await _repository.clearToken();
      } catch (exception) {
        error = _strings.localSessionClearFailed;
      }
      _token = null;
      user = null;
      tasks = const [];
      users = const [];
      notifyListeners();
    }
  }

  void clearError() {
    error = null;
    notifyListeners();
  }

  void _replaceTask(Task updated) {
    final index = tasks.indexWhere((task) => task.id == updated.id);
    if (index < 0) {
      tasks = [updated, ...tasks];
    } else {
      final copy = [...tasks];
      copy[index] = updated;
      tasks = copy;
    }
  }

  Future<void> _clearExpiredSession(ApiException exception) async {
    if (exception.statusCode != 401 && exception.statusCode != 419) return;
    await _repository.clearToken();
    _token = null;
    user = null;
    tasks = const [];
    users = const [];
  }
}
