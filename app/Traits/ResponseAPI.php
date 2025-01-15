<?php

namespace App\Traits;

trait ResponseAPI
{
    /**
     * Core of response
     * 
     * @param   string          $message
     * @param   array|object    $data
     * @param   string|integer  $statusCode  
     * @param   boolean         $isSuccess
    */
    public function coreResponse($message, $data = null, $statusCode, $isSuccess = true)
    {
        // Ensure status code is an integer
        if (is_string($statusCode)) {
            $statusCode = (int)$statusCode;
        }

        if (!$message) {
            return response()->json(['message' => 'Message Is Required'], 500);
        }

        if ($isSuccess) {
            return response()->json([
                'message' => $message,
                'error' => false,
                'code' => $statusCode,
                'data' => $data
            ], $statusCode);
        } else {
            return response()->json([
                'message' => $message,
                'error' => true,
                'code' => $statusCode,
            ], $statusCode);
        }
    }

    /**
     * Send any success response
     * 
     * @param   string          $message
     * @param   array|object    $data
     * @param   integer         $statusCode
    */
    public function success($message, $data, $statusCode = 200) {
        return $this->coreResponse($message, $data, $statusCode);
    }

    /**
     * Send any error response
     * 
     * @param   string          $message
     * @param   integer         $statusCode    
    */
    public function error($message, $statusCode = 500) {
        return $this->coreResponse($message, null, $statusCode, false);
    }
}