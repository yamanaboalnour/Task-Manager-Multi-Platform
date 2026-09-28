# Task Manager Multi-Platform

A training monorepo for a multi-platform task management app built around a single Laravel backend and shared SQL Server database.

## Architecture

- Laravel API backend with Sanctum authentication
- Web UI served by Laravel Blade with Laravel session authentication
- WPF desktop client for Windows
- Flutter mobile client
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

The Laravel Blade Web UI is available at `/register`, `/login`, and `/tasks`. It uses Laravel sessions, CSRF protection, and the same shared authentication, validation, task service, and ownership policy as the API.

Register and login are public API routes. All other API endpoints require a Sanctum bearer token:

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

## Current phase status

The Laravel Web UI and API are configured for SQL Server LocalDB. WPF and Flutter use the API and do not connect to SQL Server directly.

Run the backend feature tests from `backend/` with `php artisan test`. They use an in-memory SQLite test database; SQL Server connection, migrations, and HTTP API behavior have also been checked against LocalDB.

## Git workflow

```bash
git status
git add .
git commit -m "chore: initialize monorepo"
git push origin main
```
