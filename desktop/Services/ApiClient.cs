using System.Net;
using System.Net.Http.Headers;
using System.Net.Http.Json;
using System.Net.Http;
using System.Globalization;
using System.Text.Json;
using System.Text.Json.Serialization;
using TaskManager.Desktop.Models;
using TaskManager.Desktop.Properties;

namespace TaskManager.Desktop.Services;

public sealed class ApiClient
{
    private static readonly HttpClient HttpClient = new() { Timeout = TimeSpan.FromSeconds(30) };
    private static readonly JsonSerializerOptions JsonOptions = new(JsonSerializerDefaults.Web)
    {
        PropertyNameCaseInsensitive = true,
        NumberHandling = JsonNumberHandling.AllowReadingFromString
    };

    public async Task<AuthResponse> LoginAsync(string baseUrl, string email, string password)
    {
        var response = await SendAsync<AuthResponse>(
            HttpMethod.Post, baseUrl, "login", null,
            new { email, password, device_name = "Task Manager Desktop" });
        return response ?? throw new ApiException(Strings.EmptySignInResponse);
    }

    public async Task LogoutAsync(string baseUrl, string token) =>
        await SendAsync<object>(HttpMethod.Post, baseUrl, "logout", token, new { });

    public async Task<IReadOnlyList<TaskItem>> GetTasksAsync(string baseUrl, string token) =>
        await SendAsync<List<TaskItem>>(HttpMethod.Get, baseUrl, "tasks", token)
        ?? [];

    public async Task<IReadOnlyList<UserItem>> GetUsersAsync(string baseUrl, string token) =>
        await SendAsync<List<UserItem>>(HttpMethod.Get, baseUrl, "users", token)
        ?? [];

    public async Task<UserItem> CreateUserAsync(
        string baseUrl, string token, string name, string email, string password, string role)
    {
        var user = await SendAsync<UserItem>(
            HttpMethod.Post, baseUrl, "users", token,
            new { name, email, password, password_confirmation = password, role });
        return user ?? throw new ApiException(Strings.EmptyUserResponse);
    }

    public async Task<UserItem> UpdateUserAsync(
        string baseUrl, string token, int id, string name, string email, string? password, string role)
    {
        var payload = new Dictionary<string, object?> { ["name"] = name, ["email"] = email, ["role"] = role };
        if (!string.IsNullOrWhiteSpace(password))
        {
            payload["password"] = password;
            payload["password_confirmation"] = password;
        }

        var user = await SendAsync<UserItem>(HttpMethod.Put, baseUrl, $"users/{id}", token, payload);
        return user ?? throw new ApiException(Strings.EmptyUserResponse);
    }

    public async Task<TaskItem> CreateTaskAsync(
        string baseUrl, string token, string title, string? description, int? userId)
    {
        var payload = new Dictionary<string, object?> { ["title"] = title, ["description"] = description };
        if (userId.HasValue)
        {
            payload["user_id"] = userId.Value;
        }

        var task = await SendAsync<TaskItem>(
            HttpMethod.Post, baseUrl, "tasks", token, payload);
        return task ?? throw new ApiException(Strings.EmptyTaskResponse);
    }

    public async Task<TaskItem> UpdateTaskAsync(
        string baseUrl, string token, int id, string title, string? description)
    {
        var task = await SendAsync<TaskItem>(
            HttpMethod.Put, baseUrl, $"tasks/{id}", token, new { title, description });
        return task ?? throw new ApiException(Strings.EmptyTaskResponse);
    }

    public async Task<TaskItem> ToggleCompletionAsync(string baseUrl, string token, int id)
    {
        var task = await SendAsync<TaskItem>(
            HttpMethod.Patch, baseUrl, $"tasks/{id}/complete", token);
        return task ?? throw new ApiException(Strings.EmptyTaskResponse);
    }

    public async Task DeleteTaskAsync(string baseUrl, string token, int id) =>
        await SendAsync<object>(HttpMethod.Delete, baseUrl, $"tasks/{id}", token);

    private static async Task<T?> SendAsync<T>(
        HttpMethod method,
        string baseUrl,
        string endpoint,
        string? token,
        object? payload = null)
    {
        using var request = new HttpRequestMessage(method, BuildEndpoint(baseUrl, endpoint));
        if (token is not null)
        {
            request.Headers.Authorization = new AuthenticationHeaderValue("Bearer", token);
        }

        if (payload is not null)
        {
            request.Content = JsonContent.Create(payload);
        }

        using var response = await HttpClient.SendAsync(request);
        if (!response.IsSuccessStatusCode)
        {
            var content = await response.Content.ReadAsStringAsync();
            throw new ApiException(FormatError(content, response.StatusCode), response.StatusCode);
        }

        if (response.StatusCode == HttpStatusCode.NoContent || typeof(T) == typeof(object))
        {
            return default;
        }

        return await response.Content.ReadFromJsonAsync<T>(JsonOptions);
    }

    private static Uri BuildEndpoint(string baseUrl, string endpoint)
    {
        if (!Uri.TryCreate(baseUrl?.Trim(), UriKind.Absolute, out var root)
            || (root.Scheme != Uri.UriSchemeHttp && root.Scheme != Uri.UriSchemeHttps))
        {
            throw new ApiException(Strings.InvalidApiUrl);
        }

        var path = root.AbsolutePath.TrimEnd('/');
        var apiRoot = path.EndsWith("/api/v1", StringComparison.OrdinalIgnoreCase)
            || string.Equals(path, "api/v1", StringComparison.OrdinalIgnoreCase)
            ? root.ToString().TrimEnd('/') + "/"
            : root.ToString().TrimEnd('/') + "/api/v1/";

        return new Uri(new Uri(apiRoot, UriKind.Absolute), endpoint);
    }

    private static string FormatError(string content, HttpStatusCode statusCode)
    {
        try
        {
            using var document = JsonDocument.Parse(content);
            var root = document.RootElement;
            var messages = new List<string>();
            if (root.TryGetProperty("errors", out var errors) && errors.ValueKind == JsonValueKind.Object)
            {
                foreach (var field in errors.EnumerateObject())
                {
                    if (field.Value.ValueKind == JsonValueKind.Array)
                    {
                        messages.AddRange(field.Value.EnumerateArray()
                            .Where(value => value.ValueKind == JsonValueKind.String)
                            .Select(value => $"{GetFieldLabel(field.Name)}: {value.GetString()}"));
                    }
                }
            }

            if (messages.Count > 0)
            {
                return string.Join(Environment.NewLine, messages.Distinct());
            }

            if (root.TryGetProperty("message", out var message) && message.ValueKind == JsonValueKind.String)
            {
                return message.GetString()!;
            }
        }
        catch (JsonException)
        {
            return string.Format(
                CultureInfo.CurrentUICulture,
                Strings.ApiRequestFailed,
                (int)statusCode);
        }

        return string.Format(
            CultureInfo.CurrentUICulture,
            Strings.ApiRequestFailed,
            (int)statusCode);
    }

    private static string GetFieldLabel(string fieldName) =>
        fieldName switch
        {
            "name" => Strings.Name,
            "email" => Strings.Email,
            "password" => Strings.Password,
            "password_confirmation" => Strings.ConfirmPassword,
            "title" => Strings.Title,
            "description" => Strings.Description,
            "device_name" => Strings.DeviceName,
            "is_completed" => Strings.TaskStatus,
            _ => Strings.Field
        };

    public sealed class AuthResponse
    {
        [JsonPropertyName("token")]
        public string Token { get; init; } = string.Empty;

        [JsonPropertyName("user")]
        public UserResponse User { get; init; } = new();
    }

    public sealed class UserResponse
    {
        [JsonPropertyName("name")]
        public string Name { get; init; } = string.Empty;

        [JsonPropertyName("role")]
        public string Role { get; init; } = "worker";
    }
}

public sealed class ApiException(string message, HttpStatusCode? statusCode = null) : Exception(message)
{
    public HttpStatusCode? StatusCode { get; } = statusCode;
}
