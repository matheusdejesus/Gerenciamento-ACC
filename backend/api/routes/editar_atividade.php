<?php
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    require_once __DIR__ . '/../config/config.php';
    require_once __DIR__ . '/../controllers/AtividadesDisponiveisController.php';
    require_once __DIR__ . '/../middleware/AuthMiddleware.php';
    require_once __DIR__ . '/../middleware/ApiKeyMiddleware.php';
    
    \backend\api\middleware\ApiKeyMiddleware::validateApiKey();
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller = new \backend\api\controllers\AtividadesDisponiveisController();
        $controller->editar();
    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Método não permitido']);
    }
} catch (Exception $e) {
    error_log("Erro na rota editar_atividade: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erro interno do servidor: ' . $e->getMessage()]);
}
?>
