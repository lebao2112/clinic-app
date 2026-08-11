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
}