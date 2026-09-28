import 'package:flutter/material.dart';

import 'data/task_repository.dart';
import 'models/task.dart';
import 'view_models/task_view_model.dart';

void main() {
  WidgetsFlutterBinding.ensureInitialized();
  runApp(TaskManagerApp(viewModel: TaskViewModel(HttpTaskRepository())));
}

class TaskManagerApp extends StatefulWidget {
  const TaskManagerApp({required this.viewModel, super.key});

  final TaskViewModel viewModel;

  @override
  State<TaskManagerApp> createState() => _TaskManagerAppState();
}

class _TaskManagerAppState extends State<TaskManagerApp> {
  @override
  void initState() {
    super.initState();
    widget.viewModel.initialize();
  }

  @override
  void dispose() {
    widget.viewModel.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Task Manager',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        colorScheme: ColorScheme.fromSeed(
          seedColor: const Color(0xff315c4c),
          surface: const Color(0xfff6f7f4),
        ),
        scaffoldBackgroundColor: const Color(0xfff6f7f4),
        useMaterial3: true,
        inputDecorationTheme: const InputDecorationTheme(
          border: OutlineInputBorder(),
        ),
      ),
      home: ListenableBuilder(
        listenable: widget.viewModel,
        builder: (context, _) {
          if (!widget.viewModel.initialized) {
            return const Scaffold(
              body: Center(child: CircularProgressIndicator()),
            );
          }
          return widget.viewModel.isAuthenticated
              ? TaskListScreen(viewModel: widget.viewModel)
              : SignInScreen(viewModel: widget.viewModel);
        },
      ),
    );
  }
}

class SignInScreen extends StatefulWidget {
  const SignInScreen({required this.viewModel, super.key});

  final TaskViewModel viewModel;

  @override
  State<SignInScreen> createState() => _SignInScreenState();
}

class _SignInScreenState extends State<SignInScreen> {
  final _formKey = GlobalKey<FormState>();
  late final _baseUrl = TextEditingController(
    text: widget.viewModel.apiBaseUrl,
  );
  final _name = TextEditingController();
  final _email = TextEditingController();
  final _password = TextEditingController();
  final _confirmation = TextEditingController();
  bool _registering = false;

  @override
  void dispose() {
    _baseUrl.dispose();
    _name.dispose();
    _email.dispose();
    _password.dispose();
    _confirmation.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    final saved = await widget.viewModel.setApiBaseUrl(_baseUrl.text);
    if (!saved || !mounted) return;
    await widget.viewModel.authenticate(
      email: _email.text.trim(),
      password: _password.text,
      name: _registering ? _name.text.trim() : null,
      passwordConfirmation: _registering ? _confirmation.text : null,
    );
  }

  @override
  Widget build(BuildContext context) {
    final vm = widget.viewModel;
    return Scaffold(
      body: SafeArea(
        child: Center(
          child: SingleChildScrollView(
            padding: const EdgeInsets.all(24),
            child: ConstrainedBox(
              constraints: const BoxConstraints(maxWidth: 440),
              child: Form(
                key: _formKey,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    const Icon(
                      Icons.task_alt_rounded,
                      size: 56,
                      color: Color(0xff315c4c),
                    ),
                    const SizedBox(height: 12),
                    Text(
                      _registering ? 'Create your account' : 'Welcome back',
                      textAlign: TextAlign.center,
                      style: Theme.of(context).textTheme.headlineMedium
                          ?.copyWith(fontWeight: FontWeight.w700),
                    ),
                    const SizedBox(height: 8),
                    Text(
                      _registering
                          ? 'Sign up to keep your tasks in sync.'
                          : 'Sign in to pick up where you left off.',
                      textAlign: TextAlign.center,
                      style: Theme.of(context).textTheme.bodyLarge,
                    ),
                    const SizedBox(height: 28),
                    TextFormField(
                      controller: _baseUrl,
                      keyboardType: TextInputType.url,
                      autocorrect: false,
                      decoration: const InputDecoration(
                        labelText: 'API base URL',
                        hintText: 'https://your-server.example',
                        prefixIcon: Icon(Icons.link),
                        helperText:
                            'Android emulator default: http://10.0.2.2:8000',
                      ),
                      validator: (value) {
                        final uri = Uri.tryParse(value?.trim() ?? '');
                        if (uri == null ||
                            !uri.hasAuthority ||
                            !const {'http', 'https'}.contains(uri.scheme) ||
                            uri.host.isEmpty) {
                          return 'Enter a valid http:// or https:// URL';
                        }
                        return null;
                      },
                    ),
                    const SizedBox(height: 16),
                    if (_registering) ...[
                      TextFormField(
                        controller: _name,
                        textCapitalization: TextCapitalization.words,
                        decoration: const InputDecoration(
                          labelText: 'Name',
                          prefixIcon: Icon(Icons.person_outline),
                        ),
                        validator: (value) =>
                            value == null || value.trim().isEmpty
                            ? 'Name is required'
                            : null,
                      ),
                      const SizedBox(height: 16),
                    ],
                    TextFormField(
                      controller: _email,
                      keyboardType: TextInputType.emailAddress,
                      autocorrect: false,
                      decoration: const InputDecoration(
                        labelText: 'Email',
                        prefixIcon: Icon(Icons.mail_outline),
                      ),
                      validator: (value) {
                        final email = value?.trim() ?? '';
                        if (email.isEmpty || !email.contains('@')) {
                          return 'Enter a valid email address';
                        }
                        return null;
                      },
                    ),
                    const SizedBox(height: 16),
                    TextFormField(
                      controller: _password,
                      obscureText: true,
                      decoration: const InputDecoration(
                        labelText: 'Password',
                        prefixIcon: Icon(Icons.lock_outline),
                      ),
                      validator: (value) {
                        if (value == null || value.isEmpty) {
                          return 'Password is required';
                        }
                        if (_registering && value.length < 8) {
                          return 'Use at least 8 characters';
                        }
                        return null;
                      },
                    ),
                    if (_registering) ...[
                      const SizedBox(height: 16),
                      TextFormField(
                        controller: _confirmation,
                        obscureText: true,
                        decoration: const InputDecoration(
                          labelText: 'Confirm password',
                          prefixIcon: Icon(Icons.lock_reset_outlined),
                        ),
                        validator: (value) => value != _password.text
                            ? 'Passwords do not match'
                            : null,
                      ),
                    ],
                    if (vm.error != null) ...[
                      const SizedBox(height: 16),
                      _ErrorMessage(message: vm.error!),
                    ],
                    const SizedBox(height: 22),
                    FilledButton(
                      onPressed: vm.isWorking ? null : _submit,
                      child: Padding(
                        padding: const EdgeInsets.symmetric(vertical: 12),
                        child: vm.isWorking
                            ? const SizedBox.square(
                                dimension: 22,
                                child: CircularProgressIndicator(
                                  strokeWidth: 2,
                                ),
                              )
                            : Text(_registering ? 'Create account' : 'Sign in'),
                      ),
                    ),
                    TextButton(
                      onPressed: vm.isWorking
                          ? null
                          : () {
                              setState(() => _registering = !_registering);
                              vm.clearError();
                            },
                      child: Text(
                        _registering
                            ? 'Already have an account? Sign in'
                            : 'New here? Create an account',
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}

class TaskListScreen extends StatelessWidget {
  const TaskListScreen({required this.viewModel, super.key});

  final TaskViewModel viewModel;

  Future<void> _openTaskEditor(BuildContext context, [Task? task]) async {
    await showDialog<void>(
      context: context,
      builder: (_) => TaskEditorDialog(viewModel: viewModel, task: task),
    );
  }

  Future<void> _confirmDelete(BuildContext context, Task task) async {
    final shouldDelete = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Delete task?'),
        content: Text('“${task.title}” will be permanently deleted.'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Cancel'),
          ),
          FilledButton.tonal(
            onPressed: () => Navigator.pop(context, true),
            child: const Text('Delete'),
          ),
        ],
      ),
    );
    if (shouldDelete == true) await viewModel.deleteTask(task.id);
  }

  Future<void> _logout(BuildContext context) async {
    final shouldLogout = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Sign out?'),
        content: const Text('Your tasks will stay safely on your account.'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Cancel'),
          ),
          FilledButton(
            onPressed: () => Navigator.pop(context, true),
            child: const Text('Sign out'),
          ),
        ],
      ),
    );
    if (shouldLogout == true) await viewModel.logout();
  }

  @override
  Widget build(BuildContext context) {
    return ListenableBuilder(
      listenable: viewModel,
      builder: (context, _) {
        final vm = viewModel;
        return Scaffold(
          appBar: AppBar(
            title: const Text(
              'My tasks',
              style: TextStyle(fontWeight: FontWeight.w700),
            ),
            actions: [
              IconButton(
                tooltip: 'Sign out',
                onPressed: () => _logout(context),
                icon: const Icon(Icons.logout),
              ),
            ],
          ),
          floatingActionButton: FloatingActionButton.extended(
            onPressed: () => _openTaskEditor(context),
            icon: const Icon(Icons.add),
            label: const Text('New task'),
          ),
          body: Column(
            children: [
              if (vm.user?['name'] is String)
                Padding(
                  padding: const EdgeInsets.fromLTRB(20, 0, 20, 12),
                  child: Align(
                    alignment: Alignment.centerLeft,
                    child: Text(
                      'Hello, ${vm.user!['name']}',
                      style: Theme.of(context).textTheme.titleMedium,
                    ),
                  ),
                ),
              if (vm.error != null)
                Padding(
                  padding: const EdgeInsets.fromLTRB(16, 0, 16, 12),
                  child: _ErrorMessage(
                    message: vm.error!,
                    onRetry: vm.isLoading ? null : vm.loadTasks,
                  ),
                ),
              Expanded(
                child: vm.isLoading && vm.tasks.isEmpty
                    ? const Center(child: CircularProgressIndicator())
                    : RefreshIndicator(
                        onRefresh: vm.loadTasks,
                        child: vm.tasks.isEmpty
                            ? ListView(
                                physics: const AlwaysScrollableScrollPhysics(),
                                children: [
                                  SizedBox(
                                    height:
                                        MediaQuery.sizeOf(context).height * .5,
                                    child: const _EmptyTasks(),
                                  ),
                                ],
                              )
                            : ListView.separated(
                                physics: const AlwaysScrollableScrollPhysics(),
                                padding: const EdgeInsets.fromLTRB(
                                  16,
                                  4,
                                  16,
                                  100,
                                ),
                                itemCount: vm.tasks.length,
                                separatorBuilder: (_, _) =>
                                    const SizedBox(height: 10),
                                itemBuilder: (context, index) {
                                  final task = vm.tasks[index];
                                  return _TaskCard(
                                    task: task,
                                    onToggle: () => vm.toggleTask(task),
                                    onEdit: () =>
                                        _openTaskEditor(context, task),
                                    onDelete: () =>
                                        _confirmDelete(context, task),
                                  );
                                },
                              ),
                      ),
              ),
            ],
          ),
        );
      },
    );
  }
}

class _TaskCard extends StatelessWidget {
  const _TaskCard({
    required this.task,
    required this.onToggle,
    required this.onEdit,
    required this.onDelete,
  });

  final Task task;
  final VoidCallback onToggle;
  final VoidCallback onEdit;
  final VoidCallback onDelete;

  @override
  Widget build(BuildContext context) {
    final titleStyle = Theme.of(context).textTheme.titleMedium;
    return Card(
      margin: EdgeInsets.zero,
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 8),
        child: Row(
          children: [
            Checkbox(value: task.isCompleted, onChanged: (_) => onToggle()),
            Expanded(
              child: GestureDetector(
                onTap: onEdit,
                child: Padding(
                  padding: const EdgeInsets.symmetric(vertical: 8),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        task.title,
                        style: titleStyle?.copyWith(
                          decoration: task.isCompleted
                              ? TextDecoration.lineThrough
                              : null,
                          color: task.isCompleted
                              ? Theme.of(context).colorScheme.onSurfaceVariant
                              : null,
                        ),
                      ),
                      if (task.description?.isNotEmpty == true) ...[
                        const SizedBox(height: 4),
                        Text(
                          task.description!,
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                          style: Theme.of(context).textTheme.bodyMedium,
                        ),
                      ],
                    ],
                  ),
                ),
              ),
            ),
            PopupMenuButton<String>(
              tooltip: 'Task actions',
              onSelected: (value) => value == 'edit' ? onEdit() : onDelete(),
              itemBuilder: (context) => const [
                PopupMenuItem(value: 'edit', child: Text('Edit')),
                PopupMenuItem(value: 'delete', child: Text('Delete')),
              ],
            ),
          ],
        ),
      ),
    );
  }
}

class TaskEditorDialog extends StatefulWidget {
  const TaskEditorDialog({required this.viewModel, this.task, super.key});

  final TaskViewModel viewModel;
  final Task? task;

  @override
  State<TaskEditorDialog> createState() => _TaskEditorDialogState();
}

class _TaskEditorDialogState extends State<TaskEditorDialog> {
  final _formKey = GlobalKey<FormState>();
  late final _title = TextEditingController(text: widget.task?.title ?? '');
  late final _description = TextEditingController(
    text: widget.task?.description ?? '',
  );
  bool _saving = false;
  String? _error;

  @override
  void dispose() {
    _title.dispose();
    _description.dispose();
    super.dispose();
  }

  Future<void> _save() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() {
      _saving = true;
      _error = null;
    });
    final succeeded = await widget.viewModel.saveTask(
      id: widget.task?.id,
      title: _title.text.trim(),
      description: _description.text.trim(),
    );
    if (!mounted) return;
    if (succeeded) {
      Navigator.pop(context);
    } else {
      setState(() {
        _saving = false;
        _error = widget.viewModel.error ?? 'The task could not be saved.';
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return AlertDialog(
      title: Text(widget.task == null ? 'New task' : 'Edit task'),
      content: SizedBox(
        width: 420,
        child: Form(
          key: _formKey,
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              TextFormField(
                controller: _title,
                autofocus: true,
                maxLength: 255,
                textCapitalization: TextCapitalization.sentences,
                decoration: const InputDecoration(labelText: 'Title'),
                validator: (value) => value == null || value.trim().isEmpty
                    ? 'Title is required'
                    : null,
              ),
              const SizedBox(height: 12),
              TextFormField(
                controller: _description,
                minLines: 2,
                maxLines: 4,
                maxLength: 5000,
                textCapitalization: TextCapitalization.sentences,
                decoration: const InputDecoration(
                  labelText: 'Description (optional)',
                  alignLabelWithHint: true,
                ),
              ),
              if (_error != null) ...[
                const SizedBox(height: 8),
                _ErrorMessage(message: _error!),
              ],
            ],
          ),
        ),
      ),
      actions: [
        TextButton(
          onPressed: _saving ? null : () => Navigator.pop(context),
          child: const Text('Cancel'),
        ),
        FilledButton(
          onPressed: _saving ? null : _save,
          child: _saving
              ? const SizedBox.square(
                  dimension: 18,
                  child: CircularProgressIndicator(strokeWidth: 2),
                )
              : const Text('Save'),
        ),
      ],
    );
  }
}

class _EmptyTasks extends StatelessWidget {
  const _EmptyTasks();

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(
              Icons.checklist_rounded,
              size: 56,
              color: Theme.of(context).colorScheme.primary,
            ),
            const SizedBox(height: 12),
            Text(
              'You’re all caught up',
              style: Theme.of(context).textTheme.titleLarge,
            ),
            const SizedBox(height: 4),
            const Text('Add a task whenever something comes to mind.'),
          ],
        ),
      ),
    );
  }
}

class _ErrorMessage extends StatelessWidget {
  const _ErrorMessage({required this.message, this.onRetry});

  final String message;
  final VoidCallback? onRetry;

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Theme.of(context).colorScheme.errorContainer,
        borderRadius: BorderRadius.circular(12),
      ),
      child: Row(
        children: [
          Icon(
            Icons.error_outline,
            color: Theme.of(context).colorScheme.onErrorContainer,
          ),
          const SizedBox(width: 10),
          Expanded(
            child: Text(
              message,
              style: TextStyle(
                color: Theme.of(context).colorScheme.onErrorContainer,
              ),
            ),
          ),
          if (onRetry != null)
            TextButton(onPressed: onRetry, child: const Text('Retry')),
        ],
      ),
    );
  }
}
