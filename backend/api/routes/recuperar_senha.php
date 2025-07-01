<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/Database.php';

// Verificar se o autoload do PHPMailer existe
if (file_exists(__DIR__ . '/../../../frontend/pages/vendor/autoload.php')) {
    require_once __DIR__ . '/../../../frontend/pages/vendor/autoload.php';
}

use backend\api\config\Database;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function generateToken($length = 25) {
    return bin2hex(random_bytes($length));
}

function buscarUsuarioPorEmail($email, $db) {
    $stmt = $db->prepare("SELECT id, nome FROM Usuario WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function criarTokenRecuperacao($usuario_id, $token, $db) {
    // Remover tokens antigos do usuário
    $stmt = $db->prepare("DELETE FROM recuperarsenha WHERE usuario_id = ?");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    
    // Salvar novo token
    $stmt = $db->prepare("INSERT INTO recuperarsenha (usuario_id, token, criacao) VALUES (?, ?, NOW())");
    $stmt->bind_param("is", $usuario_id, $token);
    return $stmt->execute();
}

function enviarEmailRecuperacao($email, $token, $nome) {
    $recoveryLink = "http://" . $_SERVER['HTTP_HOST'] . "/Gerenciamento-ACC/frontend/pages/alterar_senha.php?token=" . $token;
    
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
            <p>Olá <strong>$nome</strong>,</p>
            <p>Você solicitou a recuperação de sua senha no sistema SACC UFOPA.</p>
            <p>Clique no link abaixo para criar uma nova senha:</p>
            <p><a href='$recoveryLink' style='color: #0969DA; font-weight: bold;'>$recoveryLink</a></p>
            <p><strong>Este link expira em 20 minutos.</strong></p>
            <p>Se você não solicitou a recuperação, ignore este email.</p>
            <hr>
            <p><small>Sistema de Acompanhamento e Controle de ACC - UFOPA</small></p>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        throw new Exception('Erro ao enviar email: ' . $e->getMessage());
    }
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Método não permitido']);
        exit;
    }

    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!$data || !isset($data['email'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Email é obrigatório']);
        exit;
    }

    $email = filter_var($data['email'], FILTER_VALIDATE_EMAIL);
    if (!$email) {
        http_response_code(400);
        echo json_encode(['error' => 'Email inválido']);
        exit;
    }

    $db = Database::getInstance()->getConnection();
    
    // Verificar se usuário existe
    $usuario = buscarUsuarioPorEmail($email, $db);
    if (!$usuario) {
        http_response_code(404);
        echo json_encode(['error' => 'Email não encontrado']);
        exit;
    }
    
    // Gerar token
    $token = generateToken();
    
    // Salvar token
    if (!criarTokenRecuperacao($usuario['id'], $token, $db)) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao gerar token de recuperação']);
        exit;
    }
    
    // Enviar email
    enviarEmailRecuperacao($email, $token, $usuario['nome']);
    
    echo json_encode([
        'success' => true,
        'message' => 'Email de recuperação enviado com sucesso'
    ]);

} catch (Exception $e) {
    $statusCode = 500;
    if (strpos($e->getMessage(), 'Email não encontrado') !== false) {
        $statusCode = 404;
    } elseif (strpos($e->getMessage(), 'Email inválido') !== false) {
        $statusCode = 400;
    }
    
    http_response_code($statusCode);
    echo json_encode(['error' => $e->getMessage()]);
}
?>