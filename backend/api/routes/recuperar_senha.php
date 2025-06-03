<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config/config.php';

if (file_exists(__DIR__ . '/../../../frontend/pages/vendor/autoload.php')) {
    require_once __DIR__ . '/../../../frontend/pages/vendor/autoload.php';
}

use backend\api\services\RecuperarSenhaService;

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Método não permitido']);
        exit;
    }

    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!$data || !isset($data['email'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Email é obrigatório']);
        exit;
    }

    $recuperarSenhaService = new RecuperarSenhaService();
    $resultado = $recuperarSenhaService->solicitarRecuperacao($data['email']);
    
    echo json_encode($resultado);

} catch (Exception $e) {
    $statusCode = 400;
    if (strpos($e->getMessage(), 'Email não encontrado') !== false) {
        $statusCode = 404;
    } elseif (strpos($e->getMessage(), 'Erro ao') !== false) {
        $statusCode = 500;
    }
    
    http_response_code($statusCode);
    echo json_encode(['error' => $e->getMessage()]);
}
?>