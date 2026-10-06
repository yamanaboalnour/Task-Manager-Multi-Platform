import 'dart:convert';

import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';
import 'package:task_manager_mobile/data/task_repository.dart';

void main() {
  group('HttpTaskRepository', () {
    test(
      'loads tasks from the versioned API with a Sanctum bearer token',
      () async {
        final repository = HttpTaskRepository(
          client: MockClient((request) async {
            expect(request.method, 'GET');
            expect(request.url, Uri.parse('http://10.0.2.2:8000/api/v1/tasks'));
            expect(request.headers['authorization'], 'Bearer test-token');
            expect(request.headers['accept'], 'application/json');

            return http.Response(
              jsonEncode([
                {
                  'id': 7,
                  'title': 'Verify mobile API',
                  'description': null,
                  'is_completed': false,
                },
              ]),
              200,
              headers: {'content-type': 'application/json'},
            );
          }),
        );

        final tasks = await repository.getTasks('test-token');

        expect(tasks, hasLength(1));
        expect(tasks.single.id, 7);
        expect(tasks.single.title, 'Verify mobile API');
      },
    );

    test(
      'surfaces Laravel validation messages from failed API responses',
      () async {
        final repository = HttpTaskRepository(
          client: MockClient((_) async {
            return http.Response(
              jsonEncode({
                'message': 'The title field is required.',
                'errors': {
                  'title': ['The title field is required.'],
                },
              }),
              422,
              headers: {'content-type': 'application/json'},
            );
          }),
        );

        await expectLater(
          repository.createTask('test-token', title: '', description: ''),
          throwsA(
            isA<ApiException>()
                .having((exception) => exception.statusCode, 'statusCode', 422)
                .having(
                  (exception) => exception.message,
                  'message',
                  'The title field is required.',
                ),
          ),
        );
      },
    );

    test(
      'manager user endpoints pass account data to the Laravel API',
      () async {
        var userListRequested = false;
        var createUserRequested = false;
        String? listedPath;
        String? createPath;
        String? createAuthorization;
        Map<String, dynamic>? createBody;
        final repository = HttpTaskRepository(
          client: MockClient((request) async {
            if (request.method == 'GET') {
              userListRequested = true;
              listedPath = request.url.path;
              return http.Response(
                jsonEncode([
                  {
                    'id': 4,
                    'name': 'Worker',
                    'email': 'worker@example.test',
                    'role': 'worker',
                  },
                ]),
                200,
              );
            }

            createUserRequested = true;
            createPath = request.url.path;
            createAuthorization = request.headers['authorization'];
            createBody = jsonDecode(request.body) as Map<String, dynamic>;
            return http.Response(
              jsonEncode({
                'id': 5,
                'name': 'New Worker',
                'email': 'new-worker@example.test',
                'role': 'worker',
              }),
              201,
            );
          }),
        );

        final users = await repository.getUsers('manager-token');
        final created = await repository.createUser(
          'manager-token',
          name: 'New Worker',
          email: 'new-worker@example.test',
          password: 'password123',
          role: 'worker',
        );

        expect(userListRequested, isTrue);
        expect(createUserRequested, isTrue);
        expect(listedPath, '/api/v1/users');
        expect(createPath, '/api/v1/users');
        expect(createAuthorization, startsWith('Bearer '));
        expect(createBody?['email'], 'new-worker@example.test');
        expect(createBody?['role'], 'worker');
        expect(createBody?['password_confirmation'], createBody?['password']);
        expect(users.single['role'], 'worker');
        expect(created['id'], 5);
      },
    );
  });
}
