<?php

namespace App\Constants;

class Message
{
    const UNAUTHORIZED = 'Unauthorized';
    const NO_ROLE_ASSIGNED = 'Forbidden. Account has no role assigned.';
    const FORBIDDEN = 'Forbidden. You do not have permission: ';
    const DOCTOR_ROLE_REQUIRED = 'Doctor profiles can only be created for users with the DOCTOR role.';

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
    const INVOICE_DISCOUNT_UPDATED = 'Invoice discount updated successfully.';
    const INVOICE_CANCELLED = 'Invoice cancelled successfully.';
    const INVOICE_CANNOT_BE_MODIFIED = 'Action denied: Invoice cannot be modified because it is not in unpaid status.';

    const LOG_INVOICE_CREATION_ERROR = 'Invoice Creation Error: ';
    const LOG_INVOICE_UPDATE_DISCOUNT_ERROR = 'Invoice Update Discount Error: ';
    const LOG_INVOICE_CANCEL_ERROR = 'Invoice Cancel Error: ';

    const PAYMENT_ORDER_CREATED = 'Payment order created successfully.';
    const PAYMENT_CAPTURED_SUCCESS = 'Payment captured successfully.';
    const PAYMENT_CAPTURE_FAILED = 'Payment capture failed.';
    const PAYMENT_NOT_PENDING = 'Action denied: Only pending payments can be captured.';

    const APPOINTMENT_STATE_TRANSITION_NOT_ALLOWED = 'State transition not allowed from :from to :to.';
    const APPOINTMENT_DOCTOR_BUSY = 'The doctor is busy around this time. Please choose a slot at least :duration minutes apart.';
    const EXAMINATION_CANCELLED_APPOINTMENT = 'Cannot create an examination for a cancelled appointment.';
    const EXAMINATION_ALREADY_COMPLETED = 'This appointment has already been completed.';
    const EXAMINATION_CONFIRMED_REQUIRED = 'Examinations can only be created for confirmed appointments.';
    const EXAMINATION_ALREADY_EXISTS = 'An examination record already exists for this appointment.';

    const MEDICINE_STOCK_NEGATIVE = 'Stock cannot be negative after adjustment.';
    const PAYMENT_EXCEED_BALANCE = 'The payment amount cannot exceed the remaining balance (:amount).';

    const PAYPAL_AUTH_FAILED = 'PayPal authentication failed: ';
    const PAYPAL_CREATE_ORDER_FAILED = 'Failed to create PayPal order: ';
    const PAYPAL_CAPTURE_FAILED = 'Failed to capture PayPal order: ';
    const NOT_ENOUGH_STOCK_NEW_MEDICINE = 'Not enough stock for new medicine ID: :medicineId. Available: :stock';
    const NOT_ENOUGH_STOCK_MEDICINE = 'Not enough stock for medicine ID: :medicineId. Available: :stock';

    const ACCOUNT_LOCKED = 'Tài khoản của bạn đã bị khóa bởi quản trị viên.';
}