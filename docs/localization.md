# Localization and RTL

Arabic (`ar`) is the default interface language on all clients. Keep API routes,
JSON property names, database identifiers, and model properties unchanged when
translating user-facing text.

## Laravel Web and API

- The application locale and fallback locale are configured in
  `backend/config/app.php`; new environment files default both to `ar`.
- Add Web/API phrases to `backend/lang/ar.json`. Use Laravel's `__()` or
  `trans_choice()` helpers in controllers and Blade templates rather than
  embedding user-facing text.
- Validation messages and field labels belong in
  `backend/lang/ar/validation.php`; authentication messages belong in
  `backend/lang/ar/auth.php`.
- The shared Blade layout sets `lang="ar"` and `dir="rtl"`. Keep technical
  values such as email addresses and passwords explicitly left-to-right.
- Run `php artisan test` from `backend/` after changing translations.

## WPF desktop

- Add or update Arabic UI strings in `desktop/Properties/Strings.resx`.
- Expose new resource values in `desktop/Properties/Strings.cs` and reference
  them from XAML or view models; avoid UI text literals in application logic.
- The main window uses right-to-left flow. API URLs, email addresses, and
  passwords remain left-to-right.
- Build with `dotnet build desktop/TaskManager.Desktop.csproj`.

## Flutter mobile

- Add Arabic text to `mobile/lib/l10n/app_ar.arb`, preserving message keys and
  placeholder declarations.
- Regenerate the typed localization API from `mobile/` with
  `flutter gen-l10n`. Use `AppLocalizations.of(context)` in widgets and the
  generated Arabic localization in non-widget layers.
- Keep form layout directional and set technical input values, email
  addresses, and passwords to left-to-right where needed.
- Run `flutter analyze` and `flutter test` from `mobile/`.

## Running the clients

- Start the Laravel server from `backend/` with `php artisan serve`.
- Start WPF on Windows with
  `dotnet run --project desktop/TaskManager.Desktop.csproj`.
- Start Flutter with `flutter run` from `mobile/`. The Android Emulator's
  default API host is `10.0.2.2`; physical devices need a reachable host URL.
