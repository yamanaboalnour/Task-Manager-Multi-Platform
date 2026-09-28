# Flutter mobile client

The app talks only to the Laravel API. On first launch, set **API base URL** to
the server origin (for example, `https://tasks.example.com`) without the
`/api/v1` suffix. The Android emulator defaults to `http://10.0.2.2:8000`.
For a physical device, use a host address reachable from that device.

Set up and run from this directory:

```powershell
flutter pub get
flutter run
```

The base URL and Sanctum token are stored with platform secure storage. Debug
builds allow HTTP for local development; use HTTPS for release deployments.
