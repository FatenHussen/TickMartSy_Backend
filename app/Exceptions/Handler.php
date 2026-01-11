<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler
{
    public function register($exceptions): void
    {
        // 401
        $exceptions->render(function (AuthenticationException $e, $request) {
            return response()->json([
                'status' => false,
                'message' => __('custom.errors.401') . ' ,' . $e->getMessage(),
                'errors' => [],
            ], 401);
        });

        // 422
        $exceptions->render(function (ValidationException $e, $request) {
            return response()->json([
                'status' => false,
                'message' => __('custom.ValidationError'),
                'errors' => $e->errors(),
            ], 422);
        });

        // 404 Model
        // $exceptions->render(function (ModelNotFoundException $e, $request) {
        //     return response()->json([
        //         'status' => false,
        //         'message' => __('custom.errors.404') . ' ,' . $e->getMessage(),
        //         'errors' => [],
        //     ], 404);
        // });

        // 405
        $exceptions->render(function (MethodNotAllowedHttpException $e, $request) {
            return response()->json([
                'status' => false,
                'message' => __('custom.errors.405') . ' ,' . $e->getMessage(),
                'errors' => [],
            ], 405);
        });

        // HTTP Exceptions
        // $exceptions->render(function (HttpException $e, $request) {
        //     return response()->json([
        //         'status' => false,
        //         'message' => __('custom.errors.' . $e->getStatusCode()) ?? $e->getMessage(),
        //         'errors' => [],
        //     ], $e->getStatusCode());
        // });
    }
}
