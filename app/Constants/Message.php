<?php

namespace App\Constants;

class Message
{
    const UNAUTHORIZED = 'Unauthorized';
    const NO_ROLE_ASSIGNED = 'Forbidden. Account has no role assigned.';
    const FORBIDDEN = 'Forbidden. You do not have permission: ';

    const INVALID_CREDENTIALS = 'Invalid credentials';
    const LOGIN_SUCCESS = 'Login successful';
    const LOGOUT_SUCCESS = 'Logged out successfully';

    const VALIDATION_FAILED = 'Validation failed';
    const LAST_ADMIN_ACTION_DENIED = 'Action denied: Cannot deactivate, delete, or change the role of the last active ADMIN.';
    const SUCCESS = 'Success';
    const ERROR = 'An error occurred';

    const NOT_FOUND = 'Endpoint or resource not found';
    const INTERNAL_SERVER_ERROR = 'Internal Server Error';

    const INVOICE_CREATED_SUCCESS = 'Invoice created successfully.';
    const CREATE_INVOICE_FAILED = 'Failed to create invoice.';
    const EXAMINATION_NOT_FOUND = 'Examination record not found.';
}