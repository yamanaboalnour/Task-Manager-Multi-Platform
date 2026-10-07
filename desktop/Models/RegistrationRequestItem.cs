using System.Text.Json.Serialization;

namespace TaskManager.Desktop.Models;

public sealed class RegistrationRequestItem
{
    [JsonPropertyName("id")]
    public int Id { get; init; }

    [JsonPropertyName("first_name")]
    public string FirstName { get; init; } = string.Empty;

    [JsonPropertyName("last_name")]
    public string LastName { get; init; } = string.Empty;

    [JsonPropertyName("email")]
    public string Email { get; init; } = string.Empty;

    [JsonPropertyName("status")]
    public string Status { get; init; } = string.Empty;

    [JsonIgnore]
    public string FullName => $"{FirstName} {LastName}".Trim();

    [JsonIgnore]
    public string StatusLabel => Status switch
    {
        "pending" => Properties.Strings.RequestPending,
        "approved" => Properties.Strings.RequestApproved,
        "rejected" => Properties.Strings.RequestRejected,
        _ => Status
    };
}
