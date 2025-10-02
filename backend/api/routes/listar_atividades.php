<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controllers/AtividadesDisponiveisController.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $controller = new \backend\api\controllers\AtividadesDisponiveisController();
        // Usar o novo método que filtra por matrícula e valida JWT internamente
        $controller->listarPorMatricula();
    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Método não permitido']);
    }
} catch (Exception $e) {
    error_log("Erro na rota listar_atividades: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erro interno do servidor']);
}
?>