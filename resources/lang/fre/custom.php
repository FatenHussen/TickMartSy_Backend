<?php

return [

    'Success'           => 'Operation completed successfully.',
    'Error'             => 'An error occurred, please try again later.',
    'NotFound'          => 'The requested item was not found.',
    'Unauthorized'      => 'You are not authorized to perform this action.',
    'Forbidden'         => 'Access denied.',
    'ValidationError'   => 'The given data was invalid.',
    'Created'           => 'Item created successfully.',
    'Updated'           => 'Data updated successfully.',
    'Deleted'           => 'Item deleted successfully.',
    'invalid_verification_code' => 'Invalid verification code',
    'invalid_coupon' => 'invalid coupon',
    'inactive_account' => 'Account is inactive',
    // HTTP Status Codes
    'errors' => [
        400 => 'Bad request.',
        401 => 'Unauthorized.',
        403 => 'Forbidden.',
        404 => 'The requested item was not found.',
        405 => 'Method not allowed.',
        409 => 'Conflict detected.',
        422 => 'Unprocessable entity.',
        429 => 'Too many requests, please try again later.',
        500 => 'Server error, please try again later.',
        503 => 'Service unavailable.',
    ],
    'custom_error' => 'Something went wrong. Please try again later.',

    'wrong_credential' => 'Invalid login credentials.',

    'wrong_password' => 'The current password is incorrect.',

    'otp_invalid' => 'The verification code is invalid or expired.',

    'otp_valid' => 'The verification code is invalid or has expired.',

    'password_valid' => 'The reset code is invalid or has expired.',
];
