<?php
namespace backend\api\controllers;

require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/Cadastro.php';
require_once __DIR__ . '/LogAcoesController.php';

if (file_exists(__DIR__ . '/../../../frontend/pages/vendor/autoload.php')) {
    require_once __DIR__ . '/../../../frontend/pages/vendor/autoload.php';
} elseif (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
    require_once __DIR__ . '/../../vendor/autoload.php';
}

use backend\api\models\Cadastro;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use backend\api\controllers\LogAcoesController;

class CadastroController extends Controller {
    
    public function register() {
        try {
            // Obter dados da requisição
            $data = $this->getRequestData();
            
            // Validar dados básicos
            $validacao = $this->validarDados($data);
            if (!$validacao['valid']) {
                $this->sendJsonResponse(['error' => $validacao['message']], 400);
                return;
            }
            
            // Verificar se email já existe
            if (Cadastro::emailExists($data['email'])) {
                $this->sendJsonResponse(['error' => 'Email já cadastrado'], 400);
                return;
            }
            
            $codigo = $this->gerarCodigo();
            
            // Armazenar dados temporários na sessão
            session_start();
            $_SESSION['cadastro_temp'] = [
                'dados' => $data,
                'codigo' => $codigo,
                'expiracao' => time() + 600 // 10 minutos
            ];
            
            // Enviar email de confirmação
            $emailEnviado = $this->enviarEmailConfirmacao($data['email'], $data['nome'], $codigo);
            
            $this->sendJsonResponse([
                'success' => true,
                'message' => 'Código enviado para o email',
                'email' => $data['email'],
                'email_enviado' => $emailEnviado
            ]);
            
        } catch (Exception $e) {
            error_log("Erro em CadastroController::register: " . $e->getMessage());
            $this->sendJsonResponse(['error' => 'Erro interno do servidor'], 500);
        }
    }
    
    public function confirmarCodigo() {
        try {
            session_start();
            $data = $this->getRequestData();
            
            // Verificar se existe sessão de cadastro
            if (!isset($_SESSION['cadastro_temp'])) {
                $this->sendJsonResponse(['error' => 'Sessão expirada'], 400);
                return;
            }
            
            $cadastroTemp = $_SESSION['cadastro_temp'];
            
            // Verificar expiração
            if ($cadastroTemp['expiracao'] < time()) {
                unset($_SESSION['cadastro_temp']);
                $this->sendJsonResponse(['error' => 'Código expirado'], 400);
                return;
            }
            
            // Verificar código
            if ($cadastroTemp['codigo'] !== $data['codigo']) {
                $this->sendJsonResponse(['error' => 'Código incorreto'], 400);
                return;
            }
            
            $usuarioCriado = Cadastro::create($cadastroTemp['dados']);
            
            if ($usuarioCriado && isset($usuarioCriado['usuario_id'])) {
                // Registrar novo cadastro
                LogAcoesController::registrar(
                    $usuarioCriado['usuario_id'],
                    'CADASTRO_USUARIO',
                    "Novo usuário cadastrado: {$cadastroTemp['dados']['nome']} ({$cadastroTemp['dados']['tipo']})"
                );
                
                unset($_SESSION['cadastro_temp']);

                $_SESSION['usuario'] = [
                    'id' => $usuarioCriado['usuario_id'],
                    'nome' => $cadastroTemp['dados']['nome'],
                    'email' => $cadastroTemp['dados']['email'],
                    'tipo' => $cadastroTemp['dados']['tipo']
                ];

                $this->sendJsonResponse([
                    'success' => true,
                    'message' => 'Cadastro realizado com sucesso',
                    'usuario' => $_SESSION['usuario'],
                    'api_key' => $usuarioCriado['api_key']
                ]);
            } else {
                $this->sendJsonResponse(['error' => 'Erro ao criar usuário'], 500);
            }
            
        } catch (Exception $e) {
            error_log("Erro em CadastroController::confirmarCodigo: " . $e->getMessage());
            $this->sendJsonResponse(['error' => 'Erro interno do servidor'], 500);
        }
    }
    
    private function validarDados($data) {
        $required = ['nome', 'email', 'senha', 'conf_senha', 'tipo'];
        
        // Verificar campos obrigatórios
        foreach ($required as $field) {
            if (!isset($data[$field]) || empty(trim($data[$field]))) {
                return ['valid' => false, 'message' => "Campo {$field} é obrigatório"];
            }
        }
        
        // Validar email
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ['valid' => false, 'message' => 'Formato de email inválido'];
        }
        
        // Validar senhas
        if ($data['senha'] !== $data['conf_senha']) {
            return ['valid' => false, 'message' => 'As senhas não coincidem'];
        }
        
        // Validar força da senha
        if (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{8,}$/', $data['senha'])) {
            return ['valid' => false, 'message' => 'Senha fraca (mínimo 8 chars, 1 upper, 1 lower, 1 dígito e 1 símbolo)'];
        }
        
        // Validar tipo de usuário
        if (!in_array($data['tipo'], ['aluno', 'coordenador', 'orientador', 'admin'])) {
            return ['valid' => false, 'message' => 'Tipo de usuário inválido'];
        }
        
        // Validações específicas por tipo
        if ($data['tipo'] === 'aluno') {
            if (empty($data['matricula']) || empty($data['curso_id'])) {
                return ['valid' => false, 'message' => 'Matrícula e curso são obrigatórios para alunos'];
            }
        } elseif ($data['tipo'] === 'coordenador') {
            if (empty($data['siape']) || empty($data['curso_id'])) {
                return ['valid' => false, 'message' => 'SIAPE e curso são obrigatórios para coordenadores'];
            }
        } elseif ($data['tipo'] === 'orientador') {
            if (empty($data['siape'])) {
                return ['valid' => false, 'message' => 'SIAPE é obrigatório para orientadores'];
            }
        }
        
        return ['valid' => true];
    }
    
    private function gerarCodigo() {
        return sprintf("%06d", rand(0, 999999));
    }
    
    private function enviarEmailConfirmacao($email, $nome, $codigo) {
        if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            return false;
        }
        
        try {
            $mail = new PHPMailer(true);
            
            // Configurações SMTP
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'sistemaacc2025@gmail.com';
            $mail->Password = 'ehgg wzxq bsxt blab';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            $mail->CharSet = 'UTF-8';
            
            $mail->setFrom('sistemaacc2025@gmail.com', 'SACC UFOPA');
            $mail->addAddress($email);
            
            $mail->isHTML(true);
            $mail->Subject = 'Código de Verificação - SACC UFOPA';
            $mail->Body = "
                <h2>Confirmação de Cadastro - SACC UFOPA</h2>
                <p>Olá <strong>{$nome}</strong>,</p>
                <p>Seu código de verificação para completar o cadastro é:</p>
                <h1 style='color: #0969DA; font-size: 32px; text-align: center; letter-spacing: 5px;'>{$codigo}</h1>
                <p>Este código é válido por 10 minutos.</p>
                <p>Se você não solicitou este cadastro, ignore este email.</p>
                <hr>
                <p><small>Sistema de Acompanhamento e Controle de ACC - UFOPA</small></p>
            ";
            
            $mail->send();
            return true;
            
        } catch (Exception $e) {
            error_log("Erro ao enviar email: " . $e->getMessage());
            return false;
        }
    }
}
?>