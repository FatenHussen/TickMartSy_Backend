<?php

namespace App\Http\Controllers;

abstract class Controller
{

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
