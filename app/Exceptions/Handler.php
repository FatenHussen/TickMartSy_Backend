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

        // Data errors only — other SQL failures stay 500 and get reported.
        $exceptions->render(function (QueryException $e, $request) {
            if (!$request->expectsJson()) {
                return null;
            }

            $errorCode = (int) ($e->errorInfo[1] ?? 0);

            if ($errorCode === 1451) {
                return response()->json([
                    'status' => false,
                    'message' => __('custom.cannot_delete_category_related'),
                    'errors' => [],
                ], 409);
            }

            // 1452 missing FK parent · 1364/1048 required column empty
            if (in_array($errorCode, [1048, 1364, 1452], true)) {
                report($e);

                return response()->json([
                    'status' => false,
                    'message' => __('custom.cannot_save_missing_reference'),
                    'errors' => [],
                ], 422);
            }

            if ($errorCode === 1062) {
                report($e);

                return response()->json([
                    'status' => false,
                    'message' => __('custom.duplicate_unique_value'),
                    'errors' => [],
                ], 422);
            }

            return null;
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
