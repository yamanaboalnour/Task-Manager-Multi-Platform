using System.ComponentModel;
using System.Runtime.CompilerServices;
using System.Text.Json.Serialization;

namespace TaskManager.Desktop.Models;

public sealed class TaskItem : INotifyPropertyChanged
{
    private string _title = string.Empty;
    private string? _description;
    private bool _isCompleted;

    [JsonPropertyName("id")]
    public int Id { get; init; }

    [JsonPropertyName("user_id")]
    public int UserId { get; init; }

    [JsonPropertyName("user")]
    public TaskOwner? User { get; init; }

    [JsonIgnore]
    public string OwnerName => User?.Name ?? string.Empty;

    [JsonPropertyName("title")]
    public string Title
    {
        get => _title;
        set => SetField(ref _title, value);
    }

    public sealed class TaskOwner
    {
        [JsonPropertyName("id")]
        public int Id { get; init; }

        [JsonPropertyName("name")]
        public string Name { get; init; } = string.Empty;
    }

    [JsonPropertyName("description")]
    public string? Description
    {
        get => _description;
        set => SetField(ref _description, value);
    }

    [JsonPropertyName("is_completed")]
    public bool IsCompleted
    {
        get => _isCompleted;
        set
        {
            if (SetField(ref _isCompleted, value))
            {
                OnPropertyChanged(nameof(CompletionLabel));
            }
        }
    }

    [JsonIgnore]
    public string CompletionLabel => IsCompleted ? Properties.Strings.Completed : Properties.Strings.Pending;

    public event PropertyChangedEventHandler? PropertyChanged;

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
