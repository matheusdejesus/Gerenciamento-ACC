<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controllers/UsuarioController.php';

// Função para processar a requisição
function processRequest() {
    $method = $_SERVER['REQUEST_METHOD'];
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $uri = explode('/', trim($uri, '/'));

    // Remove o prefixo 'api' da URI
    array_shift($uri);

    // Rota base
    $controller = isset($uri[0]) ? $uri[0] : '';
    $action = isset($uri[1]) ? $uri[1] : '';
    $id = isset($uri[2]) ? $uri[2] : null;

    // Instancia o controlador apropriado
    switch ($controller) {
        case 'usuarios':
            $controller = new UsuarioController();
            break;
        default:
            jsonResponse(['error' => 'Rota não encontrada'], 404);
            return;
    }

    // Executa a ação apropriada
    switch ($method) {
        case 'GET':
            if ($action === 'tipo' && isset($uri[2])) {
                $controller->getByTipo($uri[2]);
            } elseif ($id) {
                $controller->show($id);
            } else {
                $controller->index();
            }
            break;

        case 'POST':
            if ($action === 'login') {
                $controller->login();
            } else {
                $controller->store();
            }
            break;

        case 'PUT':
            if (!$id) {
                jsonResponse(['error' => 'ID não fornecido'], 400);
            }
            $controller->update($id);
            break;

        case 'DELETE':
            if (!$id) {
                jsonResponse(['error' => 'ID não fornecido'], 400);
            }
            $controller->delete($id);
            break;

        default:
            jsonResponse(['error' => 'Método não permitido'], 405);
            break;
    }
}


processRequest(); 