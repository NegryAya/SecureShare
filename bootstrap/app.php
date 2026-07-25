<?php

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
        // Le groupe "web" (session, csrf, cookies chiffres...) est deja
        // applique par defaut par Laravel aux routes de routes/web.php.
        // On y ajoute nos middlewares personnalises si besoin dans les sprints suivants.
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
