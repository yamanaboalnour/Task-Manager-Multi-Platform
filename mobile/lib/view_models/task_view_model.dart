import 'package:flutter/foundation.dart';

import '../data/task_repository.dart';
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

  bool get isAuthenticated => _token != null;

  Future<void> initialize() async {
    try {
      apiBaseUrl = await _repository.readApiBaseUrl() ?? apiBaseUrl;
      _token = await _repository.readToken();
      if (_token != null) {
        user = await _repository.currentUser(_token!);
        await loadTasks();
      }
    } on ApiException catch (exception) {
      if (exception.statusCode == 401 || exception.statusCode == 419) {
        await _repository.clearToken();
        _token = null;
      }
      error = exception.message;
    } catch (exception) {
      error = exception.toString();
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
      notifyListeners();
      return false;
    } catch (exception) {
      error = exception.toString();
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
        throw const ApiException(
          'The server returned an invalid sign-in response.',
        );
      }
      await _repository.saveToken(token);
      _token = token;
      user = responseUser;
      await loadTasks();
      return error == null;
    } on ApiException catch (exception) {
      error = exception.message;
      return false;
    } catch (exception) {
      error = exception.toString();
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
      if (exception.statusCode == 401 || exception.statusCode == 419) {
        await _repository.clearToken();
        _token = null;
        user = null;
      }
    } catch (exception) {
      error = exception.toString();
    } finally {
      isLoading = false;
      notifyListeners();
    }
  }

  Future<bool> saveTask({
    int? id,
    required String title,
    required String description,
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
      return false;
    } catch (exception) {
      error = exception.toString();
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
    } catch (exception) {
      error = exception.toString();
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
    } catch (exception) {
      error = exception.toString();
    }
    notifyListeners();
  }

  Future<void> logout() async {
    final token = _token;
    try {
      if (token != null) await _repository.logout(token);
    } on ApiException catch (exception) {
      error = exception.message;
    } catch (exception) {
      error = exception.toString();
    } finally {
      try {
        await _repository.clearToken();
      } catch (exception) {
        error = exception.toString();
      }
      _token = null;
      user = null;
      tasks = const [];
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
}
