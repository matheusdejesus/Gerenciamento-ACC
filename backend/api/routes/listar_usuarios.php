<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../middleware/ApiKeyMiddleware.php';
require_once __DIR__ . '/../controllers/UsuarioController.php';

use backend\api\middleware\AuthMiddleware;
use backend\api\middleware\ApiKeyMiddleware;

try {
    // Verificar API Key
    ApiKeyMiddleware::validateApiKey();
    
    // Verificar autenticação JWT
    $usuario = AuthMiddleware::validateToken();
    if (!$usuario) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Token inválido ou ausente']);
        exit;
    }
    
    // Verificar se é admin
    if ($usuario['tipo'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Acesso negado - apenas administradores']);
        exit;
    }
    
    $controller = new UsuarioController();
    $controller->listarTodos();
    
} catch (Exception $e) {
    error_log("Erro em listar_usuarios.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erro interno do servidor']);
}