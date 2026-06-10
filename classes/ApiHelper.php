<?php

class ApiHelper {
    public static function sendResponse($status, $message, $extra = []) {
        header('Content-Type: application/json');

        $response = [
            'status' => $status,
            'message' => $message
        ];

        if (!empty($extra)) {
            $response = array_merge($response, $extra);
        }

        echo json_encode($response);
        exit;
    }

    public static function getJsonInput() {
        $json = file_get_contents('php://input');
        return json_decode($json, true);
    }
}
?>