<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
class Handler extends ExceptionHandler
{
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            // logging or ignore
        });
    }

    public function render($request, Throwable $e)
    {
        if ($e instanceof BaseException) {
            return $e->render();
        }

        if (
            $e->getPrevious() instanceof ModelNotFoundException
        ) {
            return response()->json([
                'error' => 'العنصر المطلوب غير موجود',
            ], 404);
        }

        return response()->json([
            'message' => __('custom.Unexpected error'),
        ], 500);
    }
}
