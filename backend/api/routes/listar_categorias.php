<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controllers/AtividadesDisponiveisController.php';
require_once __DIR__ . '/../middleware/ApiKeyMiddleware.php';

use backend\api\controllers\AtividadesDisponiveisController;
use backend\api\middleware\ApiKeyMiddleware;

try {
    ApiKeyMiddleware::validateApiKey();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller = new AtividadesDisponiveisController();
        $controller->listarCategorias();
    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Método não permitido']);
    }
} catch (Exception $e) {
    error_log("Erro na rota listar_categorias: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erro interno do servidor']);
}
?>