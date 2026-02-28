<?php

use App\Enums\ApiCode;
use App\Http\Responses\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
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
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (ValidationException $e) {
            return ApiResponse::error(ApiCode::VALIDATION_ERROR, $e->getMessage(), 422, $e->errors());
        });

        $exceptions->render(function (NotFoundHttpException $e) {
            return ApiResponse::error(ApiCode::NOT_FOUND, $e->getMessage() ?: 'Resource not found.', 404);
        });

        $exceptions->render(function (AuthenticationException $e) {
            return ApiResponse::error(ApiCode::UNAUTHENTICATED, 'Unauthenticated.', 401);
        });
    })->create();
