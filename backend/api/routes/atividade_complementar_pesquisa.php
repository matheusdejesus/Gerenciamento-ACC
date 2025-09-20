<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../controllers/AtividadeComplementarPesquisaController.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

use backend\api\controllers\AtividadeComplementarPesquisaController;
use backend\api\middleware\AuthMiddleware;

try {
    // Inicializar controlador
    $controller = new AtividadeComplementarPesquisaController();
    
    // Roteamento baseado no método HTTP e parâmetros
    $method = $_SERVER['REQUEST_METHOD'];
    $path = $_SERVER['REQUEST_URI'];
    
    switch ($method) {
        case 'POST':
            if (isset($_POST['action'])) {
                switch ($_POST['action']) {
                    case 'cadastrar':
                        $controller->cadastrar();
                        break;
                    case 'avaliar':
                        $controller->avaliarAtividade();
                        break;
                    default:
                        http_response_code(400);
                        echo json_encode(['error' => 'Ação não reconhecida']);
                }
            } else {
                // Ação padrão é cadastrar
                $controller->cadastrar();
            }
            break;
            
        case 'GET':
            if (isset($_GET['aluno_id'])) {
                $controller->listarPorAluno();
            } elseif (isset($_GET['id'])) {
                $controller->buscarPorId();
            } elseif (isset($_GET['listar_todas'])) {
                $controller->listarTodas();
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Parâmetros inválidos']);
            }
            break;
            
        case 'PUT':
            // Ler dados do corpo da requisição
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (isset($input['action']) && $input['action'] === 'avaliar') {
                $controller->avaliarAtividade($input);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Ação não reconhecida para método PUT']);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Método não permitido']);
            break;
    }
    
} catch (Exception $e) {
    error_log("Erro na rota de atividades de pesquisa: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erro interno do servidor'
    ]);
}