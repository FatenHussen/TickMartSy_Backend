<?php

use App\Exceptions\Handler;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\HandleCors;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->use([
            SetLocale::class,
            HandleCors::class,
        ]);

        $middleware->alias([
            'setLocale' => SetLocale::class,
            'abilities' => \Laravel\Sanctum\Http\Middleware\CheckAbilities::class,
            'ability' => \Laravel\Sanctum\Http\Middleware\CheckForAnyAbility::class,
            'check.blocked' => \App\Http\Middleware\CheckIfBlocked::class,
            'api' => \App\Http\Middleware\AttachTokenFromCookie::class,
            'crud.permission' => \App\Http\Middleware\CrudPermissionMiddleware::class,

        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        app(Handler::class)->register($exceptions);
    })->create();
