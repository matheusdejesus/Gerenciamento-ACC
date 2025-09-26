<?php
// Configurar tratamento de erros para evitar HTML na resposta
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once __DIR__ . '/../controllers/AtividadeComplementarEnsinoController.php';

// Configurar headers CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Responder a requisições OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    $controller = new \AtividadeComplementarEnsinoController();
    
    // Obter o método HTTP e a URI
    $method = $_SERVER['REQUEST_METHOD'];
    $uri = $_SERVER['REQUEST_URI'];
    
    // Remover query string da URI
    $uri = strtok($uri, '?');
    
    // Extrair o path após /backend/api/routes/atividade_complementar_ensino.php
    $basePath = '/Gerenciamento-ACC/backend/api/routes/atividade_complementar_ensino.php';
    $path = str_replace($basePath, '', $uri);
    
    // Se não há path adicional, usar a raiz
    if (empty($path) || $path === '/') {
        $path = '/';
    }
    
    // Roteamento
    switch ($method) {
        case 'POST':
            if ($path === '/' || $path === '/criar') {
                $controller->criar();
            } else {
                http_response_code(404);
                echo json_encode(['erro' => 'Rota não encontrada']);
            }
            break;
            
        case 'GET':
            if ($path === '/' || $path === '/listar') {
                // Verificar se é busca por aluno específico
                if (isset($_GET['aluno_id'])) {
                    $controller->listarPorAluno();
                } else {
                    $controller->listar();
                }
            } elseif (preg_match('/^\/([0-9]+)$/', $path, $matches)) {
                // Rota para buscar por ID: /123
                $id = (int)$matches[1];
                $controller->buscarPorId($id);
            } else {
                http_response_code(404);
                echo json_encode(['erro' => 'Rota não encontrada']);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['erro' => 'Método não permitido']);
            break;
    }
    
} catch (\Exception $e) {
    error_log("Erro na rota atividade_complementar_ensino: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['erro' => 'Erro interno do servidor']);
}