<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web:[
            __DIR__.'/../routes/web.php',
            __DIR__.'/../routes/user.php',
            ],
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->redirectGuestsTo(function (Request $request){
            return route('auth.login-form');
        });
        $middleware->alias([
            'authorized'=>\App\Http\Middleware\Authorized::class
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
