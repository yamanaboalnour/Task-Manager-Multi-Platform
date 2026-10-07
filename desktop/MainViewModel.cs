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
    private string _userEditorName = string.Empty;
    private string _userEditorEmail = string.Empty;
    private string _userEditorPassword = string.Empty;
    private string _firstName = string.Empty;
    private string _lastName = string.Empty;
    private string _passwordConfirmation = string.Empty;
    private TaskItem? _selectedTask;
    private UserItem? _selectedUser;
    private RegistrationRequestItem? _selectedRegistrationRequest;
    private int? _selectedTaskOwnerId;
    private bool _isAuthenticated;
    private bool _isManager;
    private bool _isCreating;
    private bool _isCreatingUser;
    private bool _isUserManagement;
    private bool _isRegistrationManagement;
    private bool _isRegistering;
    private bool _userEditorIsManager;
    private bool _isBusy;

    public MainViewModel()
    {
        _apiBaseUrl = _settings.LoadApiBaseUrl();
        LoginCommand = new AsyncCommand(LoginAsync, () => !IsBusy && !IsAuthenticated);
        ToggleRegistrationCommand = new AsyncCommand(ToggleRegistrationAsync, () => !IsBusy && !IsAuthenticated);
        ForgotPasswordCommand = new AsyncCommand(ForgotPasswordAsync, () => !IsBusy && !IsAuthenticated);
        LogoutCommand = new AsyncCommand(LogoutAsync, () => !IsBusy && IsAuthenticated);
        RefreshCommand = new AsyncCommand(LoadTasksAsync, () => !IsBusy && IsAuthenticated);
        NewTaskCommand = new AsyncCommand(NewTaskAsync, () => !IsBusy && IsAuthenticated);
        SaveTaskCommand = new AsyncCommand(SaveTaskAsync, () => !IsBusy && IsAuthenticated);
        ToggleCompletionCommand = new AsyncCommand(ToggleCompletionAsync,
            () => !IsBusy && IsAuthenticated && SelectedTask is not null);
        DeleteTaskCommand = new AsyncCommand(DeleteTaskAsync,
            () => !IsBusy && IsAuthenticated && SelectedTask is not null);
        ManageUsersCommand = new AsyncCommand(ShowUsersAsync, () => !IsBusy && IsManager);
        ShowTasksCommand = new AsyncCommand(ShowTasksAsync, () => !IsBusy && IsAuthenticated);
        NewUserCommand = new AsyncCommand(NewUserAsync, () => !IsBusy && IsManager);
        SaveUserCommand = new AsyncCommand(SaveUserAsync, () => !IsBusy && IsManager);
        ShowRegistrationRequestsCommand = new AsyncCommand(
            ShowRegistrationRequestsAsync, () => !IsBusy && IsManager);
        ApproveRegistrationRequestCommand = new AsyncCommand(
            () => ReviewRegistrationRequestAsync(true),
            () => !IsBusy && IsManager && SelectedRegistrationRequest?.Status == "pending");
        RejectRegistrationRequestCommand = new AsyncCommand(
            () => ReviewRegistrationRequestAsync(false),
            () => !IsBusy && IsManager && SelectedRegistrationRequest?.Status == "pending");
    }

    public event PropertyChangedEventHandler? PropertyChanged;

    public ObservableCollection<TaskItem> Tasks { get; } = [];
    public ObservableCollection<UserItem> Users { get; } = [];
    public ObservableCollection<RegistrationRequestItem> RegistrationRequests { get; } = [];
    public AsyncCommand LoginCommand { get; }
    public AsyncCommand ToggleRegistrationCommand { get; }
    public AsyncCommand ForgotPasswordCommand { get; }
    public AsyncCommand LogoutCommand { get; }
    public AsyncCommand RefreshCommand { get; }
    public AsyncCommand NewTaskCommand { get; }
    public AsyncCommand SaveTaskCommand { get; }
    public AsyncCommand ToggleCompletionCommand { get; }
    public AsyncCommand DeleteTaskCommand { get; }
    public AsyncCommand ManageUsersCommand { get; }
    public AsyncCommand ShowTasksCommand { get; }
    public AsyncCommand NewUserCommand { get; }
    public AsyncCommand SaveUserCommand { get; }
    public AsyncCommand ShowRegistrationRequestsCommand { get; }
    public AsyncCommand ApproveRegistrationRequestCommand { get; }
    public AsyncCommand RejectRegistrationRequestCommand { get; }

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

    public string FirstName
    {
        get => _firstName;
        set => SetField(ref _firstName, value);
    }

    public string LastName
    {
        get => _lastName;
        set => SetField(ref _lastName, value);
    }

    public string PasswordConfirmation
    {
        get => _passwordConfirmation;
        set => SetField(ref _passwordConfirmation, value);
    }

    public string AuthenticationActionLabel => IsRegistering
        ? Properties.Strings.RequestAccount
        : Properties.Strings.SignIn;

    public string RegistrationToggleLabel => IsRegistering
        ? Properties.Strings.ExistingAccount
        : Properties.Strings.NewAccount;

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

    public string WelcomeMessage => string.Format(
        Properties.Strings.WelcomeMessage,
        _userName,
        IsManager ? Properties.Strings.Manager : Properties.Strings.Worker);

    public string EditorHeading => IsCreating ? Properties.Strings.NewTask : Properties.Strings.TaskDetails;
    public string UserEditorHeading => IsCreatingUser ? Properties.Strings.AddUser : Properties.Strings.UserDetails;
    public bool IsTaskManagement => !IsUserManagement && !IsRegistrationManagement;
    public bool IsRegistrationManagement => _isRegistrationManagement;

    public bool IsRegistering
    {
        get => _isRegistering;
        private set
        {
            if (SetField(ref _isRegistering, value))
            {
                OnPropertyChanged(nameof(AuthenticationActionLabel));
                OnPropertyChanged(nameof(RegistrationToggleLabel));
                OnPropertyChanged(nameof(IsNotRegistering));
            }
        }
    }

    public bool IsNotRegistering => !IsRegistering;

    public RegistrationRequestItem? SelectedRegistrationRequest
    {
        get => _selectedRegistrationRequest;
        set
        {
            if (SetField(ref _selectedRegistrationRequest, value))
            {
                ApproveRegistrationRequestCommand.NotifyCanExecuteChanged();
                RejectRegistrationRequestCommand.NotifyCanExecuteChanged();
            }
        }
    }
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

    public string UserEditorName
    {
        get => _userEditorName;
        set => SetField(ref _userEditorName, value);
    }

    public string UserEditorEmail
    {
        get => _userEditorEmail;
        set => SetField(ref _userEditorEmail, value);
    }

    public string UserEditorPassword
    {
        get => _userEditorPassword;
        set => SetField(ref _userEditorPassword, value);
    }

    public bool UserEditorIsManager
    {
        get => _userEditorIsManager;
        set => SetField(ref _userEditorIsManager, value);
    }

    public int? SelectedTaskOwnerId
    {
        get => _selectedTaskOwnerId;
        set => SetField(ref _selectedTaskOwnerId, value);
    }

    public UserItem? SelectedUser
    {
        get => _selectedUser;
        set
        {
            if (SetField(ref _selectedUser, value))
            {
                _isCreatingUser = false;
                UserEditorName = value?.Name ?? string.Empty;
                UserEditorEmail = value?.Email ?? string.Empty;
                UserEditorPassword = string.Empty;
                UserEditorIsManager = value?.Role == "manager";
                OnPropertyChanged(nameof(UserEditorHeading));
            }
        }
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
                if (IsManager && value is not null)
                {
                    SelectedTaskOwnerId = value.UserId;
                }
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

    public bool IsManager
    {
        get => _isManager;
        private set
        {
            if (SetField(ref _isManager, value))
            {
                OnPropertyChanged(nameof(WelcomeMessage));
                RaiseCommandStates();
            }
        }
    }

    public bool IsUserManagement
    {
        get => _isUserManagement;
        private set
        {
            if (SetField(ref _isUserManagement, value))
            {
                OnPropertyChanged(nameof(IsTaskManagement));
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

    private bool IsCreatingUser
    {
        get => _isCreatingUser;
        set
        {
            if (SetField(ref _isCreatingUser, value))
            {
                OnPropertyChanged(nameof(UserEditorHeading));
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
            if (IsRegistering)
            {
                if (string.IsNullOrWhiteSpace(FirstName) || string.IsNullOrWhiteSpace(LastName))
                {
                    ErrorMessage = Properties.Strings.RegistrationFieldsRequired;
                    return;
                }

                if (Password.Length < 8 || Password != PasswordConfirmation)
                {
                    ErrorMessage = Properties.Strings.PasswordConfirmationMismatch;
                    return;
                }

                await _api.RequestRegistrationAsync(
                    ApiBaseUrl,
                    FirstName.Trim(),
                    LastName.Trim(),
                    Email.Trim(),
                    Password);
                Password = string.Empty;
                PasswordConfirmation = string.Empty;
                FirstName = string.Empty;
                LastName = string.Empty;
                IsRegistering = false;
                StatusMessage = Properties.Strings.RequestSubmitted;
                return;
            }

            var result = await _api.LoginAsync(ApiBaseUrl, Email.Trim(), Password);
            _token = result.Token;
            _userName = string.IsNullOrWhiteSpace(result.User.Name) ? Email.Trim() : result.User.Name;
            IsManager = result.User.Role == "manager";
            OnPropertyChanged(nameof(WelcomeMessage));
            Password = string.Empty;
            IsAuthenticated = true;
            StatusMessage = Properties.Strings.LoadingTasks;
            await LoadTasksCoreAsync();
            if (IsManager)
            {
                await LoadUsersCoreAsync();
                await LoadRegistrationRequestsCoreAsync();
            }
        });
    }

    private Task ToggleRegistrationAsync()
    {
        ErrorMessage = string.Empty;
        IsRegistering = !IsRegistering;
        Password = string.Empty;
        PasswordConfirmation = string.Empty;
        return Task.CompletedTask;
    }

    private async Task ForgotPasswordAsync()
    {
        ErrorMessage = string.Empty;
        if (string.IsNullOrWhiteSpace(Email))
        {
            ErrorMessage = Properties.Strings.EnterEmailForReset;
            return;
        }

        await RunBusyAsync(async () =>
        {
            await _api.RequestPasswordResetAsync(ApiBaseUrl, Email.Trim());
            StatusMessage = Properties.Strings.PasswordResetSent;
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
            if (IsManager && Users.Count > 0 && SelectedTaskOwnerId is null)
            {
                SelectedTaskOwnerId = Users[0].Id;
            }
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
        if (IsManager)
        {
            SelectedTaskOwnerId = Users.FirstOrDefault()?.Id;
        }
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
                    ? await _api.CreateTaskAsync(
                        ApiBaseUrl,
                        _token,
                        title,
                        description,
                        IsManager ? SelectedTaskOwnerId : null)
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

    private async Task ShowUsersAsync()
    {
        ErrorMessage = string.Empty;
        _isRegistrationManagement = false;
        OnPropertyChanged(nameof(IsRegistrationManagement));
        OnPropertyChanged(nameof(IsTaskManagement));
        IsUserManagement = true;
        await RunBusyAsync(LoadUsersCoreAsync);
    }

    private async Task ShowRegistrationRequestsAsync()
    {
        ErrorMessage = string.Empty;
        IsUserManagement = false;
        _isRegistrationManagement = true;
        OnPropertyChanged(nameof(IsRegistrationManagement));
        OnPropertyChanged(nameof(IsTaskManagement));
        await RunBusyAsync(LoadRegistrationRequestsCoreAsync);
    }

    private async Task ShowTasksAsync()
    {
        ErrorMessage = string.Empty;
        _isRegistrationManagement = false;
        OnPropertyChanged(nameof(IsRegistrationManagement));
        OnPropertyChanged(nameof(IsTaskManagement));
        IsUserManagement = false;
        await LoadTasksAsync();
    }

    private async Task LoadRegistrationRequestsCoreAsync()
    {
        if (!IsManager)
        {
            return;
        }

        try
        {
            var requests = await _api.GetRegistrationRequestsAsync(ApiBaseUrl, _token);
            RegistrationRequests.Clear();
            foreach (var request in requests)
            {
                RegistrationRequests.Add(request);
            }

            SelectedRegistrationRequest = RegistrationRequests.FirstOrDefault(
                request => request.Status == "pending");
        }
        catch (Exception exception)
        {
            HandleError(exception);
        }
    }

    private async Task ReviewRegistrationRequestAsync(bool approve)
    {
        if (SelectedRegistrationRequest is not { Status: "pending" } request)
        {
            return;
        }

        ErrorMessage = string.Empty;
        await RunBusyAsync(async () =>
        {
            await _api.ReviewRegistrationRequestAsync(ApiBaseUrl, _token, request.Id, approve);
            StatusMessage = Properties.Strings.RequestReviewComplete;
            await LoadRegistrationRequestsCoreAsync();
            if (approve)
            {
                await LoadUsersCoreAsync();
            }
        });
    }

    private async Task LoadUsersCoreAsync()
    {
        if (!IsManager)
        {
            return;
        }

        try
        {
            var users = await _api.GetUsersAsync(ApiBaseUrl, _token);
            Users.Clear();
            foreach (var user in users)
            {
                Users.Add(user);
            }

            if (SelectedTaskOwnerId is null || Users.All(user => user.Id != SelectedTaskOwnerId))
            {
                SelectedTaskOwnerId = Users.FirstOrDefault()?.Id;
            }

            StatusMessage = string.Format(Properties.Strings.UserCount, Users.Count);
        }
        catch (Exception exception)
        {
            HandleError(exception);
        }
    }

    private Task NewUserAsync()
    {
        ErrorMessage = string.Empty;
        IsCreatingUser = true;
        SelectedUser = null;
        UserEditorName = string.Empty;
        UserEditorEmail = string.Empty;
        UserEditorPassword = string.Empty;
        UserEditorIsManager = false;
        return Task.CompletedTask;
    }

    private async Task SaveUserAsync()
    {
        ErrorMessage = string.Empty;
        if (string.IsNullOrWhiteSpace(UserEditorName) || string.IsNullOrWhiteSpace(UserEditorEmail))
        {
            ErrorMessage = Properties.Strings.UserFieldsRequired;
            return;
        }

        if ((SelectedUser is null || !string.IsNullOrWhiteSpace(UserEditorPassword))
            && UserEditorPassword.Length < 8)
        {
            ErrorMessage = Properties.Strings.UserPasswordRequired;
            return;
        }

        await RunBusyAsync(async () =>
        {
            try
            {
                var editing = SelectedUser is not null;
                var user = editing
                    ? await _api.UpdateUserAsync(
                        ApiBaseUrl,
                        _token,
                        SelectedUser!.Id,
                        UserEditorName.Trim(),
                        UserEditorEmail.Trim(),
                        UserEditorPassword,
                        UserEditorIsManager ? "manager" : "worker")
                    : await _api.CreateUserAsync(
                        ApiBaseUrl,
                        _token,
                        UserEditorName.Trim(),
                        UserEditorEmail.Trim(),
                        UserEditorPassword,
                        UserEditorIsManager ? "manager" : "worker");

                await LoadUsersCoreAsync();
                SelectedUser = Users.FirstOrDefault(item => item.Id == user.Id);
                UserEditorPassword = string.Empty;
                StatusMessage = editing ? Properties.Strings.UserSaved : Properties.Strings.UserCreated;
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
        Users.Clear();
        RegistrationRequests.Clear();
        UserEditorName = string.Empty;
        UserEditorEmail = string.Empty;
        UserEditorPassword = string.Empty;
        Password = string.Empty;
        PasswordConfirmation = string.Empty;
        FirstName = string.Empty;
        LastName = string.Empty;
        SelectedTask = null;
        SelectedUser = null;
        SelectedRegistrationRequest = null;
        IsCreating = false;
        IsCreatingUser = false;
        IsRegistering = false;
        IsManager = false;
        IsUserManagement = false;
        _isRegistrationManagement = false;
        OnPropertyChanged(nameof(IsRegistrationManagement));
        OnPropertyChanged(nameof(IsTaskManagement));
        SelectedTaskOwnerId = null;
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
        ToggleRegistrationCommand?.NotifyCanExecuteChanged();
        ForgotPasswordCommand?.NotifyCanExecuteChanged();
        ManageUsersCommand?.NotifyCanExecuteChanged();
        ShowTasksCommand?.NotifyCanExecuteChanged();
        NewUserCommand?.NotifyCanExecuteChanged();
        SaveUserCommand?.NotifyCanExecuteChanged();
        ShowRegistrationRequestsCommand?.NotifyCanExecuteChanged();
        ApproveRegistrationRequestCommand?.NotifyCanExecuteChanged();
        RejectRegistrationRequestCommand?.NotifyCanExecuteChanged();
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
