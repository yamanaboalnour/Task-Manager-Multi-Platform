using System.Globalization;
using System.Resources;

namespace TaskManager.Desktop.Properties;

public static class Strings
{
    private static readonly ResourceManager ResourceManager = new(
        "TaskManager.Desktop.Properties.Strings",
        typeof(Strings).Assembly);

    public static string TaskManager => Get(nameof(TaskManager));
    public static string DesktopSubtitle => Get(nameof(DesktopSubtitle));
    public static string SignIn => Get(nameof(SignIn));
    public static string ApiBaseUrl => Get(nameof(ApiBaseUrl));
    public static string ApiUrlToolTip => Get(nameof(ApiUrlToolTip));
    public static string Email => Get(nameof(Email));
    public static string Name => Get(nameof(Name));
    public static string Password => Get(nameof(Password));
    public static string ConfirmPassword => Get(nameof(ConfirmPassword));
    public static string LogOut => Get(nameof(LogOut));
    public static string Refresh => Get(nameof(Refresh));
    public static string MyTasks => Get(nameof(MyTasks));
    public static string NewTask => Get(nameof(NewTask));
    public static string Title => Get(nameof(Title));
    public static string Description => Get(nameof(Description));
    public static string SaveTask => Get(nameof(SaveTask));
    public static string Delete => Get(nameof(Delete));
    public static string ChooseOrCreateTask => Get(nameof(ChooseOrCreateTask));
    public static string Completed => Get(nameof(Completed));
    public static string Pending => Get(nameof(Pending));
    public static string MarkComplete => Get(nameof(MarkComplete));
    public static string MarkPending => Get(nameof(MarkPending));
    public static string SignInWithApi => Get(nameof(SignInWithApi));
    public static string WelcomeMessage => Get(nameof(WelcomeMessage));
    public static string TaskDetails => Get(nameof(TaskDetails));
    public static string CredentialsRequired => Get(nameof(CredentialsRequired));
    public static string LoadingTasks => Get(nameof(LoadingTasks));
    public static string SignedOut => Get(nameof(SignedOut));
    public static string LogoutApiError => Get(nameof(LogoutApiError));
    public static string TaskCount => Get(nameof(TaskCount));
    public static string TitleRequired => Get(nameof(TitleRequired));
    public static string TaskLengthLimit => Get(nameof(TaskLengthLimit));
    public static string TaskSaved => Get(nameof(TaskSaved));
    public static string TaskCompleted => Get(nameof(TaskCompleted));
    public static string TaskMarkedPending => Get(nameof(TaskMarkedPending));
    public static string TaskDeleted => Get(nameof(TaskDeleted));
    public static string SessionExpired => Get(nameof(SessionExpired));
    public static string PermissionDenied => Get(nameof(PermissionDenied));
    public static string TaskNotFound => Get(nameof(TaskNotFound));
    public static string ApiUnavailable => Get(nameof(ApiUnavailable));
    public static string ApiTimedOut => Get(nameof(ApiTimedOut));
    public static string UnexpectedError => Get(nameof(UnexpectedError));
    public static string EmptySignInResponse => Get(nameof(EmptySignInResponse));
    public static string EmptyTaskResponse => Get(nameof(EmptyTaskResponse));
    public static string InvalidApiUrl => Get(nameof(InvalidApiUrl));
    public static string ApiRequestFailed => Get(nameof(ApiRequestFailed));
    public static string DeviceName => Get(nameof(DeviceName));
    public static string TaskStatus => Get(nameof(TaskStatus));
    public static string Field => Get(nameof(Field));
    public static string Manager => Get(nameof(Manager));
    public static string Worker => Get(nameof(Worker));
    public static string UserManagement => Get(nameof(UserManagement));
    public static string ShowTasks => Get(nameof(ShowTasks));
    public static string AddUser => Get(nameof(AddUser));
    public static string SaveUser => Get(nameof(SaveUser));
    public static string UserDetails => Get(nameof(UserDetails));
    public static string UserRole => Get(nameof(UserRole));
    public static string ChangePasswordOptional => Get(nameof(ChangePasswordOptional));
    public static string SelectUser => Get(nameof(SelectUser));
    public static string UserCreated => Get(nameof(UserCreated));
    public static string UserSaved => Get(nameof(UserSaved));
    public static string EmptyUserResponse => Get(nameof(EmptyUserResponse));
    public static string UserCount => Get(nameof(UserCount));
    public static string UserFieldsRequired => Get(nameof(UserFieldsRequired));
    public static string UserPasswordRequired => Get(nameof(UserPasswordRequired));

    private static string Get(string name) =>
        ResourceManager.GetString(name, CultureInfo.CurrentUICulture)
        ?? throw new MissingManifestResourceException($"Missing Arabic UI resource '{name}'.");
}
