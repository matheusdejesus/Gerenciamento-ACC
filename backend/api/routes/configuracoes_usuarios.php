<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost');
header('Access-Control-Allow-Methods: GET, POST, PUT');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

// Buffer de saída para capturar erros
ob_start();

require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../controllers/UsuarioController.php';

use backend\api\middleware\AuthMiddleware;

function enviarErro($mensagem, $codigo = 500) {
    // Limpar buffer
    ob_end_clean();
    http_response_code($codigo);
    echo json_encode([
        'success' => false,
        'error' => $mensagem
    ]);
    exit;
}

try {
    error_log("=== INICIO configuracoes_usuarios.php ===");
    error_log("Method: " . $_SERVER['REQUEST_METHOD']);
    
    // Validar token JWT
    $usuario = AuthMiddleware::validateToken();
    
    if (!$usuario) {
        enviarErro('Token inválido', 401);
    }
    
    error_log("Usuário autenticado: " . json_encode($usuario));
    
    $controller = new UsuarioController();
    
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            error_log("Executando buscarDadosConfiguracao...");
            // Limpar qualquer saída anterior
            ob_clean();
            $controller->buscarDadosConfiguracao($usuario['id'], $usuario['tipo']);
            break;
            
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (isset($data['acao']) && $data['acao'] === 'alterar_senha') {
                ob_clean();
                $controller->alterarSenha($usuario['id'], $data['senha_atual'], $data['nova_senha']);
            } else {
                ob_clean();
                $controller->atualizarDadosPessoais($usuario['id'], $data);
            }
            break;
            
        default:
            enviarErro('Método não permitido', 405);
            break;
    }
    
} catch (Exception $e) {
    error_log("Erro em configuracoes_usuarios: " . $e->getMessage());
    enviarErro('Erro interno do servidor: ' . $e->getMessage());
}

// Finalizar buffer se chegou até aqui
if (ob_get_length()) {
    ob_end_flush();
}
?>