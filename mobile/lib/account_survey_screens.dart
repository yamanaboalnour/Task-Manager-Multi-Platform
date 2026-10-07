import 'package:flutter/material.dart';

import 'l10n/generated/app_localizations.dart';
import 'view_models/task_view_model.dart';

class RegistrationRequestsScreen extends StatelessWidget {
  const RegistrationRequestsScreen({required this.viewModel, super.key});

  final TaskViewModel viewModel;

  @override
  Widget build(BuildContext context) {
    final strings = AppLocalizations.of(context)!;
    return Scaffold(
      appBar: AppBar(title: Text(strings.registrationRequests)),
      body: ListenableBuilder(
        listenable: viewModel,
        builder: (context, _) => ListView(
          padding: const EdgeInsets.all(16),
          children: [
            if (viewModel.error != null) Text(viewModel.error!),
            for (final request in viewModel.registrationRequests)
              Card(
                child: ListTile(
                  title: Text('${request['first_name'] ?? ''} ${request['last_name'] ?? ''}'),
                  subtitle: Text(
                    '${request['email'] ?? ''}\n${_localizedStatus(strings, request['status'] as String?)}',
                    textDirection: TextDirection.ltr,
                    textAlign: TextAlign.right,
                  ),
                  isThreeLine: true,
                  trailing: request['status'] == 'pending'
                      ? Wrap(
                          children: [
                            IconButton(
                              tooltip: strings.approve,
                              onPressed: () => viewModel.reviewRegistrationRequest(
                                request['id'] as int,
                                approve: true,
                              ),
                              icon: const Icon(Icons.check),
                            ),
                            IconButton(
                              tooltip: strings.reject,
                              onPressed: () => viewModel.reviewRegistrationRequest(
                                request['id'] as int,
                                approve: false,
                              ),
                              icon: const Icon(Icons.close),
                            ),
                          ],
                        )
                      : null,
                ),
              ),
          ],
        ),
      ),
    );
  }

  String _localizedStatus(AppLocalizations strings, String? status) => switch (status) {
        'pending' => strings.pending,
        'approved' => strings.approved,
        'rejected' => strings.rejected,
        _ => '',
      };
}

class SurveysScreen extends StatelessWidget {
  const SurveysScreen({required this.viewModel, super.key});

  final TaskViewModel viewModel;

  Future<void> _openBuilder(BuildContext context, [Map<String, dynamic>? survey]) async {
    await Navigator.of(context).push<void>(
      MaterialPageRoute(
        builder: (_) => SurveyBuilderScreen(viewModel: viewModel, survey: survey),
      ),
    );
  }

  Future<void> _openSurvey(BuildContext context, Map<String, dynamic> survey) async {
    final id = survey['id'] as int;
    if (viewModel.isManager) {
      final results = await viewModel.getSurveyResults(id);
      if (results != null && context.mounted) {
        await Navigator.of(context).push<void>(
          MaterialPageRoute(
            builder: (_) => SurveyResultsScreen(results: results),
          ),
        );
      }
      return;
    }
    final detail = await viewModel.getSurvey(id);
    if (detail == null || !context.mounted) return;
    await Navigator.of(context).push<void>(
      MaterialPageRoute(
        builder: (_) => SurveyResponseScreen(viewModel: viewModel, survey: detail),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final strings = AppLocalizations.of(context)!;
    return Scaffold(
      appBar: AppBar(title: Text(strings.surveys)),
      floatingActionButton: viewModel.isManager
          ? FloatingActionButton.extended(
              onPressed: () => _openBuilder(context),
              icon: const Icon(Icons.add),
              label: Text(strings.createSurvey),
            )
          : null,
      body: ListenableBuilder(
        listenable: viewModel,
        builder: (context, _) => RefreshIndicator(
          onRefresh: viewModel.loadSurveys,
          child: ListView(
            padding: const EdgeInsets.all(16),
            children: [
              if (viewModel.error != null) Text(viewModel.error!),
              if (viewModel.surveys.isEmpty)
                Padding(
                  padding: const EdgeInsets.all(32),
                  child: Center(child: Text(strings.noSurveys)),
                ),
              for (final survey in viewModel.surveys)
                Card(
                  child: ListTile(
                    title: Text(survey['title'] as String? ?? ''),
                    subtitle: Text(
                      '${survey['status'] == 'published' ? strings.published : strings.draft}'
                      '${survey['has_responded'] == true ? ' · ${strings.alreadySubmitted}' : ''}',
                    ),
                    onTap: () => _openSurvey(context, survey),
                    trailing: viewModel.isManager
                        ? Wrap(
                            children: [
                              if (survey['status'] == 'draft')
                                IconButton(
                                  tooltip: strings.edit,
                                  onPressed: () => _openBuilder(context, survey),
                                  icon: const Icon(Icons.edit_outlined),
                                ),
                              if (survey['status'] == 'draft')
                                IconButton(
                                  tooltip: strings.publishSurvey,
                                  onPressed: () => viewModel.publishSurvey(survey['id'] as int),
                                  icon: const Icon(Icons.publish),
                                ),
                              IconButton(
                                tooltip: strings.surveyResults,
                                onPressed: () => _openSurvey(context, survey),
                                icon: const Icon(Icons.bar_chart),
                              ),
                            ],
                          )
                        : null,
                  ),
                ),
            ],
          ),
        ),
      ),
    );
  }
}

class SurveyBuilderScreen extends StatefulWidget {
  const SurveyBuilderScreen({
    required this.viewModel,
    this.survey,
    super.key,
  });

  final TaskViewModel viewModel;
  final Map<String, dynamic>? survey;

  @override
  State<SurveyBuilderScreen> createState() => _SurveyBuilderScreenState();
}

class _SurveyBuilderScreenState extends State<SurveyBuilderScreen> {
  late final _title = TextEditingController(text: widget.survey?['title'] as String?);
  late final _description = TextEditingController(text: widget.survey?['description'] as String?);
  final List<_QuestionDraft> _questions = [];

  @override
  void initState() {
    super.initState();
    for (final question in (widget.survey?['questions'] as List<dynamic>? ?? [])) {
      final value = question as Map<String, dynamic>;
      _questions.add(_QuestionDraft.fromJson(value));
    }
    if (_questions.isEmpty) _questions.add(_QuestionDraft());
  }

  @override
  void dispose() {
    _title.dispose();
    _description.dispose();
    for (final question in _questions) {
      question.dispose();
    }
    super.dispose();
  }

  Future<void> _save() async {
    if (_title.text.trim().isEmpty ||
        _questions.any((question) => question.text.text.trim().isEmpty)) {
      return;
    }
    final success = await widget.viewModel.saveSurvey(
      id: widget.survey?['id'] as int?,
      title: _title.text.trim(),
      description: _description.text.trim(),
      questions: _questions.map((question) => question.toJson()).toList(),
    );
    if (success && mounted) Navigator.pop(context);
  }

  @override
  Widget build(BuildContext context) {
    final strings = AppLocalizations.of(context)!;
    final questionTypes = {
      'short_text': strings.shortAnswer,
      'long_text': strings.longAnswer,
      'single_choice': strings.singleChoice,
      'multiple_choice': strings.multipleChoice,
      'dropdown': strings.dropdown,
      'yes_no': strings.yesNo,
    };
    return Scaffold(
      appBar: AppBar(
        title: Text(widget.survey == null ? strings.createSurvey : strings.editSurvey),
        actions: [IconButton(onPressed: _save, icon: const Icon(Icons.save))],
      ),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          TextField(controller: _title, decoration: InputDecoration(labelText: strings.surveyTitle)),
          TextField(controller: _description, decoration: InputDecoration(labelText: strings.surveyDescription)),
          const SizedBox(height: 12),
          ReorderableListView.builder(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            itemCount: _questions.length,
            onReorderItem: (oldIndex, newIndex) {
              setState(() {
                final question = _questions.removeAt(oldIndex);
                _questions.insert(newIndex, question);
              });
            },
            itemBuilder: (context, index) {
              final question = _questions[index];
              return Card(
                key: ValueKey(question),
                child: Padding(
                  padding: const EdgeInsets.all(12),
                  child: Column(
                    children: [
                      TextField(
                        controller: question.text,
                        decoration: InputDecoration(labelText: strings.questionText),
                      ),
                      DropdownButtonFormField<String>(
                        initialValue: question.type,
                        decoration: InputDecoration(labelText: strings.questionType),
                        items: questionTypes.entries
                            .map((entry) => DropdownMenuItem(value: entry.key, child: Text(entry.value)))
                            .toList(),
                        onChanged: (value) => setState(() => question.type = value ?? 'short_text'),
                      ),
                      if (const {'single_choice', 'multiple_choice', 'dropdown'}
                          .contains(question.type))
                        TextField(
                          controller: question.options,
                          decoration: InputDecoration(labelText: strings.optionsCommaSeparated),
                        ),
                      SwitchListTile(
                        contentPadding: EdgeInsets.zero,
                        title: Text(strings.requiredQuestion),
                        value: question.required,
                        onChanged: (value) => setState(() => question.required = value),
                      ),
                      Align(
                        alignment: AlignmentDirectional.centerEnd,
                        child: IconButton(
                          tooltip: strings.delete,
                          onPressed: _questions.length == 1
                              ? null
                              : () => setState(() {
                                    _questions.removeAt(index).dispose();
                                  }),
                          icon: const Icon(Icons.delete_outline),
                        ),
                      ),
                    ],
                  ),
                ),
              );
            },
          ),
          OutlinedButton.icon(
            onPressed: () => setState(() => _questions.add(_QuestionDraft())),
            icon: const Icon(Icons.add),
            label: Text(strings.addQuestion),
          ),
          const SizedBox(height: 12),
          FilledButton(onPressed: _save, child: Text(strings.save)),
          if (widget.viewModel.error != null) Text(widget.viewModel.error!),
        ],
      ),
    );
  }
}

class SurveyResponseScreen extends StatefulWidget {
  const SurveyResponseScreen({required this.viewModel, required this.survey, super.key});

  final TaskViewModel viewModel;
  final Map<String, dynamic> survey;

  @override
  State<SurveyResponseScreen> createState() => _SurveyResponseScreenState();
}

class _SurveyResponseScreenState extends State<SurveyResponseScreen> {
  final Map<int, TextEditingController> _text = {};
  final Map<int, Set<int>> _options = {};

  @override
  void dispose() {
    for (final controller in _text.values) {
      controller.dispose();
    }
    super.dispose();
  }

  Future<void> _submit() async {
    final questions = widget.survey['questions'] as List<dynamic>? ?? [];
    final answers = <Map<String, Object?>>[];
    for (final item in questions) {
      final question = item as Map<String, dynamic>;
      final id = question['id'] as int;
      final type = question['type'] as String;
      final answer = <String, Object?>{'question_id': id};
      if (const {'single_choice', 'multiple_choice', 'dropdown'}.contains(type)) {
        answer['option_ids'] = _options[id]?.toList() ?? <int>[];
      } else {
        final text = _text[id]?.text.trim() ?? '';
        if (text.isNotEmpty) answer['text'] = text;
      }
      answers.add(answer);
    }
    final success = await widget.viewModel.submitSurvey(
      widget.survey['id'] as int,
      answers,
    );
    if (success && mounted) Navigator.pop(context);
  }

  @override
  Widget build(BuildContext context) {
    final strings = AppLocalizations.of(context)!;
    final questions = widget.survey['questions'] as List<dynamic>? ?? [];
    return Scaffold(
      appBar: AppBar(title: Text(widget.survey['title'] as String? ?? strings.surveys)),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          for (final item in questions)
            Builder(builder: (context) {
              final question = item as Map<String, dynamic>;
              final id = question['id'] as int;
              final type = question['type'] as String;
              final options = question['options'] as List<dynamic>? ?? [];
              if (type == 'yes_no') {
                return DropdownButtonFormField<String>(
                  decoration: InputDecoration(labelText: question['text'] as String),
                  items: [
                    DropdownMenuItem(value: 'yes', child: Text(strings.yes)),
                    DropdownMenuItem(value: 'no', child: Text(strings.no)),
                  ],
                  onChanged: (value) {
                    if (value != null) {
                      _text.putIfAbsent(id, TextEditingController.new).text = value;
                    }
                  },
                );
              }
              if (type == 'dropdown') {
                return DropdownButtonFormField<int>(
                  decoration: InputDecoration(labelText: question['text'] as String),
                  initialValue: _options[id]?.isNotEmpty == true
                      ? _options[id]!.first
                      : null,
                  items: options.map((item) {
                    final option = item as Map<String, dynamic>;
                    return DropdownMenuItem<int>(
                      value: option['id'] as int,
                      child: Text(option['label'] as String),
                    );
                  }).toList(),
                  onChanged: (value) => setState(() {
                    if (value == null) {
                      _options.remove(id);
                    } else {
                      _options[id] = {value};
                    }
                  }),
                );
              }
              if (type == 'multiple_choice') {
                return Card(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      ListTile(title: Text(question['text'] as String)),
                      for (final item in options)
                        Builder(builder: (context) {
                          final option = item as Map<String, dynamic>;
                          final optionId = option['id'] as int;
                          return CheckboxListTile(
                            title: Text(option['label'] as String),
                            value: _options[id]?.contains(optionId) ?? false,
                            onChanged: (checked) => setState(() {
                              final selected = _options.putIfAbsent(id, () => {});
                              if (checked == true) {
                                selected.add(optionId);
                              } else {
                                selected.remove(optionId);
                              }
                            }),
                          );
                        }),
                    ],
                  ),
                );
              }
              if (type == 'single_choice') {
                return Card(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      ListTile(title: Text(question['text'] as String)),
                      RadioGroup<int>(
                        groupValue: _options[id]?.isNotEmpty == true
                            ? _options[id]!.first
                            : null,
                        onChanged: (value) {
                          if (value != null) {
                            setState(() => _options[id] = {value});
                          }
                        },
                        child: Column(
                          children: [
                            for (final item in options)
                              Builder(builder: (context) {
                                final option = item as Map<String, dynamic>;
                                return RadioListTile<int>(
                                  title: Text(option['label'] as String),
                                  value: option['id'] as int,
                                );
                              }),
                          ],
                        ),
                      ),
                    ],
                  ),
                );
              }
              return TextField(
                controller: _text.putIfAbsent(id, TextEditingController.new),
                maxLines: type == 'long_text' ? 5 : 1,
                decoration: InputDecoration(labelText: question['text'] as String),
              );
            }),
          FilledButton(onPressed: _submit, child: Text(strings.submitSurvey)),
          if (widget.viewModel.error != null) Text(widget.viewModel.error!),
        ],
      ),
    );
  }
}

class SurveyResultsScreen extends StatelessWidget {
  const SurveyResultsScreen({required this.results, super.key});

  final Map<String, dynamic> results;

  @override
  Widget build(BuildContext context) {
    final strings = AppLocalizations.of(context)!;
    final survey = results['survey'] as Map<String, dynamic>? ?? {};
    final questions = results['questions'] as List<dynamic>? ?? [];
    return Scaffold(
      appBar: AppBar(title: Text(strings.surveyResults)),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          Text(survey['title'] as String? ?? ''),
          Text('${strings.participants}: ${results['participant_count'] ?? 0}'),
          for (final item in questions)
            Builder(builder: (context) {
              final question = item as Map<String, dynamic>;
              final answers = question['answers'] as List<dynamic>? ?? [];
              final statistics = question['statistics'] as List<dynamic>? ?? [];
              return Card(
                child: Padding(
                  padding: const EdgeInsets.all(12),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(question['text'] as String),
                      for (final statistic in statistics)
                        Text('${statistic['label'] ?? statistic['answer']}: ${statistic['count']}'),
                      for (final item in answers)
                        Builder(builder: (context) {
                          final answer = item as Map<String, dynamic>;
                          final user = answer['user'] as Map<String, dynamic>? ?? {};
                          final options = answer['selected_options'] as List<dynamic>? ?? [];
                          return Text(
                            '${user['name'] ?? ''}: ${answer['text'] ?? options.map((option) => option['label']).join('، ')}',
                          );
                        }),
                    ],
                  ),
                ),
              );
            }),
        ],
      ),
    );
  }
}

class _QuestionDraft {
  _QuestionDraft({
    String? value,
    this.type = 'short_text',
    this.required = false,
    String optionsValue = '',
  }) : text = TextEditingController(text: value),
       options = TextEditingController(text: optionsValue);

  factory _QuestionDraft.fromJson(Map<String, dynamic> value) {
    final options = (value['options'] as List<dynamic>? ?? [])
        .map((item) => (item as Map<String, dynamic>)['label'] as String)
        .join(', ');
    return _QuestionDraft(
      value: value['text'] as String?,
      type: value['type'] as String? ?? 'short_text',
      required: value['is_required'] as bool? ?? false,
      optionsValue: options,
    );
  }

  final TextEditingController text;
  final TextEditingController options;
  String type;
  bool required;

  Map<String, Object?> toJson() => {
    'text': text.text.trim(),
    'type': type,
    'is_required': required,
    if (const {'single_choice', 'multiple_choice', 'dropdown'}.contains(type))
      'options': options.text.split(',').map((value) => value.trim()).where((value) => value.isNotEmpty).toList(),
  };

  void dispose() {
    text.dispose();
    options.dispose();
  }
}
