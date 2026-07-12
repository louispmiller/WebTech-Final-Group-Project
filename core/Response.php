<?php
// Author: Ojong Bessong NKONGHO
// One of the first things I built because without it every student on the
// team was sending responses in a different format.
// Rachid used { "error": "..." }, Hugo used { "message": "..." }.
// This class enforces a single format across the whole API:
//   success -> { "success": true,  "data": ...    }
//   failure -> { "success": false, "error": "..." }
// Every controller must use this instead of echo json_encode directly.

class Response
{
    public static function success($data = [], $code = 200)
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'data'    => $data
        ]);
        if (php_sapi_name() !== 'cli') {
            exit;
        }
    }

    public static function error($message, $code = 400)
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error'   => $message
        ]);
        if (php_sapi_name() !== 'cli') {
            exit;
        }
    }
}