<?php

namespace App\Traits;

trait ApiResponse
{
    /**
     * Return a standardized JSON success response.
     *
     * @param string $message    The success message.
     * @param int    $statusCode HTTP status code.
     * @param array  $data       The data to include in the response.
     * @param array  $meta       Additional metadata for the response.
     * @return \Illuminate\Http\JsonResponse
     */
    protected function success(string $message = 'Success.', int $statusCode = 200, array $data = [],  array $meta = [])
    {
        $response = [
            'success' => true,
            'status'  => $statusCode,
            'message' => $message,
            'data'    => $data,
        ];

        if (!empty($meta)) $response['meta'] = $meta;

        return response()->json($response, $statusCode);
    }

    /**
     * Return a standardized JSON error response.
     *
     * @param string $message    The error message.
     * @param int    $statusCode HTTP status code.
     * @param array  $errors     Additional error details.
     * @return \Illuminate\Http\JsonResponse
     */
    protected function error(string $message = 'Error.', int $statusCode = 400, array $errors = [])
    {
        $response = [
            'success' => false,
            'status'  => $statusCode,
            'message' => $message,
        ];

        if (!empty($errors)) $response['errors'] = $errors;

        return response()->json($response, $statusCode);
    }
}
