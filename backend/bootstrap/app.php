<?php

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
        $exceptions->render(function (AuthorizationException|AccessDeniedHttpException $exception, Request $request) {
            $message = __('You do not have permission to perform this action.');

            return $request->is('api/*') || $request->expectsJson()
                ? response()->json(['message' => $message], 403)
                : response()->view('errors.403', ['message' => $message], 403);
        });
    })
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectUsersTo('/tasks');
        $middleware->redirectGuestsTo(
            fn (Request $request) => $request->is('api/*') ? null : '/login',
        );
    })
    ->create();
