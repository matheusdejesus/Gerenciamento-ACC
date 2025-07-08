<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key');
header('Access-Control-Allow-Credentials: true');

ob_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../middleware/ApiKeyMiddleware.php';
require_once __DIR__ . '/../controllers/AtividadeComplementarController.php';

use backend\api\controllers\AtividadeComplementarController;
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
    error_log("=== INICIO minhas_atividades.php ===");
    error_log("Method: " . $_SERVER['REQUEST_METHOD']);
    
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        enviarErro('Método não permitido', 405);
    }
    
    $usuario = ApiKeyMiddleware::verificarApiKey();
    
    if (!$usuario) {
        error_log("API Key não encontrada, tentando JWT...");
        $usuario = AuthMiddleware::validateToken();
        
        if (!$usuario) {
            error_log("Nenhum método de autenticação válido encontrado");
            enviarErro('Token de acesso inválido', 401);
        }
    }
    
    error_log("Usuário autenticado: " . print_r($usuario, true));
    
    // Verificar se é aluno
    if ($usuario['tipo'] !== 'aluno') {
        error_log("Tipo de usuário inválido: " . $usuario['tipo']);
        enviarErro('Acesso negado. Apenas alunos podem acessar.', 403);
    }
    
    $controller = new AtividadeComplementarController();
    ob_start();
    $controller->listarPorAluno($usuario['id']);
    $output = ob_get_clean();
    
    // Verificar se é JSON válido
    $jsonData = json_decode($output, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("JSON inválido em minhas_atividades: " . $output);
        enviarErro('Resposta inválida do servidor');
    }
    
    echo $output;
    
} catch (Exception $e) {
    error_log("Erro na rota minhas_atividades: " . $e->getMessage());
    enviarErro('Erro interno do servidor: ' . $e->getMessage());
}
?>