<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Keep your existing middleware configurations here
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Force all API exceptions to return a standardized JSON response instead of HTML
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('api/*')) {
                // Handle 404 Not Found exceptions
                if ($e instanceof NotFoundHttpException) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Endpoint or resource not found',
                        'data' => null
                    ], 404);
                }

                // Handle 500 Internal Server Error and other exceptions
                return response()->json([
                    'success' => false,
                    'message' => 'Internal Server Error: ' . $e->getMessage(),
                    'data' => null
                ], 500);
            }
        });
    })->create();