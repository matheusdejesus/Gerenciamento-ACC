<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controllers/AtividadesDisponiveisController.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

use backend\api\controllers\AtividadesDisponiveisController;
use backend\api\middleware\AuthMiddleware;

try {
    $userData = AuthMiddleware::validateToken();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller = new AtividadesDisponiveisController();
        $controller->listarCategorias($userData);
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