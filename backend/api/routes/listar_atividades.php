<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key');

require_once __DIR__ . '/../controllers/AtividadesDisponiveisController.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../middleware/ApiKeyMiddleware.php';

use backend\api\controllers\AtividadesDisponiveisController;
use backend\api\middleware\AuthMiddleware;
use backend\api\middleware\ApiKeyMiddleware;

ApiKeyMiddleware::validateApiKey(); 

try {
    
    $usuario = AuthMiddleware::validateToken();
    if (!$usuario) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Token inválido ou ausente']);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Método não permitido']);
        exit;
    }

    $controller = new AtividadesDisponiveisController();
    if (isset($_GET['id'])) {
        $controller->buscarPorId();
    } else {
        $controller->listar();
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erro interno do servidor: ' . $e->getMessage()
    ]);
}
?>