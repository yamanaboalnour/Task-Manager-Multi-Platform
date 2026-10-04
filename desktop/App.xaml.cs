using System.Windows;
using System.Globalization;
using System.Threading;

namespace TaskManager.Desktop;

public partial class App : Application
{
    protected override void OnStartup(StartupEventArgs e)
    {
        var arabicCulture = CultureInfo.GetCultureInfo("ar-SA");
        CultureInfo.DefaultThreadCurrentCulture = arabicCulture;
        CultureInfo.DefaultThreadCurrentUICulture = arabicCulture;
        Thread.CurrentThread.CurrentCulture = arabicCulture;
        Thread.CurrentThread.CurrentUICulture = arabicCulture;

        base.OnStartup(e);
    }
}
