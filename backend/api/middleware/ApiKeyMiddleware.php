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

    public static function verificarApiKey() {
        $headers = getallheaders();
        
        if (!$headers) {
            error_log("Headers não encontrados");
            return null;
        }
        
        // Buscar header X-API-Key (suporte a diferentes formatos)
        $apiKey = null;
        $possibleKeys = ['X-API-Key', 'x-api-key', 'X-Api-Key', 'HTTP_X_API_KEY'];
        
        foreach ($possibleKeys as $keyName) {
            if (isset($headers[$keyName])) {
                $apiKey = $headers[$keyName];
                break;
            }
        }
        
        if (!$apiKey) {
            error_log("API Key não encontrada nos headers");
            error_log("Headers disponíveis: " . print_r(array_keys($headers), true));
            return null;
        }
        
        try {
            $db = Database::getInstance()->getConnection();
            
            // Buscar a API key e os dados do usuário associado
            $stmt = $db->prepare("
                SELECT ak.id as api_key_id, ak.usuario_id, u.nome, u.email, u.tipo 
                FROM ApiKeys ak 
                JOIN Usuario u ON ak.usuario_id = u.id 
                WHERE ak.api_key = ? AND ak.ativa = 1
            ");
            $stmt->bind_param("s", $apiKey);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                error_log("API Key inválida ou inativa: " . $apiKey);
                return null;
            }
            
            $userData = $result->fetch_assoc();
            
            error_log("Dados do usuário encontrados via API Key: " . print_r($userData, true));
            
            // Retornar dados do usuário no formato esperado
            return [
                'id' => $userData['usuario_id'],
                'nome' => $userData['nome'],
                'email' => $userData['email'],
                'tipo' => $userData['tipo']
            ];
            
        } catch (Exception $e) {
            error_log("Erro ao verificar API Key: " . $e->getMessage());
            return null;
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
?>