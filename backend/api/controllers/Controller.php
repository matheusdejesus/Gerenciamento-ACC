<?php
namespace backend\api\controllers;

class Controller {
    
    protected function getRequestData() {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        
        if (strpos($contentType, 'application/json') !== false) {
            // Se for JSON, decodifica o JSON
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            return $data ?: [];
        } else {
            // Se for form-data, usa $_POST
            return $_POST;
        }
    }
}

// Função global para resposta JSON
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
?>