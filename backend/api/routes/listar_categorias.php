<?php
// Configurar output buffering e headers
ob_start();
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-API-Key');
header('Access-Control-Allow-Credentials: true');

// Log de debug - início do script
error_log("[DEBUG] listar_categorias.php - Início do script. Método: " . $_SERVER['REQUEST_METHOD']);

// Limpar qualquer output anterior
ob_clean();

ini_set('display_errors', 1);
ini_set('log_errors', 1);

// Tratar preflight e sondagens leves
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Permitir HEAD como verificação de conectividade sem executar lógica
if ($_SERVER['REQUEST_METHOD'] === 'HEAD') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../middleware/ApiKeyMiddleware.php';
require_once __DIR__ . '/../services/JWTService.php';
require_once __DIR__ . '/../controllers/CategoriaController.php';

use backend\api\config\Database;
use backend\api\middleware\AuthMiddleware;
use backend\api\middleware\ApiKeyMiddleware;
use backend\api\services\JWTService;

function enviarErro($mensagem, $codigo = 400) {
    error_log("[DEBUG] enviarErro chamada - Código: $codigo, Mensagem: $mensagem");
    ob_clean();
    http_response_code($codigo);
    $response = [
        'success' => false,
        'error' => $mensagem
    ];
    echo json_encode($response);
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

    // Instanciar o controller
    $categoriaController = new CategoriaController();
    
    // Obter dados da requisição
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        $input = $_POST;
    }
    
    // Verificar se é uma requisição para buscar categoria específica
    if (isset($input['id']) && is_numeric($input['id'])) {
        // Buscar categoria por ID
        $categoriaController->buscarPorId($input['id']);
    } else {
        // Listar todas as categorias
        $categoriaController->listarTodas();
    }

} catch (Exception $e) {
    error_log("[ERROR] Erro em listar_categorias.php: " . $e->getMessage());
    error_log("[ERROR] Stack trace: " . $e->getTraceAsString());
    enviarErro('Erro interno do servidor', 500);
}

// Finalizar output buffering
ob_end_flush();
?>