<?php
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../config/Database.php';

use backend\api\config\Database;

class UsuarioController {
    
    protected function getRequestData() {
        $input = file_get_contents('php://input');
        return json_decode($input, true);
    }
    
    protected function validateRequired($data, $fields) {
        foreach ($fields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                return false;
            }
        }
        return true;
    }
    
    public function login() {
        try {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            
            // Conectar ao banco
            $database = new Database();
            $db = $database->getConnection();
            
            if (!$db) {
                $this->sendJsonResponse(['error' => 'Erro de conexão com o banco de dados'], 500);
            }
            
            if (!$data || !isset($data['email']) || !isset($data['senha'])) {
                Usuario::registrarTentativaLogin(isset($data['email']) ? $data['email'] : 'unknown', 0, $db);
                $this->sendJsonResponse(['error' => 'Email e senha são obrigatórios'], 400);
            }
            
            $email = trim($data['email']);
            $senha = $data['senha'];
            
            if (empty($email) || empty($senha)) {
                Usuario::registrarTentativaLogin($email, 0, $db);
                $this->sendJsonResponse(['error' => 'Email e senha não podem estar vazios'], 400);
            }
            
            // Verificar se o usuário está bloqueado
            if (Usuario::verificarBloqueio($email)) {
                $tempoRestante = Usuario::obterTempoRestanteBloqueio($email);
                $minutosRestantes = ceil($tempoRestante / 60);
                $this->sendJsonResponse([
                    'error' => "Muitas tentativas de login falhadas. Tente novamente em $minutosRestantes minuto(s).",
                    'bloqueado' => true,
                    'tempo_restante' => $tempoRestante
                ], 429);
            }
            
            // Buscar usuário usando o model
            $usuario = Usuario::findByEmailForLogin($email);
            
            if (!$usuario) {
                Usuario::registrarTentativaLogin($email, 0, $db);
                $this->sendJsonResponse(['error' => 'Email ou senha inválidos'], 401);
            }
            
            // Verificar senha
            if (!password_verify($senha, $usuario['senha'])) {
                Usuario::registrarTentativaLogin($email, 0, $db);
                $this->sendJsonResponse(['error' => 'Email ou senha inválidos'], 401);
            }
            
            // Login bem-sucedido - registrar sucesso e limpar tentativas antigas
            Usuario::registrarTentativaLogin($email, 1, $db);
            Usuario::limparTentativasAntigas($email);
            
            // Remover senha do retorno
            unset($usuario['senha']);
            
            // Retornar dados do usuário
            $this->sendJsonResponse([
                'success' => true,
                'message' => 'Login realizado com sucesso',
                'usuario' => $usuario
            ]);
            
        } catch (Exception $e) {
            if (isset($email)) {
                Usuario::registrarTentativaLogin($email, 0, $db);
            }
            $this->sendJsonResponse(['error' => 'Erro interno do servidor'], 500);
        }
    }

    private function sendJsonResponse($data, $status = 200) {
        http_response_code($status);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
?>