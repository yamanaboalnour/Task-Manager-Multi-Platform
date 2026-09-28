# WPF desktop client

This .NET 8 WPF application talks only to the Laravel HTTP API; it has no SQL Server or database connection.

From the repository root, run:

```powershell
dotnet run --project .\desktop\TaskManager.Desktop.csproj
```

Enter the Laravel server root as the API base URL (the default `http://127.0.0.1:8000` works with `php artisan serve`). The client appends `/api/v1` automatically; a base URL that already ends in `/api/v1` is also accepted. Sign in with an account created through the API. The URL is saved in the current user's Local AppData; the bearer token remains in memory and is cleared on logout or when the app exits.

After signing in, the client can list, create, edit, complete/uncomplete, and delete tasks. It sends JSON requests to the versioned API and surfaces server validation and connection errors in the UI.
