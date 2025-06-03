<?php

namespace backend\api\services;

use backend\api\models\Usuario;
use backend\api\models\RecuperarSenha;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class RecuperarSenhaService {
    private $usuarioModel;
    private $recuperarSenhaModel;
    
    public function __construct() {
        $this->usuarioModel = new Usuario();
        $this->recuperarSenhaModel = new RecuperarSenha();
    }
    
    public function generateToken($length = 25) {
        return bin2hex(random_bytes($length));
    }
    
    public function solicitarRecuperacao($email) {
        // Validar email
        $email = filter_var($email, FILTER_VALIDATE_EMAIL);
        if (!$email) {
            throw new \Exception('Email inválido');
        }
        
        // Verificar se usuário existe
        $usuario = $this->usuarioModel->buscarPorEmail($email);
        if (!$usuario) {
            throw new \Exception('Email não encontrado');
        }
        
        // Gerar token
        $token = $this->generateToken();
        
        // Salvar token
        if (!$this->recuperarSenhaModel->criarToken($usuario['id'], $token)) {
            throw new \Exception('Erro ao gerar token de recuperação');
        }
        
        // Enviar email
        $this->enviarEmailRecuperacao($email, $token);
        
        return [
            'success' => true,
            'message' => 'Email de recuperação enviado com sucesso'
        ];
    }
    
    public function validarToken($token) {
        if (empty($token)) {
            throw new \Exception('Token é obrigatório');
        }
        
        $recovery = $this->recuperarSenhaModel->validarToken($token);
        if (!$recovery) {
            throw new \Exception('Token inválido ou expirado');
        }
        
        return $recovery;
    }
    
    public function alterarSenha($token, $nova_senha, $confirmar_senha) {
        // Validações
        if (strlen($nova_senha) < 6) {
            throw new \Exception('A senha deve ter pelo menos 6 caracteres');
        }
        
        if ($nova_senha !== $confirmar_senha) {
            throw new \Exception('As senhas não coincidem');
        }
        
        // Validar token
        $recovery = $this->validarToken($token);
        
        // Atualizar senha
        $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
        if (!$this->usuarioModel->atualizarSenha($recovery['usuario_id'], $senha_hash)) {
            throw new \Exception('Erro ao atualizar senha');
        }
        
        // Remover token
        $this->recuperarSenhaModel->removerToken($token);
        
        return [
            'success' => true,
            'message' => 'Senha alterada com sucesso'
        ];
    }
    
    private function enviarEmailRecuperacao($email, $token) {
        $recoveryLink = "http://" . $_SERVER['HTTP_HOST'] . "/Gerenciamento-de-ACC/frontend/pages/alterar_senha.php?token=" . $token;
        
        $mail = new PHPMailer(true);
        try {
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
            $mail->Subject = 'Recuperação de Senha - SACC UFOPA';
            $mail->Body = "
                <h2>Recuperação de Senha</h2>
                <p>Olá,</p>
                <p>Clique no link abaixo para recuperar sua senha:</p>
                <p><a href='$recoveryLink' style='color: #0969DA;'>$recoveryLink</a></p>
                <p>Este link expira em 20 minutos.</p>
                <p>Se você não solicitou a recuperação, ignore este email.</p>
            ";

            $mail->send();
        } catch (Exception $e) {
            throw new \Exception('Erro ao enviar email: ' . $e->getMessage());
        }
    }
}
?>