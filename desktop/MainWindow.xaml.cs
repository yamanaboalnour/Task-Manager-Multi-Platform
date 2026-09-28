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
        };
    }

    private void LoginPassword_OnPasswordChanged(object sender, RoutedEventArgs e)
    {
        _viewModel.Password = LoginPassword.Password;
    }
}
