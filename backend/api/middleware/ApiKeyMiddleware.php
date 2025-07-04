<?php
namespace backend\api\middleware;

require_once __DIR__ . '/../config/Database.php';
use backend\api\config\Database;

class ApiKeyMiddleware {
    
    public static function validateApiKey() {
        $headers = getallheaders();
        
        if (!$headers) {
            self::sendError('Headers não encontrados', 401);
            return false;
        }
        
        // Buscar header X-API-Key
        $apiKey = null;
        foreach ($headers as $key => $value) {
            if (strtolower($key) === 'x-api-key') {
                $apiKey = $value;
                break;
            }
        }
        
        if (!$apiKey) {
            self::sendError('API Key não fornecida', 401);
            return false;
        }
        
        // Validar se a API Key existe e está ativa
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT id, nome_aplicacao FROM ApiKeys WHERE api_key = ? AND ativa = 1");
            $stmt->bind_param("s", $apiKey);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                self::sendError('API Key inválida ou inativa', 401);
                return false;
            }
            
            return true;
            
        } catch (Exception $e) {
            error_log("Erro ao validar API Key: " . $e->getMessage());
            self::sendError('Erro interno na validação da API Key', 500);
            return false;
        }
    }
    
    private static function sendError($message, $statusCode = 401) {
        http_response_code($statusCode);
        echo json_encode([
            'success' => false,
            'error' => $message
        ]);
        exit;
    }
}