import 'package:flutter/material.dart';
import 'package:flutter_localizations/flutter_localizations.dart';

import 'data/task_repository.dart';
import 'l10n/generated/app_localizations.dart';
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
      onGenerateTitle: (context) => AppLocalizations.of(context)!.appTitle,
      locale: const Locale('ar'),
      supportedLocales: AppLocalizations.supportedLocales,
      localizationsDelegates: const [
        AppLocalizations.delegate,
        GlobalMaterialLocalizations.delegate,
        GlobalWidgetsLocalizations.delegate,
        GlobalCupertinoLocalizations.delegate,
      ],
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
            return Scaffold(
              body: Center(
                child: Semantics(
                  label: AppLocalizations.of(context)!.loading,
                  child: const CircularProgressIndicator(),
                ),
              ),
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
    final strings = AppLocalizations.of(context)!;
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
                      _registering ? strings.registerTitle : strings.welcomeBack,
                      textAlign: TextAlign.center,
                      style: Theme.of(context).textTheme.headlineMedium
                          ?.copyWith(fontWeight: FontWeight.w700),
                    ),
                    const SizedBox(height: 8),
                    Text(
                      _registering
                          ? strings.registerSubtitle
                          : strings.loginSubtitle,
                      textAlign: TextAlign.center,
                      style: Theme.of(context).textTheme.bodyLarge,
                    ),
                    const SizedBox(height: 28),
                    TextFormField(
                      controller: _baseUrl,
                      keyboardType: TextInputType.url,
                      autocorrect: false,
                      decoration: InputDecoration(
                        labelText: strings.apiBaseUrl,
                        hintText: strings.apiBaseUrlHint,
                        prefixIcon: Icon(Icons.link),
                        helperText: strings.apiBaseUrlHelp,
                      ),
                      textDirection: TextDirection.ltr,
                      textAlign: TextAlign.left,
                      validator: (value) {
                        final uri = Uri.tryParse(value?.trim() ?? '');
                        if (uri == null ||
                            !uri.hasAuthority ||
                            !const {'http', 'https'}.contains(uri.scheme) ||
                            uri.host.isEmpty) {
                          return strings.invalidApiUrl;
                        }
                        return null;
                      },
                    ),
                    const SizedBox(height: 16),
                    if (_registering) ...[
                      TextFormField(
                        controller: _name,
                        textCapitalization: TextCapitalization.words,
                        decoration: InputDecoration(
                          labelText: strings.name,
                          prefixIcon: Icon(Icons.person_outline),
                        ),
                        validator: (value) =>
                            value == null || value.trim().isEmpty
                            ? strings.nameRequired
                            : null,
                      ),
                      const SizedBox(height: 16),
                    ],
                    TextFormField(
                      controller: _email,
                      keyboardType: TextInputType.emailAddress,
                      autocorrect: false,
                      decoration: InputDecoration(
                        labelText: strings.email,
                        prefixIcon: Icon(Icons.mail_outline),
                      ),
                      textDirection: TextDirection.ltr,
                      textAlign: TextAlign.left,
                      validator: (value) {
                        final email = value?.trim() ?? '';
                        if (email.isEmpty || !email.contains('@')) {
                          return strings.emailRequired;
                        }
                        return null;
                      },
                    ),
                    const SizedBox(height: 16),
                    TextFormField(
                      controller: _password,
                      obscureText: true,
                      decoration: InputDecoration(
                        labelText: strings.password,
                        prefixIcon: Icon(Icons.lock_outline),
                      ),
                      textDirection: TextDirection.ltr,
                      textAlign: TextAlign.left,
                      validator: (value) {
                        if (value == null || value.isEmpty) {
                          return strings.passwordRequired;
                        }
                        if (_registering && value.length < 8) {
                          return strings.passwordMinLength;
                        }
                        return null;
                      },
                    ),
                    if (_registering) ...[
                      const SizedBox(height: 16),
                      TextFormField(
                        controller: _confirmation,
                        obscureText: true,
                        decoration: InputDecoration(
                          labelText: strings.confirmPassword,
                          prefixIcon: Icon(Icons.lock_reset_outlined),
                        ),
                        validator: (value) => value != _password.text
                            ? strings.passwordsMismatch
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
                            : Text(_registering ? strings.createAccount : strings.signIn),
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
                            ? strings.alreadyHaveAccount
                            : strings.newAccountPrompt,
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
    final strings = AppLocalizations.of(context)!;
    final shouldDelete = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: Text(strings.deleteTaskQuestion),
        content: Text(strings.deleteTaskConfirmation(task.title)),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: Text(strings.cancel),
          ),
          FilledButton.tonal(
            onPressed: () => Navigator.pop(context, true),
            child: Text(strings.delete),
          ),
        ],
      ),
    );
    if (shouldDelete == true) await viewModel.deleteTask(task.id);
  }

  Future<void> _logout(BuildContext context) async {
    final strings = AppLocalizations.of(context)!;
    final shouldLogout = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: Text(strings.signOutQuestion),
        content: Text(strings.signOutMessage),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: Text(strings.cancel),
          ),
          FilledButton(
            onPressed: () => Navigator.pop(context, true),
            child: Text(strings.signOut),
          ),
        ],
      ),
    );
    if (shouldLogout == true) await viewModel.logout();
  }

  Future<void> _openUsers(BuildContext context) async {
    await viewModel.loadUsers();
    if (!context.mounted) return;
    await Navigator.of(context).push(
      MaterialPageRoute<void>(
        builder: (_) => UserManagementScreen(viewModel: viewModel),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return ListenableBuilder(
      listenable: viewModel,
      builder: (context, _) {
        final vm = viewModel;
        final strings = AppLocalizations.of(context)!;
        return Scaffold(
          appBar: AppBar(
            title: Text(
              vm.isManager ? strings.allTasks : strings.myTasks,
              style: TextStyle(fontWeight: FontWeight.w700),
            ),
            actions: [
              if (vm.isManager)
                IconButton(
                  tooltip: strings.manageUsers,
                  onPressed: () => _openUsers(context),
                  icon: const Icon(Icons.people_alt_outlined),
                ),
              IconButton(
                tooltip: strings.signOut,
                onPressed: () => _logout(context),
                icon: const Icon(Icons.logout),
              ),
            ],
          ),
          floatingActionButton: FloatingActionButton.extended(
            onPressed: () => _openTaskEditor(context),
            icon: const Icon(Icons.add),
            label: Text(strings.newTask),
          ),
          body: Column(
            children: [
              if (vm.user?['name'] is String)
                Padding(
                  padding: const EdgeInsetsDirectional.fromSTEB(20, 0, 20, 12),
                  child: Align(
                    alignment: AlignmentDirectional.centerStart,
                    child: Text(
                      strings.helloUser(vm.user!['name'] as String),
                      style: Theme.of(context).textTheme.titleMedium,
                    ),
                  ),
                ),
              if (vm.user?['role'] is String)
                Padding(
                  padding: const EdgeInsetsDirectional.fromSTEB(20, 0, 20, 12),
                  child: Align(
                    alignment: AlignmentDirectional.centerStart,
                    child: Text(vm.isManager ? strings.manager : strings.worker),
                  ),
                ),
              if (vm.error != null)
                Padding(
                  padding: const EdgeInsetsDirectional.fromSTEB(16, 0, 16, 12),
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
                                padding: const EdgeInsetsDirectional.fromSTEB(
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
    final strings = AppLocalizations.of(context)!;
    final titleStyle = Theme.of(context).textTheme.titleMedium;
    return Card(
      margin: EdgeInsets.zero,
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 8),
        child: Row(
          children: [
            Semantics(
              label: task.isCompleted ? strings.markPending : strings.markComplete,
              child: Checkbox(value: task.isCompleted, onChanged: (_) => onToggle()),
            ),
            Expanded(
              child: GestureDetector(
                onTap: onEdit,
                child: Padding(
                  padding: const EdgeInsets.symmetric(vertical: 8),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      if (task.userName?.isNotEmpty == true)
                        Padding(
                          padding: const EdgeInsets.only(bottom: 4),
                          child: Text(task.userName!),
                        ),
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
              tooltip: strings.taskActions,
              onSelected: (value) => value == 'edit' ? onEdit() : onDelete(),
              itemBuilder: (context) => [
                PopupMenuItem(value: 'edit', child: Text(strings.edit)),
                PopupMenuItem(value: 'delete', child: Text(strings.delete)),
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
  int? _selectedUserId;

  @override
  void initState() {
    super.initState();
    _selectedUserId = widget.task?.userId ??
        (widget.viewModel.users.isNotEmpty
            ? int.tryParse('${widget.viewModel.users.first['id']}')
            : null);
  }

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
      userId: widget.task == null && widget.viewModel.isManager
          ? _selectedUserId
          : null,
    );
    if (!mounted) return;
    if (succeeded) {
      Navigator.pop(context);
    } else {
      setState(() {
        _saving = false;
        _error = widget.viewModel.error ?? AppLocalizations.of(context)!.taskSaveFailed;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    final strings = AppLocalizations.of(context)!;
    return AlertDialog(
      title: Text(widget.task == null ? strings.newTask : strings.editTask),
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
                decoration: InputDecoration(labelText: strings.title),
                validator: (value) => value == null || value.trim().isEmpty
                    ? strings.titleRequired
                    : null,
              ),
              if (widget.viewModel.isManager && widget.task == null) ...[
                const SizedBox(height: 12),
                DropdownButtonFormField<int>(
                  initialValue: _selectedUserId,
                  decoration: InputDecoration(labelText: strings.assignUser),
                  items: widget.viewModel.users.map((user) {
                    final id = int.parse('${user['id']}');
                    return DropdownMenuItem(
                      value: id,
                      child: Text('${user['name']} · ${user['email']}'),
                    );
                  }).toList(),
                  onChanged: (value) => setState(() => _selectedUserId = value),
                ),
              ],
              const SizedBox(height: 12),
              TextFormField(
                controller: _description,
                minLines: 2,
                maxLines: 4,
                maxLength: 5000,
                textCapitalization: TextCapitalization.sentences,
                decoration: InputDecoration(
                  labelText: strings.optionalDescription,
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
          child: Text(strings.cancel),
        ),
        FilledButton(
          onPressed: _saving ? null : _save,
          child: _saving
              ? const SizedBox.square(
                  dimension: 18,
                  child: CircularProgressIndicator(strokeWidth: 2),
                )
              : Text(strings.save),
        ),
      ],
    );
  }
}

class UserManagementScreen extends StatelessWidget {
  const UserManagementScreen({required this.viewModel, super.key});

  final TaskViewModel viewModel;

  Future<void> _editUser(BuildContext context, [Map<String, dynamic>? user]) async {
    await showDialog<void>(
      context: context,
      builder: (_) => UserEditorDialog(viewModel: viewModel, user: user),
    );
  }

  @override
  Widget build(BuildContext context) {
    final strings = AppLocalizations.of(context)!;
    return ListenableBuilder(
      listenable: viewModel,
      builder: (context, _) => Scaffold(
        appBar: AppBar(title: Text(strings.manageUsers)),
        floatingActionButton: FloatingActionButton.extended(
          onPressed: () => _editUser(context),
          icon: const Icon(Icons.person_add_alt_1),
          label: Text(strings.addUser),
        ),
        body: Column(
          children: [
            if (viewModel.error != null)
              Padding(
                padding: const EdgeInsets.all(16),
                child: _ErrorMessage(
                  message: viewModel.error!,
                  onRetry: viewModel.loadUsers,
                ),
              ),
            Expanded(
              child: RefreshIndicator(
                onRefresh: viewModel.loadUsers,
                child: viewModel.users.isEmpty
                    ? ListView(
                        physics: const AlwaysScrollableScrollPhysics(),
                        children: [
                          SizedBox(
                            height: MediaQuery.sizeOf(context).height * .5,
                            child: Center(child: Text(strings.noUsers)),
                          ),
                        ],
                      )
                    : ListView.separated(
                        padding: const EdgeInsetsDirectional.fromSTEB(
                          16,
                          12,
                          16,
                          100,
                        ),
                        itemCount: viewModel.users.length,
                        separatorBuilder: (_, _) => const SizedBox(height: 8),
                        itemBuilder: (context, index) {
                          final user = viewModel.users[index];
                          final role = user['role'] == 'manager'
                              ? strings.manager
                              : strings.worker;
                          return Card(
                            child: ListTile(
                              title: Text('${user['name']}'),
                              subtitle: Text(
                                '${user['email']} · $role · ${user['tasks_count'] ?? 0}',
                                textDirection: TextDirection.rtl,
                              ),
                              leading: const Icon(Icons.person_outline),
                              trailing: const Icon(Icons.edit_outlined),
                              onTap: () => _editUser(context, user),
                            ),
                          );
                        },
                      ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class UserEditorDialog extends StatefulWidget {
  const UserEditorDialog({required this.viewModel, this.user, super.key});

  final TaskViewModel viewModel;
  final Map<String, dynamic>? user;

  @override
  State<UserEditorDialog> createState() => _UserEditorDialogState();
}

class _UserEditorDialogState extends State<UserEditorDialog> {
  final _formKey = GlobalKey<FormState>();
  late final _name = TextEditingController(
    text: widget.user?['name'] as String? ?? '',
  );
  late final _email = TextEditingController(
    text: widget.user?['email'] as String? ?? '',
  );
  final _password = TextEditingController();
  late String _role = widget.user?['role'] as String? ?? 'worker';
  bool _saving = false;
  String? _error;

  @override
  void dispose() {
    _name.dispose();
    _email.dispose();
    _password.dispose();
    super.dispose();
  }

  Future<void> _save() async {
    final strings = AppLocalizations.of(context)!;
    if (!_formKey.currentState!.validate()) return;
    setState(() {
      _saving = true;
      _error = null;
    });
    final success = await widget.viewModel.saveUser(
      id: widget.user == null ? null : int.parse('${widget.user!['id']}'),
      name: _name.text.trim(),
      email: _email.text.trim(),
      role: _role,
      password: _password.text.isEmpty ? null : _password.text,
    );
    if (!mounted) return;
    if (success) {
      Navigator.pop(context);
    } else {
      setState(() {
        _saving = false;
        _error = widget.viewModel.error ?? strings.unexpectedError;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    final strings = AppLocalizations.of(context)!;
    return AlertDialog(
      title: Text(widget.user == null ? strings.addUser : strings.editUser),
      content: SizedBox(
        width: 440,
        child: Form(
          key: _formKey,
          child: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                TextFormField(
                  controller: _name,
                  decoration: InputDecoration(labelText: strings.name),
                  validator: (value) => value == null || value.trim().isEmpty
                      ? strings.userFieldsRequired
                      : null,
                ),
                const SizedBox(height: 12),
                TextFormField(
                  controller: _email,
                  keyboardType: TextInputType.emailAddress,
                  textDirection: TextDirection.ltr,
                  textAlign: TextAlign.left,
                  decoration: InputDecoration(labelText: strings.email),
                  validator: (value) => value == null || !value.contains('@')
                      ? strings.emailRequired
                      : null,
                ),
                const SizedBox(height: 12),
                DropdownButtonFormField<String>(
                  initialValue: _role,
                  decoration: InputDecoration(labelText: strings.userRole),
                  items: [
                    DropdownMenuItem(
                      value: 'worker',
                      child: Text(strings.worker),
                    ),
                    DropdownMenuItem(
                      value: 'manager',
                      child: Text(strings.manager),
                    ),
                  ],
                  onChanged: (value) {
                    if (value != null) setState(() => _role = value);
                  },
                ),
                const SizedBox(height: 12),
                TextFormField(
                  controller: _password,
                  obscureText: true,
                  decoration: InputDecoration(
                    labelText: widget.user == null
                        ? strings.password
                        : strings.newPasswordOptional,
                  ),
                  validator: (value) {
                    if (widget.user == null && (value?.length ?? 0) < 8) {
                      return strings.userPasswordRequired;
                    }
                    if (value != null && value.isNotEmpty && value.length < 8) {
                      return strings.userPasswordRequired;
                    }
                    return null;
                  },
                ),
                if (_error != null) ...[
                  const SizedBox(height: 8),
                  _ErrorMessage(message: _error!),
                ],
              ],
            ),
          ),
        ),
      ),
      actions: [
        TextButton(
          onPressed: _saving ? null : () => Navigator.pop(context),
          child: Text(strings.cancel),
        ),
        FilledButton(
          onPressed: _saving ? null : _save,
          child: _saving
              ? const SizedBox.square(
                  dimension: 18,
                  child: CircularProgressIndicator(strokeWidth: 2),
                )
              : Text(strings.saveUser),
        ),
      ],
    );
  }
}

class _EmptyTasks extends StatelessWidget {
  const _EmptyTasks();

  @override
  Widget build(BuildContext context) {
    final strings = AppLocalizations.of(context)!;
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
              strings.noTasks,
              style: Theme.of(context).textTheme.titleLarge,
            ),
            const SizedBox(height: 4),
            Text(strings.noTasksHint),
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
    final strings = AppLocalizations.of(context)!;
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
            TextButton(onPressed: onRetry, child: Text(strings.retry)),
        ],
      ),
    );
  }
}
