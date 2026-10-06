# Task Manager Multi-Platform

A training monorepo for a multi-platform task management app built around a single Laravel backend and shared SQL Server database.

## Architecture

- Laravel API backend with Sanctum authentication
- Arabic Laravel Blade Web UI with RTL and Laravel session authentication
- WPF desktop client for Windows
- Arabic Flutter mobile client with RTL
- SQL Server as the shared database

## Repository Structure

```text
Task-Manager-Multi-Platform/
├── backend/
├── desktop/
├── mobile/
├── docs/
├── .gitignore
├── README.md
├── LICENSE
└── .github/
```

## Requirements

- PHP 8.3+
- Composer
- Laravel 13
- .NET 8+
- Flutter 3.3+
- SQL Server 2022+ / LocalDB / Express
- Microsoft PHP `sqlsrv` and `pdo_sqlsrv` extensions
- Git

## Backend setup

1. Install SQL Server LocalDB or SQL Server, the Microsoft ODBC driver, and the PHP SQL Server extensions.
2. Copy `backend/.env.example` to `backend/.env`, then set `APP_KEY` by running `php artisan key:generate` from `backend/`.
3. Create the configured database if it does not exist. For LocalDB:

   ```powershell
   sqlcmd -S "(localdb)\MSSQLLocalDB" -Q "IF DB_ID(N'task_manager') IS NULL CREATE DATABASE [task_manager];"
   ```

4. Run `php artisan migrate` from `backend/`.
5. Start the Laravel web/API app with `php artisan serve`.

The LocalDB connection uses `DB_PORT=null`; LocalDB is a named instance and must not have Laravel append the usual SQL Server TCP port (`1433`). For a regular SQL Server host, set `DB_HOST`, `DB_PORT`, database credentials, and encryption settings to match that server.

## Web and API

Arabic (`ar`) is the default interface language. The Blade UI uses right-to-left document direction, while email/password and technical values retain left-to-right direction where appropriate.

The Laravel Blade Web UI is available at `/register`, `/login`, and `/tasks`. It uses Laravel sessions, CSRF protection, and the same shared authentication, validation, task service, and ownership policy as the API.

All public registrations receive the `worker` role; role values submitted during registration are ignored. Managers can create and manage accounts through `/users` and see all tasks grouped by owner through `/tasks`. To bootstrap the first manager on an existing database, first create or identify the account, then run `php artisan users:make-manager <email>` from `backend/` in a trusted local/administrative shell. The command does not create an account or expose a public manager-registration path. Managers can promote/demote users in the application; the final manager cannot be demoted.

Register and login are public. All other endpoints require a Sanctum bearer token:

| Method | Endpoint | Purpose |
|---|---|---|
| POST | `/api/v1/register` | Register and issue an API token |
| POST | `/api/v1/login` | Sign in and issue an API token |
| POST | `/api/v1/logout` | Revoke the current token |
| GET | `/api/v1/me` | Return the current user |
| GET, POST | `/api/v1/tasks` | List or create the current user's tasks |
| GET, PUT, DELETE | `/api/v1/tasks/{task}` | Read, update, or delete an owned task |
| PATCH | `/api/v1/tasks/{task}/complete` | Toggle task completion |
| GET, POST | `/api/v1/users` | Manager-only user listing and account creation |
| GET, PUT, PATCH | `/api/v1/users/{user}` | Manager-only user details and account/role updates |

New public registrations are workers. Task endpoints return only the authenticated worker's tasks; managers see all tasks and may assign newly created tasks to any user. Laravel Policies enforce task ownership and manager-only user administration regardless of client-side controls.

## Run the clients

Run the WPF desktop client on Windows:

```powershell
dotnet run --project desktop\TaskManager.Desktop.csproj
```

Run the Flutter mobile client:

```powershell
cd mobile
flutter pub get
flutter run
```

For Android Emulator networking, the default API base URL is `http://10.0.2.2:8000`. Use the host's reachable address when running on a physical device.

## Localization

Arabic is the default UI language across Web, WPF, and Flutter. See [docs/localization.md](docs/localization.md) for how to maintain translations and RTL behavior.

## Current phase status

The Laravel Web UI and API use SQL Server LocalDB. WPF and Flutter communicate only through the Laravel API and do not connect to SQL Server directly.

Run the backend feature tests from `backend/` with `php artisan test`. They use an in-memory SQLite test database; SQL Server connection, migrations, and HTTP API behavior have also been checked against LocalDB.

## Git workflow

```bash
git status
git add .
git commit -m "chore: initialize monorepo"
git push origin main
```
