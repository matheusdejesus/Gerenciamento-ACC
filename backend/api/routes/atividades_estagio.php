<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../controllers/AtividadeComplementarEstagioController.php';

use backend\api\controllers\AtividadeComplementarEstagioController;

try {
    $controller = new AtividadeComplementarEstagioController();
    $metodo = $_SERVER['REQUEST_METHOD'];
    
    switch ($metodo) {
        case 'GET':
            if (isset($_GET['id'])) {
                $controller->buscarPorId($_GET['id']);
            } elseif (isset($_GET['aluno_id'])) {
                $controller->listarPorAluno();
            } else {
                $controller->listar();
            }
            break;
            
        case 'POST':
            $controller->criar();
            break;
            
        case 'PUT':
            $controller->atualizarStatus();
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['erro' => 'Método não permitido']);
    }
    
} catch (Exception $e) {
    error_log("Erro em atividades_estagio.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['erro' => 'Erro interno do servidor: ' . $e->getMessage()]);
}
?>