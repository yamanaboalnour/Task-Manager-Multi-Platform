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

The Laravel Blade Web UI is available at `/register`, `/login`, `/tasks`, and `/surveys`. It uses Laravel sessions, CSRF protection, and the same shared authentication, validation, task service, and ownership policy as the API.

Public registration submits a pending request; it does not create an account or issue an authentication token. A manager reviews requests at `/registration-requests`; approved accounts are created as workers. Manager-created accounts and role changes are available at `/users`. To bootstrap the first manager on an existing database, first create or identify the account, then run `php artisan users:make-manager <email>` from `backend/` in a trusted local/administrative shell. The command does not create an account or expose a public manager-registration path. Managers can promote/demote users in the application; the final manager cannot be demoted.

Password-reset links are sent through Laravel's configured mail transport. Configure `MAIL_*` in the local environment for delivery; Laravel's default `log` mailer writes the notification to the application log and does not deliver external email.

Authentication and account endpoints:

| Method | Endpoint | Purpose |
|---|---|---|
| POST | `/api/v1/register-request` | Submit a pending account request |
| POST | `/api/v1/login` | Sign in and issue an API token |
| POST | `/api/v1/logout` | Revoke the current token |
| GET | `/api/v1/me` | Return the current user |
| POST | `/api/v1/forgot-password` | Request a password-reset link |
| POST | `/api/v1/reset-password` | Reset password using the emailed token |

All task, user-management, registration-review, and survey endpoints require a Sanctum bearer token:

| Method | Endpoint | Purpose |
|---|---|---|
| GET, POST | `/api/v1/tasks` | List or create tasks (workers: own tasks; managers: all tasks) |
| GET, PUT, DELETE | `/api/v1/tasks/{task}` | Read, update, or delete a task subject to role/ownership |
| PATCH | `/api/v1/tasks/{task}/complete` | Toggle task completion subject to role/ownership |
| GET, POST | `/api/v1/users` | Manager-only user listing and account creation |
| GET, PUT, PATCH | `/api/v1/users/{user}` | Manager-only user details and account/role updates |
| GET | `/api/v1/registration-requests` | Manager-only account-request queue |
| POST | `/api/v1/registration-requests/{id}/approve` | Approve a request and create a worker |
| POST | `/api/v1/registration-requests/{id}/reject` | Reject a pending request |
| GET, POST | `/api/v1/surveys` | List surveys or create a manager draft |
| GET, PUT, DELETE | `/api/v1/surveys/{id}` | Read, update, or delete an authorized draft |
| POST | `/api/v1/surveys/{id}/publish` | Publish a manager survey |
| POST | `/api/v1/surveys/{id}/responses` | Submit one response to a published survey |
| GET | `/api/v1/surveys/{id}/responses` | Manager-only survey results and summary |

Laravel Policies enforce role and ownership regardless of client-side controls. Managers can review sign-up requests and create, publish, and review surveys. Workers can view published surveys and submit a response once per survey. Published surveys cannot be edited or deleted.

The WPF client supports account requests, login, password-reset email requests, task management, manager user administration, and approval/rejection of account requests. Flutter additionally includes the survey builder, response screens, and results view.

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

Run the backend feature tests from `backend/` with `php artisan test`. They use an in-memory SQLite test database. Run `php artisan migrate` after configuring the target SQL Server database before using the new account-request and survey features.

## Git workflow

```bash
git status
git add .
git commit -m "chore: initialize monorepo"
git push origin main
```
