<?php
// Configurar output buffering e headers
ob_start();
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-API-Key');
header('Access-Control-Allow-Credentials: true');

// Log de debug - início do script
error_log("[DEBUG] cadastrar_atividades.php - Início do script. Método: " . $_SERVER['REQUEST_METHOD']);

// Limpar qualquer output anterior
ob_clean();

ini_set('display_errors', 1);
ini_set('log_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../middleware/ApiKeyMiddleware.php';
require_once __DIR__ . '/../services/JWTService.php';
require_once __DIR__ . '/../controllers/CadastrarAtividadesController.php';

use backend\api\config\Database;
use backend\api\middleware\AuthMiddleware;
use backend\api\middleware\ApiKeyMiddleware;
use backend\api\services\JWTService;
use backend\api\controllers\CadastrarAtividadesController;

function enviarErro($mensagem, $codigo = 500) {
    error_log("[ERROR] " . $mensagem);
    ob_clean();
    http_response_code($codigo);
    echo json_encode([
        'success' => false,
        'error' => $mensagem,
        'message' => $mensagem
    ]);
    exit;
}

function enviarSucesso($dados) {
    error_log("[DEBUG] enviarSucesso chamada");
    ob_clean();
    http_response_code(200);
    echo json_encode($dados);
    exit;
}

try {
    // Verificar método HTTP
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        enviarErro('Método não permitido', 405);
    }

    // Verificar autenticação JWT
    $usuarioLogado = null;
    
    // Tentar autenticação via API Key primeiro
    $usuarioLogado = ApiKeyMiddleware::verificarApiKey();
    if (!$usuarioLogado) {
        // Se não tem API Key, tentar JWT
        $usuarioLogado = AuthMiddleware::validateToken();
        if (!$usuarioLogado) {
            enviarErro('Token de autenticação inválido ou expirado', 401);
        }
    }
    
    error_log("[DEBUG] Usuário autenticado: " . json_encode($usuarioLogado));

    // Verificar se é aluno (apenas alunos podem cadastrar atividades)
    if ($usuarioLogado['tipo'] !== 'aluno') {
        error_log("Tipo de usuário inválido: " . $usuarioLogado['tipo']);
        enviarErro('Acesso negado. Apenas alunos podem cadastrar atividades.', 403);
    }

    // Instanciar o controller
    $controller = new CadastrarAtividadesController();
    
    // Passar dados do usuário autenticado para o controller
    $_POST['usuario_logado'] = $usuarioLogado;
    $_REQUEST['usuario_logado'] = $usuarioLogado;
    
    // Chamar o método do controller
    $controller->cadastrarAtividade();

} catch (Exception $e) {
    error_log("[ERROR] Erro em cadastrar_atividades.php: " . $e->getMessage());
    error_log("[ERROR] Stack trace: " . $e->getTraceAsString());
    enviarErro('Erro interno do servidor: ' . $e->getMessage(), 500);
}

// Finalizar output buffering
ob_end_flush();