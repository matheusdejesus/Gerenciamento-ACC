<?php
error_reporting(0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

ob_start();

require_once __DIR__ . '/../controllers/AtividadeComplementarController.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

use backend\api\controllers\AtividadeComplementarController;
use backend\api\middleware\AuthMiddleware;

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
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        enviarErro('Método não permitido', 405);
    }
    
    // Validar token JWT e verificar se é orientador
    $usuario = AuthMiddleware::requireOrientador();
    
    $controller = new AtividadeComplementarController();
    
    // Capturar a saída do controller
    ob_start();
    $controller->listarPendentesOrientadorComJWT($usuario['id']);
    $output = ob_get_clean();
    
    // Verificar se é JSON válido
    $jsonData = json_decode($output, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("JSON inválido em atividades_pendentes: " . $output);
        enviarErro('Resposta inválida do servidor');
    }
    
    echo $output;
    
} catch (Exception $e) {
    error_log("Erro na rota atividades_pendentes: " . $e->getMessage());
    enviarErro('Erro interno do servidor: ' . $e->getMessage());
}
?>