<?php

use App\Exceptions\ApiException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(fn (): ?string => null);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(function (Request $request): bool {
            return $request->is('api/*') || $request->expectsJson();
        });

        $renderApiError = function (
            string $message,
            int $statusCode,
            mixed $errors = null,
            array $meta = []
        ) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'data' => null,
                'errors' => $errors,
                'meta' => array_merge([
                    'timestamp' => now()->toIso8601String(),
                    'status_code' => $statusCode,
                ], $meta),
            ], $statusCode);
        };

        $exceptions->render(function (AuthenticationException $exception, Request $request) use ($renderApiError) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return $renderApiError('Unauthorized', 401);
            }
        });

        $exceptions->render(function (ApiException $exception, Request $request) use ($renderApiError) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return $renderApiError(
                    $exception->getMessage(),
                    $exception->statusCode(),
                    $exception->errors(),
                    $exception->meta()
                );
            }
        });

        $exceptions->render(function (AuthorizationException $exception, Request $request) use ($renderApiError) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return $renderApiError(
                    $exception->getMessage() ?: 'Forbidden',
                    $exception->status() ?? 403
                );
            }
        });

        $exceptions->render(function (ValidationException $exception, Request $request) use ($renderApiError) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return $renderApiError(
                    $exception->getMessage() ?: 'Validation failed',
                    $exception->status,
                    $exception->errors()
                );
            }
        });

        $exceptions->render(function (ModelNotFoundException $exception, Request $request) use ($renderApiError) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return $renderApiError('Resource not found', 404);
            }
        });

        $exceptions->render(function (NotFoundHttpException $exception, Request $request) use ($renderApiError) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return $renderApiError('Resource not found', 404);
            }
        });

        $exceptions->render(function (\Throwable $exception, Request $request) use ($renderApiError) {
            if ($request->is('api/*') || $request->expectsJson()) {
                Log::error('Unhandled API exception', [
                    'message' => $exception->getMessage(),
                    'exception' => get_class($exception),
                    'path' => $request->path(),
                    'method' => $request->method(),
                ]);

                return $renderApiError('Internal server error', 500);
            }
        });
    })->create();
