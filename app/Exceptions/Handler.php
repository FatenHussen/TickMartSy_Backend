<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\QueryException;
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

        // 409 Delete confirmation required (category / attribute / variant)
        $exceptions->render(function (\App\Exceptions\DeleteConfirmationRequiredException $e, $request) {
            return $e->render();
        });

        // 409 Foreign key constraint (delete restricted)
        $exceptions->render(function (QueryException $e, $request) {
            $errorCode = $e->errorInfo[1] ?? null;

            if ($errorCode === 1451) {
                return response()->json([
                    'status' => false,
                    'message' => __('custom.cannot_delete_category_related'),
                    'errors' => [],
                ], 409);
            }
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
        // $exceptions->render(function (MethodNotAllowedHttpException $e, $request) {
        //     return response()->json([
        //         'status' => false,
        //         'message' => __('custom.errors.405') . ' ,' . $e->getMessage(),
        //         'errors' => [],
        //     ], 405);
        // });

        // BaseException
        $exceptions->render(function (BaseException $e, $request) {
            return $e->render();
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
