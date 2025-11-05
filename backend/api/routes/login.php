<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Log de debug - início do script
error_log("[DEBUG] login.php - Início do script. Método: " . $_SERVER['REQUEST_METHOD']);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

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
    error_log("[DEBUG] login.php - Verificando método HTTP");
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        error_log("[DEBUG] login.php - Método não permitido: " . $_SERVER['REQUEST_METHOD']);
        enviarErro('Método não permitido', 405);
    }
    
    error_log("[DEBUG] login.php - Criando controller");
    $controller = new UsuarioController();
    
    error_log("[DEBUG] login.php - Chamando método login do controller");
    ob_start();
    $controller->login();
    $output = ob_get_clean();
    
    error_log("[DEBUG] login.php - Output do controller: " . $output);
    
    $jsonData = json_decode($output, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("[ERROR] JSON inválido em login: " . $output);
        error_log("[ERROR] JSON error: " . json_last_error_msg());
        enviarErro('Resposta inválida do servidor');
    }
    
    error_log("[DEBUG] login.php - Enviando resposta JSON válida");
    echo $output;
    
} catch (Exception $e) {
    error_log("[ERROR] Erro na rota login: " . $e->getMessage());
    error_log("[ERROR] Stack trace: " . $e->getTraceAsString());
    enviarErro('Erro interno do servidor: ' . $e->getMessage());
}

error_log("[DEBUG] login.php - Fim do script");
?>