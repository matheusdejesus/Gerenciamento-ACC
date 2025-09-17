<?php
namespace backend\api\middleware;

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../services/JWTService.php';
require_once __DIR__ . '/ApiKeyMiddleware.php';

use backend\api\config\Database;
use backend\api\services\JWTService;
use backend\api\middleware\ApiKeyMiddleware;
use Exception;

class AuthMiddleware {
    
    public static function validateToken() {
        try {
            $headers = function_exists('getallheaders') ? getallheaders() : [];
            
            if (empty($headers)) {
                // Fallback para servidores sem getallheaders (ex.: PHP built-in)
                foreach ($_SERVER as $key => $value) {
                    if (strpos($key, 'HTTP_') === 0) {
                        $header = str_replace('_', '-', substr($key, 5));
                        $headers[$header] = $value;
                    }
                }
            }
            
            // Normalizar chaves dos headers (case-insensitive)
            $normalizedHeaders = [];
            foreach ($headers as $key => $value) {
                $normalizedHeaders[strtolower($key)] = $value;
            }
            $headers = array_merge($headers, $normalizedHeaders);
            
            if (!$headers) {
                error_log("Headers não encontrados");
                return null;
            }
            
            // Verificar header Authorization
            $authHeader = null;
            foreach ($headers as $key => $value) {
                if (strtolower($key) === 'authorization') {
                    $authHeader = $value;
                    break;
                }
            }
            
            if (!$authHeader) {
                error_log("Header Authorization não encontrado");
                return null;
            }
            
            // Verificar formato Bearer
            if (!preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
                error_log("Formato de token inválido: " . $authHeader);
                return null;
            }
            
            $token = $matches[1];
            
            // Validar token
            $payload = JWTService::validate($token);
            if (!$payload) {
                error_log("Token inválido ou expirado");
                return null;
            }
            
            error_log("Token validado com sucesso: " . json_encode($payload));
            return $payload;
            
        } catch (Exception $e) {
            error_log("Erro em validateToken: " . $e->getMessage());
            return null;
        }
    }
    
    public static function requireRole($allowedRoles) {
        $payload = self::validateToken();
        
        if (!in_array($payload['tipo'], $allowedRoles)) {
            self::sendError('Acesso negado para este tipo de usuário', 403);
        }
        
        return $payload;
    }
    
    public static function requireAluno() {
        $user = ApiKeyMiddleware::verificarApiKey();
        
        if (!$user) {
            $user = self::validateToken();
        }
        
        if (!$user) {
            http_response_code(401);
            echo json_encode(['error' => 'Token de acesso inválido']);
            exit;
        }
        
        if ($user['tipo'] !== 'aluno') {
            http_response_code(403);
            echo json_encode(['error' => 'Acesso negado - apenas alunos']);
            exit;
        }
        
        return $user;
    }

    public static function requireOrientador() {
        $user = ApiKeyMiddleware::verificarApiKey();
        
        if (!$user) {
            $user = self::validateToken();
        }
        
        if (!$user) {
            http_response_code(401);
            echo json_encode(['error' => 'Token de acesso inválido']);
            exit;
        }
        
        if ($user['tipo'] !== 'orientador') {
            http_response_code(403);
            echo json_encode(['error' => 'Acesso negado - apenas orientadores']);
            exit;
        }
        
        return $user;
    }
    
    public static function requireCoordenador() {
        $user = ApiKeyMiddleware::verificarApiKey();
        
        if (!$user) {
            $user = self::validateToken();
        }
        
        if (!$user) {
            http_response_code(401);
            echo json_encode(['error' => 'Token de acesso inválido']);
            exit;
        }
        
        if ($user['tipo'] !== 'coordenador') {
            http_response_code(403);
            echo json_encode(['error' => 'Acesso negado - apenas coordenadores']);
            exit;
        }
        
        return $user;
    }
    
    public static function requireAlunoOrOrientador() {
        return self::requireRole(['aluno', 'orientador']);
    }
    
    private static function sendError($message, $code = 401) {
        http_response_code($code);
        echo json_encode(['success' => false, 'error' => $message]);
        exit;
    }
}
?>