<?php

class Response {
    public static function send($statusCode, $message, $data = null) {
        if($statusCode >= 300 ){
            logError($message);
        }
        http_response_code($statusCode);
        header('Content-Type: application/json');
        $response = ['status' => $statusCode === 200 ? 'success' : 'error', 'message' => $message];
        if ($data) {
            $response['data'] = $data;
        }
        echo json_encode($response);
        exit;
    }
}
