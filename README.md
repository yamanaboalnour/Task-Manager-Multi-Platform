# Task Manager Multi-Platform

A training monorepo for a multi-platform task management app built around a single Laravel backend and shared SQL Server database.

## Architecture

- Laravel API backend with Sanctum authentication
- Web UI served by Laravel Blade
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
- Laravel 11+
- .NET 8+
- Flutter 3.3+
- SQL Server 2022 / LocalDB / Express
- Git

## Phase 1 status

This repository is initialized and ready for backend scaffolding.

## Git workflow

```bash
git status
git add .
git commit -m "chore: initialize monorepo"
git push origin main
```
