using System.IO;
using System.Text.Json;

namespace TaskManager.Desktop.Services;

public sealed class SettingsStore
{
    private static readonly string SettingsPath = Path.Combine(
        Environment.GetFolderPath(Environment.SpecialFolder.LocalApplicationData),
        "TaskManagerDesktop",
        "settings.json");

    public string LoadApiBaseUrl()
    {
        try
        {
            if (File.Exists(SettingsPath))
            {
                var settings = JsonSerializer.Deserialize<Settings>(File.ReadAllText(SettingsPath));
                if (!string.IsNullOrWhiteSpace(settings?.ApiBaseUrl))
                {
                    return settings.ApiBaseUrl;
                }
            }
        }
        catch (IOException)
        {
        }
        catch (JsonException)
        {
        }

        return "http://127.0.0.1:8000";
    }

    public void SaveApiBaseUrl(string apiBaseUrl)
    {
        var directory = Path.GetDirectoryName(SettingsPath)!;
        Directory.CreateDirectory(directory);
        File.WriteAllText(SettingsPath, JsonSerializer.Serialize(new Settings(apiBaseUrl)));
    }

    private sealed record Settings(string ApiBaseUrl);
}
