<?php

namespace App\Traits;

use App\Constants\Message;

trait ApiResponse
{
    /**
     * Return a success JSON response.
     */
    public function successResponse($data = null, $message = Message::SUCCESS, $statusCode = 200, $meta = null)
    {
        $response = [
            'success' => true,
            'message' => $message,
            'data'    => $data
        ];

        // Append pagination meta data if provided
        if (!is_null($meta)) {
            $response['meta'] = $meta;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Return an error JSON response.
     */
    public function errorResponse($message = Message::ERROR, $statusCode = 400, $errors = null)
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        // Append validation errors for 422 Unprocessable Entity
        if (!is_null($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }
}