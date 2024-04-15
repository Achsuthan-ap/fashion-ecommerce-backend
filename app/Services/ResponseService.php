<?php

namespace App\Services;

class ResponseService
{
    /**
     * Generate a standardized JSON response based on the provided status and parameters.
     *
     * @param string $statusKey   The status key indicating the desired HTTP response status.
     * @param mixed  $result   The result data to be included in the response.
     * @param string $message  The optional custom message to be included in the response.
     * @param mixed  $systemCode The internal response code. Defaults to null.
     *
     * @return \Illuminate\Http\JsonResponse The JSON response with the specified status, message, and data.
     */
    public static function response($statusKey, $result = null, $message = null, $systemCode = null)
    {
        try {
            // Define HTTP status codes and their corresponding details
            $httpStatusCodes = [
                'SUCCESS' => [
                    'code' => 200,
                    'success' => true,
                    'message' => 'Success'
                ],
                'NOT_FOUND' => [
                    'code' => 404,
                    'success' => false,
                    'message' => 'The requested resource was not found'
                ],
                'FORBIDDEN' => [
                    'code' => 403,
                    'success' => false,
                    'message' => 'Permission denied'
                ],
                'INTERNAL_SERVER_ERROR' => [
                    'code' => 500,
                    'success' => false,
                    'message' => 'An error occurred while processing the request'
                ],
                'VALIDATION_ERROR' => [
                    'code' => 417,
                    'success' => false,
                    'message' => 'There was a validation error'
                ],
                'UNAUTHORIZED' => [
                    'code' => 401,
                    'success' => false,
                    'message' => 'Unauthorized'
                ]
            ];

            // Retrieve details for the specified status code
            $httpStatusCode = $httpStatusCodes[$statusKey];

            // Construct the response JSON based on the provided parameters
            return response()->json([
                'is_success' => $httpStatusCode['success'],
                'message' => $httpStatusCode['code'] == 500 ? (env('APP_DEBUG') ? $message : $httpStatusCode['message']) : ($message ?? $httpStatusCode['message']),
                'result' => $result,
                'system_code' => $systemCode ?? '',
            ], $httpStatusCode['code']);
        } catch (\Throwable $th) {
            // Handle exceptions and return a generic error response
            return response()->json([
                'message' => 'Please check the response service',
                'result' => $th->getMessage()
            ], 500);
        }
    }
}
