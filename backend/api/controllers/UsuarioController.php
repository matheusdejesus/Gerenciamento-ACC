<?php
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../services/JWTService.php';
require_once __DIR__ . '/LogAcoesController.php';

use backend\api\config\Database;
use backend\api\services\JWTService;
use backend\api\controllers\LogAcoesController;

class UsuarioController {
    
    protected function getRequestData() {
        $input = file_get_contents('php://input');
        return json_decode($input, true);
    }
    
    protected function sendJsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
    }
    
    public function login() {
        try {
            $data = $this->getRequestData();
            
            // Validação básica de entrada
            if (!$data || !isset($data['email']) || !isset($data['senha'])) {
                $this->sendJsonResponse(['error' => 'Email e senha são obrigatórios'], 400);
                return;
            }
            
            $resultado = Usuario::autenticar($data['email'], $data['senha']);
            
            if (!$resultado['success']) {
                // Registrar tentativa de login falhada
                LogAcoesController::registrar(
                    null,
                    'LOGIN_FALHA',
                    "Tentativa de login falhada para email: {$data['email']}"
                );
                
                $statusCode = $resultado['status_code'] ?? 401;
                $response = ['error' => $resultado['error']];
                
                // Adicionar informações extras se for bloqueio
                if (isset($resultado['bloqueado'])) {
                    $response['bloqueado'] = $resultado['bloqueado'];
                    $response['tempo_restante'] = $resultado['tempo_restante'];
                }
                
                $this->sendJsonResponse($response, $statusCode);
                return;
            }
            
            // Gerar JWT Token
            $tokenPayload = [
                'id' => $resultado['usuario']['id'],
                'email' => $resultado['usuario']['email'],
                'nome' => $resultado['usuario']['nome'],
                'tipo' => $resultado['usuario']['tipo']
            ];
            
            // Adicionar matrícula se for aluno
            if ($resultado['usuario']['tipo'] === 'aluno' && isset($resultado['usuario']['matricula'])) {
                $tokenPayload['matricula'] = $resultado['usuario']['matricula'];
            }
            
            $jwt = JWTService::encode($tokenPayload);
            
            // Buscar API Key do usuário
            $apiKey = null;
            try {
                $db = \backend\api\config\Database::getInstance()->getConnection();
                $stmt = $db->prepare("SELECT api_key FROM ApiKeys WHERE usuario_id = ? AND ativa = 1 LIMIT 1");
                $stmt->bind_param("i", $resultado['usuario']['id']);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($row = $result->fetch_assoc()) {
                    $apiKey = $row['api_key'];
                }
            } catch (\Exception $e) {
                error_log("Erro ao buscar API Key no login: " . $e->getMessage());
            }
            
            // Registrar login bem-sucedido
            LogAcoesController::registrar(
                $resultado['usuario']['id'],
                'LOGIN_SUCESSO',
                "Usuário {$resultado['usuario']['nome']} fez login com sucesso"
            );
            
            // Resposta de sucesso
            $this->sendJsonResponse([
                'success' => true,
                'message' => 'Login realizado com sucesso',
                'token' => $jwt,
                'usuario' => $resultado['usuario'],
                'api_key' => $apiKey
            ]);
            
        } catch (Exception $e) {
            error_log("Erro em UsuarioController::login: " . $e->getMessage());
            $this->sendJsonResponse(['error' => 'Erro interno do servidor'], 500);
        }
    }
    
    // Buscar dados de configuração do usuário

    public function buscarDadosConfiguracao($userId, $userType) {
        try {
            // Delegar para o model
            $resultado = Usuario::buscarDadosConfiguracao($userId, $userType);
            
            $statusCode = $resultado['status_code'] ?? 200;
            
            if ($resultado['success']) {
                $response = [
                    'success' => true,
                    'data' => $resultado['data']
                ];
            } else {
                $response = [
                    'success' => false,
                    'error' => $resultado['error']
                ];
            }
            
            $this->sendJsonResponse($response, $statusCode);
            
        } catch (Exception $e) {
            error_log("Erro em UsuarioController::buscarDadosConfiguracao: " . $e->getMessage());
            $this->sendJsonResponse([
                'success' => false,
                'error' => 'Erro interno do servidor'
            ], 500);
        }
    }
    // Atualizar dados pessoais do usuário

    public function atualizarDadosPessoais($userId, $dados) {
        try {
            // Delegar para o model
            $resultado = Usuario::atualizarDadosPessoaisCompleto($userId, $dados);
            
            $statusCode = $resultado['status_code'] ?? 200;
            
            if ($resultado['success']) {
                // Registrar atualização de dados
                LogAcoesController::registrar(
                    $userId,
                    'ATUALIZAR_DADOS',
                    "Dados pessoais atualizados"
                );
                
                $response = [
                    'success' => true,
                    'message' => $resultado['message']
                ];
            } else {
                $response = [
                    'success' => false,
                    'error' => $resultado['error']
                ];
            }
            
            $this->sendJsonResponse($response, $statusCode);
            
        } catch (Exception $e) {
            error_log("Erro em UsuarioController::atualizarDadosPessoais: " . $e->getMessage());
            $this->sendJsonResponse([
                'success' => false,
                'error' => 'Erro interno do servidor'
            ], 500);
        }
    }

    // Alterar senha do usuário
    public function alterarSenha($userId, $senhaAtual, $novaSenha) {
        try {
            // Delegar para o model
            $resultado = Usuario::alterarSenhaCompleta($userId, $senhaAtual, $novaSenha);
            
            $statusCode = $resultado['status_code'] ?? 200;
            
            if ($resultado['success']) {
                // Registrar alteração de senha
                LogAcoesController::registrar(
                    $userId,
                    'ALTERAR_SENHA',
                    "Senha alterada com sucesso"
                );
                
                $response = [
                    'success' => true,
                    'message' => $resultado['message']
                ];
            } else {
                $response = [
                    'success' => false,
                    'error' => $resultado['error']
                ];
            }
            
            $this->sendJsonResponse($response, $statusCode);
            
        } catch (Exception $e) {
            error_log("Erro em UsuarioController::alterarSenha: " . $e->getMessage());
            $this->sendJsonResponse([
                'success' => false,
                'error' => 'Erro interno do servidor'
            ], 500);
        }
    }

    // Listar todos os usuários
    public function listarTodos() {
        try {
            $usuarios = Usuario::listarTodos();
            $this->sendJsonResponse(['success' => true, 'data' => $usuarios]);
        } catch (Exception $e) {
            $this->sendJsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
?>