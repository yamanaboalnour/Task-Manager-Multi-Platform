using System.Windows;

namespace TaskManager.Desktop;

public partial class MainWindow : Window
{
    private readonly MainViewModel _viewModel;

    public MainWindow()
    {
        InitializeComponent();
        _viewModel = new MainViewModel();
        DataContext = _viewModel;
        _viewModel.PropertyChanged += (_, args) =>
        {
            if (args.PropertyName == nameof(MainViewModel.IsAuthenticated))
            {
                LoginPanel.Visibility = _viewModel.IsAuthenticated ? Visibility.Collapsed : Visibility.Visible;
                WorkspacePanel.Visibility = _viewModel.IsAuthenticated ? Visibility.Visible : Visibility.Collapsed;
                if (!_viewModel.IsAuthenticated)
                {
                    LoginPassword.Clear();
                }
            }
            else if (args.PropertyName == nameof(MainViewModel.UserEditorPassword)
                     && string.IsNullOrEmpty(_viewModel.UserEditorPassword))
            {
                UserPassword.Clear();
            }
            else if (args.PropertyName == nameof(MainViewModel.SelectedUser))
            {
                UserPassword.Clear();
            }
            else if (args.PropertyName == nameof(MainViewModel.Password)
                     && string.IsNullOrEmpty(_viewModel.Password))
            {
                LoginPassword.Clear();
            }
            else if (args.PropertyName == nameof(MainViewModel.PasswordConfirmation)
                     && string.IsNullOrEmpty(_viewModel.PasswordConfirmation))
            {
                ConfirmRegistrationPassword.Clear();
            }
        };
    }

    private void LoginPassword_OnPasswordChanged(object sender, RoutedEventArgs e)
    {
        _viewModel.Password = LoginPassword.Password;
    }

    private void ConfirmRegistrationPassword_OnPasswordChanged(object sender, RoutedEventArgs e)
    {
        _viewModel.PasswordConfirmation = ConfirmRegistrationPassword.Password;
    }

    private void UserPassword_OnPasswordChanged(object sender, RoutedEventArgs e)
    {
        _viewModel.UserEditorPassword = UserPassword.Password;
    }
}
