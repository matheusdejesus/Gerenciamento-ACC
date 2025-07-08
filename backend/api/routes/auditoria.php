<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key');
header('Access-Control-Allow-Credentials: true');

ob_start();

require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../middleware/ApiKeyMiddleware.php';
require_once __DIR__ . '/../controllers/LogAcoesController.php';

use backend\api\controllers\LogAcoesController;
use backend\api\middleware\AuthMiddleware;
use backend\api\middleware\ApiKeyMiddleware;

function enviarErro($mensagem, $codigo = 500) {
    ob_end_clean();
    http_response_code($codigo);
    echo json_encode([
        'success' => false,
        'error' => $mensagem
    ]);
    exit;
}

try {
    error_log("=== INICIO auditoria.php ===");
    error_log("Method: " . $_SERVER['REQUEST_METHOD']);
    error_log("GET: " . print_r($_GET, true));
    error_log("POST: " . print_r($_POST, true));
    
    // Validar API Key
    ApiKeyMiddleware::validateApiKey();
    
    // Validar autenticação
    $usuario = AuthMiddleware::validateToken();
    if (!$usuario) {
        enviarErro('Token inválido ou ausente', 401);
    }
    
    $controller = new LogAcoesController();
    
    // POST: Registrar nova ação
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        error_log("=== REGISTRAR NOVA AÇÃO ===");
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Permitir que usuários registrem suas próprias ações
        if ($usuario['tipo'] === 'admin') {
            // Administradores podem registrar qualquer ação
            $controller->registrarAcao();
        } else {
            // Usuários comuns só podem registrar ações para si mesmos
            $controller->registrarAcaoUsuario($usuario['id'], $data['acao'], $data['detalhes']);
        }
        exit;
    }
    
    // GET: Buscar logs
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        
        // GET: Estatísticas
        if (isset($_GET['acao']) && $_GET['acao'] === 'estatisticas') {
            error_log("=== BUSCAR ESTATÍSTICAS ===");
            
            // Apenas administradores podem ver estatísticas
            if ($usuario['tipo'] !== 'admin') {
                enviarErro('Acesso negado. Apenas administradores podem ver estatísticas.', 403);
            }
            
            $controller->buscarEstatisticas();
            exit;
        }
        
        // GET: Logs de um usuário específico
        if (isset($_GET['usuario_id'])) {
            error_log("=== BUSCAR LOGS POR USUÁRIO ===");
            
            $usuario_id = (int)$_GET['usuario_id'];
            
            // Usuários podem ver apenas seus próprios logs, admins podem ver todos
            if ($usuario['tipo'] !== 'admin' && $usuario['id'] != $usuario_id) {
                enviarErro('Acesso negado. Você só pode ver seus próprios logs.', 403);
            }
            
            $controller->buscarPorUsuario($usuario_id);
            exit;
        }
        
        // GET: Todos os logs (apenas para administradores)
        error_log("=== BUSCAR TODOS OS LOGS ===");
        
        if ($usuario['tipo'] !== 'admin') {
            enviarErro('Acesso negado. Apenas administradores podem ver todos os logs.', 403);
        }
        
        $controller->buscarTodos();
        exit;
    }
    
    // Se chegou até aqui, método não é suportado
    enviarErro('Método não permitido', 405);
    
} catch (Exception $e) {
    error_log("Erro na rota auditoria: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    enviarErro('Erro interno do servidor: ' . $e->getMessage());
}

if (ob_get_length()) {
    ob_end_flush();
}
?>