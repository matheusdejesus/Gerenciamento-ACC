<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once __DIR__ . '/../controllers/AtividadesDisponiveisController.php';

use backend\api\controllers\AtividadesDisponiveisController;

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Método não permitido']);
        exit;
    }
    
    $controller = new AtividadesDisponiveisController();
    // Verificar se é uma busca específica
    if (isset($_GET['id'])) {
        $controller->buscarPorId();
    } elseif (isset($_GET['categoria'])) {
        $controller->buscarPorCategoria();
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