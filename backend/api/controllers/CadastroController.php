<?php
namespace backend\api\controllers;

require_once __DIR__ . '/../models/Cadastro.php';
require_once __DIR__ . '/../../../frontend/pages/vendor/autoload.php';

use backend\api\models\Cadastro;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class CadastroController extends Controller {
    
    public function register() {
        try {
            // Obter dados JSON
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            
            // Validar se dados foram recebidos
            if (json_last_error() !== JSON_ERROR_NONE) {
                jsonResponse(['success' => false, 'error' => 'Dados JSON inválidos'], 400);
                return;
            }
            
            // Validar dados obrigatórios
            if (empty($data['nome']) || empty($data['email']) || empty($data['senha'])) {
                jsonResponse(['success' => false, 'error' => 'Dados obrigatórios não preenchidos'], 400);
                return;
            }
            
            // Verificar se email já existe
            if (Cadastro::emailExists($data['email'])) {
                jsonResponse(['success' => false, 'error' => 'Email já cadastrado'], 400);
                return;
            }
            
            // Gerar código de confirmação
            $codigo = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
            
            // Armazenar dados temporários na sessão
            session_start();
            $_SESSION['cadastro_pendente'] = [
                'dados' => $data,
                'codigo' => $codigo,
                'expiracao' => time() + 600
            ];
            
            // Enviar email
            if ($this->enviarEmailConfirmacao($data['email'], $codigo)) {
                jsonResponse([
                    'success' => true,
                    'message' => 'Código enviado para o email',
                    'codigo' => $codigo,
                    'email' => $data['email']
                ]);
            } else {
                jsonResponse(['success' => false, 'error' => 'Erro ao enviar email de confirmação'], 500);
            }
            
        } catch (Exception $e) {
            jsonResponse(['success' => false, 'error' => 'Erro interno do servidor'], 500);
        }
    }
    
    private function enviarEmailConfirmacao($email, $codigo) {
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
            $mail->Subject = 'Código de Confirmação - SACC UFOPA';
            $mail->Body = "
                <h2>Confirmação de Cadastro</h2>
                <p>Seu código de confirmação é:</p>
                <h1 style='color: #0969DA; font-size: 32px; text-align: center;'>$codigo</h1>
                <p>Este código expira em 10 minutos.</p>
                <p>Se você não solicitou este cadastro, ignore este email.</p>
            ";
            
            $mail->send();
            return true;
            
        } catch (Exception $e) {
            return false;
        }
    }
}
?>