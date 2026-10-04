using System.Collections.ObjectModel;
using System.ComponentModel;
using System.Net;
using System.Net.Http;
using System.Runtime.CompilerServices;
using TaskManager.Desktop.Infrastructure;
using TaskManager.Desktop.Models;
using TaskManager.Desktop.Services;

namespace TaskManager.Desktop;

public sealed class MainViewModel : INotifyPropertyChanged
{
    private readonly ApiClient _api = new();
    private readonly SettingsStore _settings = new();
    private string _apiBaseUrl;
    private string _email = string.Empty;
    private string _password = string.Empty;
    private string _errorMessage = string.Empty;
    private string _statusMessage = Properties.Strings.SignInWithApi;
    private string _userName = string.Empty;
    private string _token = string.Empty;
    private string _editorTitle = string.Empty;
    private string _editorDescription = string.Empty;
    private TaskItem? _selectedTask;
    private bool _isAuthenticated;
    private bool _isCreating;
    private bool _isBusy;

    public MainViewModel()
    {
        _apiBaseUrl = _settings.LoadApiBaseUrl();
        LoginCommand = new AsyncCommand(LoginAsync, () => !IsBusy && !IsAuthenticated);
        LogoutCommand = new AsyncCommand(LogoutAsync, () => !IsBusy && IsAuthenticated);
        RefreshCommand = new AsyncCommand(LoadTasksAsync, () => !IsBusy && IsAuthenticated);
        NewTaskCommand = new AsyncCommand(NewTaskAsync, () => !IsBusy && IsAuthenticated);
        SaveTaskCommand = new AsyncCommand(SaveTaskAsync, () => !IsBusy && IsAuthenticated);
        ToggleCompletionCommand = new AsyncCommand(ToggleCompletionAsync,
            () => !IsBusy && IsAuthenticated && SelectedTask is not null);
        DeleteTaskCommand = new AsyncCommand(DeleteTaskAsync,
            () => !IsBusy && IsAuthenticated && SelectedTask is not null);
    }

    public event PropertyChangedEventHandler? PropertyChanged;

    public ObservableCollection<TaskItem> Tasks { get; } = [];
    public AsyncCommand LoginCommand { get; }
    public AsyncCommand LogoutCommand { get; }
    public AsyncCommand RefreshCommand { get; }
    public AsyncCommand NewTaskCommand { get; }
    public AsyncCommand SaveTaskCommand { get; }
    public AsyncCommand ToggleCompletionCommand { get; }
    public AsyncCommand DeleteTaskCommand { get; }

    public string ApiBaseUrl
    {
        get => _apiBaseUrl;
        set => SetField(ref _apiBaseUrl, value);
    }

    public string Email
    {
        get => _email;
        set => SetField(ref _email, value);
    }

    public string Password
    {
        get => _password;
        set => SetField(ref _password, value);
    }

    public string ErrorMessage
    {
        get => _errorMessage;
        private set => SetField(ref _errorMessage, value);
    }

    public string StatusMessage
    {
        get => _statusMessage;
        private set => SetField(ref _statusMessage, value);
    }

    public string WelcomeMessage => string.Format(Properties.Strings.WelcomeMessage, _userName);

    public string EditorHeading => IsCreating ? Properties.Strings.NewTask : Properties.Strings.TaskDetails;
    public string ToggleCompletionLabel => SelectedTask?.IsCompleted == true
        ? Properties.Strings.MarkPending
        : Properties.Strings.MarkComplete;

    public string EditorTitle
    {
        get => _editorTitle;
        set => SetField(ref _editorTitle, value);
    }

    public string EditorDescription
    {
        get => _editorDescription;
        set => SetField(ref _editorDescription, value);
    }

    public TaskItem? SelectedTask
    {
        get => _selectedTask;
        set
        {
            if (SetField(ref _selectedTask, value))
            {
                _isCreating = false;
                EditorTitle = value?.Title ?? string.Empty;
                EditorDescription = value?.Description ?? string.Empty;
                OnPropertyChanged(nameof(EditorHeading));
                OnPropertyChanged(nameof(ToggleCompletionLabel));
                RaiseCommandStates();
            }
        }
    }

    public bool IsAuthenticated
    {
        get => _isAuthenticated;
        private set
        {
            if (SetField(ref _isAuthenticated, value))
            {
                RaiseCommandStates();
            }
        }
    }

    private bool IsCreating
    {
        get => _isCreating;
        set
        {
            if (SetField(ref _isCreating, value))
            {
                OnPropertyChanged(nameof(EditorHeading));
            }
        }
    }

    private bool IsBusy
    {
        get => _isBusy;
        set
        {
            if (SetField(ref _isBusy, value))
            {
                RaiseCommandStates();
            }
        }
    }

    private async Task LoginAsync()
    {
        ErrorMessage = string.Empty;
        if (string.IsNullOrWhiteSpace(Email) || string.IsNullOrWhiteSpace(Password))
        {
            ErrorMessage = Properties.Strings.CredentialsRequired;
            return;
        }

        await RunBusyAsync(async () =>
        {
            _settings.SaveApiBaseUrl(ApiBaseUrl.Trim());
            var result = await _api.LoginAsync(ApiBaseUrl, Email.Trim(), Password);
            _token = result.Token;
            _userName = string.IsNullOrWhiteSpace(result.User.Name) ? Email.Trim() : result.User.Name;
            OnPropertyChanged(nameof(WelcomeMessage));
            Password = string.Empty;
            IsAuthenticated = true;
            StatusMessage = Properties.Strings.LoadingTasks;
            await LoadTasksCoreAsync();
        });
    }

    private async Task LogoutAsync()
    {
        ErrorMessage = string.Empty;
        await RunBusyAsync(async () =>
        {
            try
            {
                await _api.LogoutAsync(ApiBaseUrl, _token);
                StatusMessage = Properties.Strings.SignedOut;
            }
            catch (Exception exception)
            {
                ErrorMessage = string.Format(Properties.Strings.LogoutApiError, GetError(exception));
            }
            finally
            {
                ClearSession();
            }
        });
    }

    private async Task LoadTasksAsync()
    {
        ErrorMessage = string.Empty;
        await RunBusyAsync(LoadTasksCoreAsync);
    }

    private async Task LoadTasksCoreAsync()
    {
        try
        {
            var tasks = await _api.GetTasksAsync(ApiBaseUrl, _token);
            Tasks.Clear();
            foreach (var task in tasks)
            {
                Tasks.Add(task);
            }

            SelectedTask = null;
            IsCreating = false;
            StatusMessage = string.Format(Properties.Strings.TaskCount, tasks.Count);
        }
        catch (Exception exception)
        {
            HandleError(exception);
        }
    }

    private Task NewTaskAsync()
    {
        ErrorMessage = string.Empty;
        IsCreating = true;
        SelectedTask = null;
        EditorTitle = string.Empty;
        EditorDescription = string.Empty;
        return Task.CompletedTask;
    }

    private async Task SaveTaskAsync()
    {
        ErrorMessage = string.Empty;
        var title = EditorTitle.Trim();
        if (title.Length == 0)
        {
            ErrorMessage = Properties.Strings.TitleRequired;
            return;
        }

        if (title.Length > 255 || EditorDescription.Length > 5000)
        {
            ErrorMessage = Properties.Strings.TaskLengthLimit;
            return;
        }

        var description = string.IsNullOrWhiteSpace(EditorDescription) ? null : EditorDescription.Trim();
        await RunBusyAsync(async () =>
        {
            try
            {
                var task = IsCreating || SelectedTask is null
                    ? await _api.CreateTaskAsync(ApiBaseUrl, _token, title, description)
                    : await _api.UpdateTaskAsync(ApiBaseUrl, _token, SelectedTask.Id, title, description);

                var existing = Tasks.FirstOrDefault(item => item.Id == task.Id);
                if (existing is null)
                {
                    Tasks.Insert(0, task);
                }
                else
                {
                    existing.Title = task.Title;
                    existing.Description = task.Description;
                    existing.IsCompleted = task.IsCompleted;
                    task = existing;
                }

                SelectedTask = task;
                IsCreating = false;
                StatusMessage = Properties.Strings.TaskSaved;
            }
            catch (Exception exception)
            {
                HandleError(exception);
            }
        });
    }

    private async Task ToggleCompletionAsync()
    {
        if (SelectedTask is null)
        {
            return;
        }

        ErrorMessage = string.Empty;
        await RunBusyAsync(async () =>
        {
            try
            {
                SelectedTask.IsCompleted = (await _api.ToggleCompletionAsync(
                    ApiBaseUrl, _token, SelectedTask.Id)).IsCompleted;
                StatusMessage = SelectedTask.IsCompleted ? Properties.Strings.TaskCompleted : Properties.Strings.TaskMarkedPending;
                OnPropertyChanged(nameof(ToggleCompletionLabel));
            }
            catch (Exception exception)
            {
                HandleError(exception);
            }
        });
    }

    private async Task DeleteTaskAsync()
    {
        if (SelectedTask is null)
        {
            return;
        }

        ErrorMessage = string.Empty;
        await RunBusyAsync(async () =>
        {
            try
            {
                await _api.DeleteTaskAsync(ApiBaseUrl, _token, SelectedTask.Id);
                Tasks.Remove(SelectedTask);
                SelectedTask = null;
                EditorTitle = string.Empty;
                EditorDescription = string.Empty;
                StatusMessage = Properties.Strings.TaskDeleted;
            }
            catch (Exception exception)
            {
                HandleError(exception);
            }
        });
    }

    private async Task RunBusyAsync(Func<Task> operation)
    {
        if (IsBusy)
        {
            return;
        }

        IsBusy = true;
        try
        {
            await operation();
        }
        catch (Exception exception)
        {
            HandleError(exception);
        }
        finally
        {
            IsBusy = false;
        }
    }

    private void HandleError(Exception exception)
    {
        ErrorMessage = GetError(exception);
        if (exception is ApiException { StatusCode: HttpStatusCode.Unauthorized } && IsAuthenticated)
        {
            ClearSession();
            StatusMessage = Properties.Strings.SessionExpired;
        }
    }

    private void ClearSession()
    {
        _token = string.Empty;
        _userName = string.Empty;
        Tasks.Clear();
        SelectedTask = null;
        IsCreating = false;
        IsAuthenticated = false;
        OnPropertyChanged(nameof(WelcomeMessage));
        RaiseCommandStates();
    }

    private static string GetError(Exception exception) =>
        exception switch
        {
            ApiException { StatusCode: HttpStatusCode.Unauthorized } => Properties.Strings.SessionExpired,
            ApiException { StatusCode: HttpStatusCode.Forbidden } => Properties.Strings.PermissionDenied,
            ApiException { StatusCode: HttpStatusCode.NotFound } => Properties.Strings.TaskNotFound,
            ApiException => exception.Message,
            HttpRequestException => Properties.Strings.ApiUnavailable,
            TaskCanceledException => Properties.Strings.ApiTimedOut,
            _ => Properties.Strings.UnexpectedError
        };

    private void RaiseCommandStates()
    {
        LoginCommand?.NotifyCanExecuteChanged();
        LogoutCommand?.NotifyCanExecuteChanged();
        RefreshCommand?.NotifyCanExecuteChanged();
        NewTaskCommand?.NotifyCanExecuteChanged();
        SaveTaskCommand?.NotifyCanExecuteChanged();
        ToggleCompletionCommand?.NotifyCanExecuteChanged();
        DeleteTaskCommand?.NotifyCanExecuteChanged();
    }

    private bool SetField<T>(ref T field, T value, [CallerMemberName] string? propertyName = null)
    {
        if (EqualityComparer<T>.Default.Equals(field, value))
        {
            return false;
        }

        field = value;
        OnPropertyChanged(propertyName);
        return true;
    }

    private void OnPropertyChanged([CallerMemberName] string? propertyName = null) =>
        PropertyChanged?.Invoke(this, new PropertyChangedEventArgs(propertyName));
}
