<?php
// Configurações da API
define('API_VERSION', '1.0.0');
define('JWT_SECRET', 'sua_chave_secreta_aqui'); // Altere para uma chave segura em produção
define('JWT_EXPIRATION', 3600); // 1 hora em segundos

// Configurações do Banco de Dados
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'acc');

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
function jsonResponse($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// Função para validar token JWT
function validateToken() {
    $headers = getallheaders();
    if (!isset($headers['Authorization'])) {
        jsonResponse(['error' => 'Token não fornecido'], 401);
    }

    $token = str_replace('Bearer ', '', $headers['Authorization']);
    try {
        // Aqui você implementará a validação do token JWT
        return true;
    } catch (Exception $e) {
        jsonResponse(['error' => 'Token inválido'], 401);
    }
} 