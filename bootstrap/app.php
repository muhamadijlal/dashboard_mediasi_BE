<?php

use App\Http\Middleware\AuthenticatedMiddleware;
use App\Http\Middleware\RevalidateBackHistory;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'authenticated' => AuthenticatedMiddleware::class,
            'revalidateHistory' => RevalidateBackHistory::class
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
