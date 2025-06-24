<?php

namespace backend\api\middleware;

require_once __DIR__ . '/../services/JWTService.php';

use backend\api\services\JWTService;

class AuthMiddleware {
    
    public static function validateToken() {
        $headers = getallheaders();
        
        // Verificar se o header Authorization existe
        if (!isset($headers['Authorization'])) {
            self::sendError('Token de acesso obrigatório', 401);
        }
        
        $authHeader = $headers['Authorization'];
        
        if (!preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            self::sendError('Formato de token inválido. Use: Bearer <token>', 401);
        }
        
        $token = $matches[1];
        
        // Validar token
        $payload = JWTService::validate($token);
        if (!$payload) {
            self::sendError('Token inválido ou expirado', 401);
        }
        
        return $payload;
    }
    
    public static function requireRole($allowedRoles) {
        $payload = self::validateToken();
        
        if (!in_array($payload['tipo'], $allowedRoles)) {
            self::sendError('Acesso negado para este tipo de usuário', 403);
        }
        
        return $payload;
    }
    
    public static function requireAluno() {
        return self::requireRole(['aluno']);
    }
    
    public static function requireOrientador() {
        return self::requireRole(['orientador']);
    }
    
    public static function requireCoordenador() {
        return self::requireRole(['coordenador']);
    }
    
    public static function requireAlunoOrOrientador() {
        return self::requireRole(['aluno', 'orientador']);
    }
    
    private static function sendError($message, $code = 401) {
        http_response_code($code);
        echo json_encode(['error' => $message]);
        exit;
    }
}
?>