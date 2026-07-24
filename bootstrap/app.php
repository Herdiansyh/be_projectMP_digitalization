<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\CheckPermission;
use App\Http\Middleware\ManpowerAccessMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php', 
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            // 'role' => \App\Http\Middleware\CheckRole::class,
            'admin' => AdminMiddleware::class,
            'manpower' => ManpowerAccessMiddleware::class,
            'permission' => CheckPermission::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'print/manpower/bulk',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();