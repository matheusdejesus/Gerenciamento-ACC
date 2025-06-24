<?php
namespace backend\api\controllers;

class Controller {
    
    protected function sendJsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        
        // Enviar resposta e finalizar
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    protected function getRequestData() {
        $input = file_get_contents('php://input');
        return json_decode($input, true) ?? [];
    }
    
    protected function validateRequired($data, $required) {
        $missing = [];
        foreach ($required as $field) {
            if (!isset($data[$field]) || empty(trim($data[$field]))) {
                $missing[] = $field;
            }
        }
        return $missing;
    }
}
?>