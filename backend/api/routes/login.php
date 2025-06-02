<?php
// Configurar headers CORS e JSON
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config/Database.php';

use backend\api\config\Database;

function sendJsonResponse($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function registrarTentativaLogin($email, $sucesso, $db) {
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    try {
        $stmt = $db->prepare("INSERT INTO TentativasLogin (email, ip_address, sucesso) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $email, $ip_address, $sucesso);
        $stmt->execute();
    } catch (Exception $e) {
        error_log("Erro ao registrar tentativa de login: " . $e->getMessage());
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        $db = Database::getInstance()->getConnection();
        
        if (!$data || !isset($data['email']) || !isset($data['senha'])) {
            // Registrar tentativa de erro - dados obrigatórios não preenchidos
            $email = isset($data['email']) ? trim($data['email']) : 'unknown';
            registrarTentativaLogin($email, 0, $db);
            sendJsonResponse(['error' => 'Email e senha são obrigatórios'], 400);
        }
        
        $email = trim($data['email']);
        $senha = $data['senha'];
        
        // Validar campos vazios
        if (empty($email) || empty($senha)) {
            registrarTentativaLogin($email, 0, $db);
            sendJsonResponse(['error' => 'Email e senha não podem estar vazios'], 400);
        }
        
        // Buscar usuário
        $stmt = $db->prepare("SELECT id, nome, email, senha, tipo FROM Usuario WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            // Registrar tentativa de erro - usuário não encontrado
            registrarTentativaLogin($email, 0, $db);
            sendJsonResponse(['error' => 'Email ou senha inválidos'], 401);
        }
        
        $usuario = $result->fetch_assoc();
        
        // Verificar senha
        if (!password_verify($senha, $usuario['senha'])) {
            // Registrar tentativa de erro - senha incorreta
            registrarTentativaLogin($email, 0, $db);
            sendJsonResponse(['error' => 'Email ou senha inválidos'], 401);
        }
        
        // Login bem-sucedido
        registrarTentativaLogin($email, 1, $db);
        
        unset($usuario['senha']);
        
        // Retornar sucesso
        sendJsonResponse([
            'success' => true,
            'message' => 'Login realizado com sucesso',
            'usuario' => $usuario
        ]);
        
    } catch (Exception $e) {
        // Registrar tentativa de erro - erro interno
        if (isset($email)) {
            registrarTentativaLogin($email, 0, $db);
        }
        sendJsonResponse(['error' => 'Erro interno do servidor'], 500);
    }
} else {
    sendJsonResponse(['error' => 'Método não permitido'], 405);
}
?>