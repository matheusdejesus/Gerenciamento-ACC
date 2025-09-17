<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost');
header('Access-Control-Allow-Methods: GET, POST, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

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
    
    ApiKeyMiddleware::validateApiKey();
    $usuario = AuthMiddleware::validateToken();
    if (!$usuario) {
        enviarErro('Token inválido ou ausente', 401);
    }
    
    $controller = new LogAcoesController();
    
    // DELETE: Remover atividade (ADMIN)
    if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        error_log("=== REMOVER ATIVIDADE ===");
        if ($usuario['tipo'] !== 'admin') {
            enviarErro('Acesso negado. Apenas administradores podem remover atividades.', 403);
        }
        
        // Verificar se é para remover atividade
        if (isset($_GET['acao']) && $_GET['acao'] === 'remover_atividade') {
            require_once __DIR__ . '/../controllers/AtividadesDisponiveisController.php';
            $atividadesController = new \backend\api\controllers\AtividadesDisponiveisController();
            $atividadesController->remover();
            exit;
        }
        
        enviarErro('Ação DELETE não reconhecida', 400);
    }
    
    // POST: Registrar nova ação
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        error_log("=== REGISTRAR NOVA AÇÃO ===");
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        if ($usuario['tipo'] === 'admin') {
            $controller->registrarAcao();
        } else {
            $controller->registrarAcaoUsuario($usuario['id'], $data['acao'], $data['detalhes']);
        }
        exit;
    }
    
    // GET: Buscar logs
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        
        // GET: Estatísticas
        if (isset($_GET['acao']) && $_GET['acao'] === 'estatisticas') {
            error_log("=== BUSCAR ESTATÍSTICAS ===");
            if ($usuario['tipo'] !== 'admin') {
                enviarErro('Acesso negado. Apenas administradores podem ver estatísticas.', 403);
            }
            
            $controller->buscarEstatisticas();
            exit;
        }
        
        // GET: Logs de um usuário específico
        if (isset($_GET['usuario_id'])) {
            $usuario_id = intval($_GET['usuario_id']);
            error_log("=== BUSCAR LOGS POR USUARIO: $usuario_id ===");
            if ($usuario['tipo'] !== 'admin' && $usuario['id'] != $usuario_id) {
                enviarErro('Acesso negado. Você só pode ver seus próprios logs.', 403);
            }
            
            $controller->buscarPorUsuario($usuario_id);
            exit;
        }
        
        // GET: Todos os logs
        if ($usuario['tipo'] !== 'admin') {
            enviarErro('Acesso negado. Apenas administradores podem ver todos os logs.', 403);
        }

        $controller->buscarTodos();
        exit;
    }
    
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