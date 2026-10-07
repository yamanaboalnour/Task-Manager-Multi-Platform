<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', __('Task Manager')) · {{ __('Task Manager') }}</title>
    <style>
        :root { color-scheme: light; font-family: "Segoe UI", Tahoma, Arial, sans-serif; color: #18302a; background: #f3f6f3; }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; }
        a { color: #17664f; }
        .topbar { display: flex; justify-content: space-between; align-items: center; gap: 1rem; padding: 1rem max(1.25rem, calc((100vw - 1080px) / 2)); background: #fff; border-bottom: 1px solid #dce5df; }
        .brand { color: #18302a; text-decoration: none; font-weight: 750; font-size: 1.1rem; }
        .top-actions { display: flex; align-items: center; gap: 1rem; }
        .shell { width: min(1080px, calc(100% - 2rem)); margin: 2.5rem auto; }
        .auth-card, .panel, .task-card { background: #fff; border: 1px solid #dce5df; border-radius: 14px; box-shadow: 0 10px 28px #173b2c0b; }
        .auth-card { width: min(460px, 100%); margin: 5vh auto; padding: 2rem; }
        .panel { padding: 1.4rem; }
        h1 { margin: 0 0 .5rem; font-size: clamp(1.7rem, 4vw, 2.2rem); }
        h2 { margin: 0 0 1rem; font-size: 1.2rem; }
        .muted { color: #64756d; }
        label { display: block; margin: 1rem 0 .35rem; font-weight: 650; }
        input, textarea { width: 100%; padding: .75rem .85rem; border: 1px solid #bfcfc5; border-radius: 8px; font: inherit; background: #fff; }
        textarea { min-height: 92px; resize: vertical; }
        input:focus, textarea:focus { outline: 3px solid #1c765233; border-color: #1c7652; }
        button, .button { display: inline-flex; justify-content: center; align-items: center; gap: .4rem; border: 0; border-radius: 8px; padding: .7rem 1rem; background: #17664f; color: #fff; font: inherit; font-weight: 650; text-decoration: none; cursor: pointer; }
        button:hover, .button:hover { background: #10533e; }
        .button-secondary { background: #e8f0eb; color: #214438; }
        .button-danger { background: #fff0ed; color: #a32d20; }
        .button-small { padding: .48rem .7rem; font-size: .9rem; }
        .full { width: 100%; margin-top: 1.25rem; }
        .errors { margin: 1rem 0; padding: .8rem 1rem; background: #fff2f0; border: 1px solid #f0c4bd; border-radius: 8px; color: #922b20; }
        .errors ul { margin: .3rem 0 0; padding-inline-start: 1.2rem; }
        .notice { margin-bottom: 1rem; padding: .8rem 1rem; border: 1px solid #c5e4d0; border-radius: 8px; background: #effaf2; color: #205b39; }
        .auth-footer { margin-block-start: 1.25rem; text-align: center; }
        .page-heading { display: flex; justify-content: space-between; align-items: end; gap: 1rem; margin-bottom: 1.25rem; }
        .summary-grid, .user-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; }
        .summary-card { padding: 1.25rem; }
        .user-section { margin-top: 1.25rem; }
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: .75rem; align-items: end; }
        .form-grid label { margin-top: .5rem; }
        select { width: 100%; padding: .75rem .85rem; border: 1px solid #bfcfc5; border-radius: 8px; background: #fff; font: inherit; }
        .user-heading { display: flex; align-items: center; justify-content: space-between; gap: .5rem; margin-bottom: .6rem; }
        .task-grid { display: grid; grid-template-columns: minmax(260px, .85fr) minmax(0, 1.4fr); gap: 1rem; align-items: start; }
        .task-list { display: grid; gap: .8rem; }
        .task-card { padding: 1rem; }
        .task-title { margin: 0; font-size: 1.05rem; overflow-wrap: anywhere; }
        .task-description { margin: .45rem 0 .8rem; color: #64756d; white-space: pre-wrap; overflow-wrap: anywhere; }
        .task-meta { display: flex; justify-content: space-between; gap: .5rem; color: #64756d; font-size: .85rem; }
        .task-actions { display: flex; flex-wrap: wrap; gap: .45rem; margin-top: .9rem; }
        .task-actions form { margin: 0; }
        .empty { padding: 2rem 1rem; text-align: center; color: #64756d; }
        @media (max-width: 760px) { .task-grid { grid-template-columns: 1fr; } .page-heading { align-items: start; flex-direction: column; } .topbar { padding-inline: 1rem; } .shell { margin-top: 1.5rem; } }
    </style>
</head>
<body>
    <header class="topbar">
        <a class="brand" href="{{ auth()->check() ? (auth()->user()->isManager() ? route('dashboard') : route('tasks.index')) : route('login') }}">{{ __('Task Manager') }}</a>
        <nav class="top-actions" aria-label="{{ __('Main navigation') }}">
            @auth
                <span class="muted">{{ auth()->user()->name }}</span>
                <span class="muted">{{ auth()->user()->isManager() ? __('Manager') : __('Worker') }}</span>
                @if (auth()->user()->isManager())
                    <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
                    <a href="{{ route('users.index') }}">{{ __('User management') }}</a>
                    <a href="{{ route('registration-requests.index') }}">{{ __('Registration requests') }}</a>
                    <a href="{{ route('tasks.index') }}">{{ __('All tasks') }}</a>
                    <a href="{{ route('surveys.index') }}">{{ __('Surveys') }}</a>
                @else
                    <a href="{{ route('tasks.index') }}">{{ __('My tasks') }}</a>
                    <a href="{{ route('surveys.index') }}">{{ __('Surveys') }}</a>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="button-secondary" type="submit">{{ __('Log out') }}</button>
                </form>
            @else
                <a href="{{ route('login') }}">{{ __('Log in') }}</a>
                <a class="button button-small" href="{{ route('register') }}">{{ __('Request an account') }}</a>
            @endauth
        </nav>
    </header>
    <main class="shell">
        @if (session('status'))
            <div class="notice" role="status">{{ session('status') }}</div>
        @endif
        @yield('content')
    </main>
</body>
</html>
