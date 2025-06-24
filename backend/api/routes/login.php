<?php

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: http://localhost');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../services/JWTService.php';

use backend\api\config\Database;
use backend\api\services\JWTService;

function sendJsonResponse($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function registrarTentativaLogin($email, $sucesso, $db) {
    try {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $stmt = $db->prepare("INSERT INTO TentativasLogin (email, ip_address, sucesso) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $email, $ip, $sucesso);
        $stmt->execute();
    } catch (Exception $e) {
        error_log("Erro ao registrar tentativa de login: " . $e->getMessage());
    }
}

function verificarBloqueio($email, $db) {
    try {
        // Verificar tentativas dos últimos 5 minutos
        $stmt = $db->prepare("
            SELECT COUNT(*) as tentativas_falhadas 
            FROM TentativasLogin 
            WHERE email = ? 
            AND sucesso = 0 
            AND data_hora >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)
        ");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return $row['tentativas_falhadas'] >= 3;
        
    } catch (Exception $e) {
        error_log("Erro ao verificar bloqueio: " . $e->getMessage());
        return false;
    }
}

function obterTempoRestanteBloqueio($email, $db) {
    try {
        // Buscar a última tentativa falhada dentro dos últimos 5 minutos
        $stmt = $db->prepare("
            SELECT TIMESTAMPDIFF(SECOND, data_hora, DATE_ADD(data_hora, INTERVAL 5 MINUTE)) as segundos_restantes
            FROM TentativasLogin 
            WHERE email = ? 
            AND sucesso = 0 
            AND data_hora >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)
            ORDER BY data_hora DESC 
            LIMIT 1
        ");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return max(0, $row['segundos_restantes']);
        }
        
        return 0;
        
    } catch (Exception $e) {
        error_log("Erro ao obter tempo restante: " . $e->getMessage());
        return 0;
    }
}

function limparTentativasAntigas($email, $db) {
    try {
        // Limpar tentativas antigas (mais de 5 minutos) em caso de login bem-sucedido
        $stmt = $db->prepare("
            DELETE FROM TentativasLogin 
            WHERE email = ? 
            AND data_hora < DATE_SUB(NOW(), INTERVAL 5 MINUTE)
        ");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        
    } catch (Exception $e) {
        error_log("Erro ao limpar tentativas antigas: " . $e->getMessage());
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        $db = Database::getInstance()->getConnection();
        
        if (!$data || !isset($data['email']) || !isset($data['senha'])) {
            $email = isset($data['email']) ? trim($data['email']) : 'unknown';
            registrarTentativaLogin($email, 0, $db);
            sendJsonResponse(['error' => 'Email e senha são obrigatórios'], 400);
        }
        
        $email = trim($data['email']);
        $senha = $data['senha'];
        
        if (empty($email) || empty($senha)) {
            registrarTentativaLogin($email, 0, $db);
            sendJsonResponse(['error' => 'Email e senha não podem estar vazios'], 400);
        }
        
        // Verificar bloqueio
        if (verificarBloqueio($email, $db)) {
            $tempoRestante = obterTempoRestanteBloqueio($email, $db);
            $minutosRestantes = ceil($tempoRestante / 60);
            sendJsonResponse([
                'error' => "Muitas tentativas de login falhadas. Tente novamente em $minutosRestantes minuto(s).",
                'bloqueado' => true,
                'tempo_restante' => $tempoRestante
            ], 429);
        }
        
        // Buscar usuário
        $stmt = $db->prepare("SELECT id, nome, email, senha, tipo FROM Usuario WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            registrarTentativaLogin($email, 0, $db);
            sendJsonResponse(['error' => 'Email ou senha inválidos'], 401);
        }
        
        $usuario = $result->fetch_assoc();
        
        // Verificar senha
        if (!password_verify($senha, $usuario['senha'])) {
            registrarTentativaLogin($email, 0, $db);
            sendJsonResponse(['error' => 'Email ou senha inválidos'], 401);
        }
        
        // Login bem-sucedido
        registrarTentativaLogin($email, 1, $db);
        limparTentativasAntigas($email, $db);
        
        // Gerar JWT Token
        $tokenPayload = [
            'id' => $usuario['id'],
            'email' => $usuario['email'],
            'nome' => $usuario['nome'],
            'tipo' => $usuario['tipo']
        ];
        
        $jwt = JWTService::encode($tokenPayload);
        
        // Remover senha do retorno
        unset($usuario['senha']);
        
        // Retornar sucesso com JWT
        sendJsonResponse([
            'success' => true,
            'message' => 'Login realizado com sucesso',
            'token' => $jwt,
            'usuario' => $usuario
        ]);
        
    } catch (Exception $e) {
        if (isset($email)) {
            registrarTentativaLogin($email, 0, $db);
        }
        sendJsonResponse(['error' => 'Erro interno do servidor'], 500);
    }
} else {
    sendJsonResponse(['error' => 'Método não permitido'], 405);
}
?>