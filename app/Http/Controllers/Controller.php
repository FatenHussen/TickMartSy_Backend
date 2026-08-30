<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

abstract class Controller
{
    /** Upper bound for `per_page`; the admin pages picker legitimately asks for 500. */
    protected const MAX_PER_PAGE = 500;

    /**
     * Reads `per_page` and clamps it before it reaches `paginate()`, which takes whatever it
     * is handed: 0 or a negative value returns the whole table in one response, and an
     * arbitrarily large value lets any client do the same.
     */
    protected function resolvePerPage(Request $request, int $default = 10, string $key = 'per_page'): int
    {
        $perPage = (int) $request->input($key, $default);

        return $perPage < 1 ? $default : min($perPage, self::MAX_PER_PAGE);
    }

    /**
     * @param  class-string<FormRequest>|null  $filterRequest
     * @return array<string, mixed>
     */
    protected function resolveValidatedFilters(Request $request, ?string $filterRequest): array
    {
        if ($filterRequest === null) {
            return [];
        }

        /** @var FormRequest $formRequest */
        $formRequest = $filterRequest::createFrom($request);
        $formRequest->setContainer(app())->setRedirector(app('redirect'));
        $formRequest->validateResolved();

        return $formRequest->validated();
    }

    protected function sendResponse($data = [], $message = null, $code = 200, $meta = [])
    {
        if ($message === null) {
            $message = __('custom.Success');
        }

        $response = [
            'status'  => true,
            'message' => $message,
            'data'    => $data,
        ];

        if (!empty($meta)) {
            $response['meta'] = $meta;
        }

        return response()->json($response, $code);
    }

    protected function sendError($message = null, $code = 400, $errors = [])
    {
        if ($message === null) {
            $message = __('custom.Error');
        }
        $response = [
            'status'  => 'error',
            'message' => $message,
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }
}
