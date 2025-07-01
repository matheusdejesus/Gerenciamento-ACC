<?php
// Configurações da API
define('API_VERSION', '1.0.0');
define('JWT_SECRET', 'sua_chave_secreta_aqui'); // Altere para uma chave segura em produção
//define('JWT_SECRET', getenv('JWT_SECRET'));
define('JWT_EXPIRATION', 3600); // 1 hora em segundos

// Configurações do Banco de Dados
define('DB_HOST', 'localhost');
define('DB_NAME', 'acc');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8');

// Configurações de CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Configurações de Timezone
date_default_timezone_set('America/Sao_Paulo');

// Configurações de Erro
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Função para retornar resposta JSON
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
?>