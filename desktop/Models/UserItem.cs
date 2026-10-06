using System.Text.Json.Serialization;

namespace TaskManager.Desktop.Models;

public sealed class UserItem
{
    [JsonPropertyName("id")]
    public int Id { get; init; }

    [JsonPropertyName("name")]
    public string Name { get; init; } = string.Empty;

    [JsonPropertyName("email")]
    public string Email { get; init; } = string.Empty;

    [JsonPropertyName("role")]
    public string Role { get; init; } = "worker";

    [JsonIgnore]
    public string RoleLabel => Role == "manager" ? Properties.Strings.Manager : Properties.Strings.Worker;

    [JsonPropertyName("tasks_count")]
    public int TasksCount { get; init; }
}
