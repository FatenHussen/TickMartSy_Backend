<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

class Handler extends ExceptionHandler
{
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {});
    }


    public function render($request, Throwable $e)
    {
        Log::info("Hello Handler");

        // Handle custom exceptions (extend BaseException)
        if ($e instanceof BaseException) {
            return $e->render();
        }

        // Handle validation exceptions (422)
        if ($e instanceof ValidationException) {
            return response()->json([
                'status'  => false,
                'message' => __('custom.ValidationError'),
                'errors'  => $e->errors(),
            ], 422);
        }

        // Handle model not found (404)
        if ($e instanceof ModelNotFoundException) {
            return response()->json([
                'status'  => false,
                'message' => __('custom.errors.404'),
                'errors'  => [],
            ], 404);
        }

        // Handle authentication errors (401)
        if ($e instanceof AuthenticationException) {
            return response()->json([
                'status'  => false,
                'message' => __('custom.errors.401'),
                'errors'  => [],
            ], 401);
        }

        if ($e instanceof MethodNotAllowedHttpException) {
            return response()->json([
                'status' => false,
                'message' => __('custom.errors.405') ?? 'طريقة الطلب غير مسموحة. الطرق المسموحة: ' . implode(', ', $e->getAllowedMethods()),
                'errors' => [],
            ], 405);
        }

        // Handle HTTP exceptions (403, 405, 429, etc.)
        if ($e instanceof HttpException) {
            $status = $e->getStatusCode();
            return response()->json([
                'status'  => false,
                'message' => __('custom.errors.' . $status) ?? $e->getMessage(),
                'errors'  => [],
            ], $status);
        }

        // In local/dev environment: show debug details (message + stack trace)
        if (app()->environment('local')) {
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
                'errors'  => [],

            ], 500);
        }

        // Fallback for unexpected errors (500)
        return response()->json([
            'status'  => false,
            'message' => __('custom.Error'),
            'errors'  => [],
        ], 500);
    }
}
