<?php
error_reporting(0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

ob_start();

require_once __DIR__ . '/../controllers/UsuarioController.php';

function enviarErro($mensagem, $codigo = 500) {
    ob_end_clean();
    http_response_code($codigo);
    echo json_encode([
        'success' => false,
        'error' => $mensagem
    ]);
    exit;
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        enviarErro('Método não permitido', 405);
    }
    
    $controller = new UsuarioController();
    
    ob_start();
    $controller->login();
    $output = ob_get_clean();
    
    $jsonData = json_decode($output, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("JSON inválido em login: " . $output);
        enviarErro('Resposta inválida do servidor');
    }
    
    echo $output;
    
} catch (Exception $e) {
    error_log("Erro na rota login: " . $e->getMessage());
    enviarErro('Erro interno do servidor: ' . $e->getMessage());
}
?>