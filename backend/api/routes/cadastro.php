<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Carregar configurações
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// Verificar se PHPMailer existe
if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
    require_once __DIR__ . '/../../vendor/autoload.php';
} elseif (file_exists(__DIR__ . '/../../../frontend/pages/vendor/autoload.php')) {
    require_once __DIR__ . '/../../../frontend/pages/vendor/autoload.php';
}

use backend\api\config\Database;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

try {
    // Verificar se é POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Método não permitido']);
        exit;
    }

    // Receber dados JSON
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!$data) {
        http_response_code(400);
        echo json_encode(['error' => 'Dados inválidos']);
        exit;
    }

    // Validar dados obrigatórios
    $required = ['nome', 'email', 'senha', 'tipo'];
    foreach ($required as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            http_response_code(400);
            echo json_encode(['error' => "Campo $field é obrigatório"]);
            exit;
        }
    }

    // Conectar ao banco
    $db = Database::getInstance()->getConnection();
    
    // Função para verificar se email existe em uma tabela
    function verificarEmailTabela($dbConnection, $tabela, $email) {
        $result = $dbConnection->query("DESCRIBE $tabela");
        $colunas = [];
        
        while ($row = $result->fetch_assoc()) {
            $colunas[] = $row['Field'];
        }
        
        // Procurar por colunas que possam conter email
        $colunasEmail = [];
        foreach ($colunas as $coluna) {
            if (stripos($coluna, 'email') !== false || $coluna === 'email') {
                $colunasEmail[] = $coluna;
            }
        }
        
        // Verificar cada coluna de email
        foreach ($colunasEmail as $colunaEmail) {
            $stmt = $dbConnection->prepare("SELECT * FROM $tabela WHERE $colunaEmail = ? LIMIT 1");
            if ($stmt) {
                $stmt->bind_param("s", $email);
                if ($stmt->execute()) {
                    $result = $stmt->get_result();
                    if ($result->num_rows > 0) {
                        $stmt->close();
                        return true;
                    }
                }
                $stmt->close();
            }
        }
        
        return false;
    }
    
    // Verificar se email já existe em qualquer tabela
    $emailExists = false;
    $tabelas = ['Aluno', 'Coordenador', 'Orientador'];
    
    foreach ($tabelas as $tabela) {
        // Verificar se a tabela existe
        $result = $db->query("SHOW TABLES LIKE '$tabela'");
        if ($result->num_rows > 0) {
            if (verificarEmailTabela($db, $tabela, $data['email'])) {
                $emailExists = true;
                break;
            }
        }
    }
    
    if ($emailExists) {
        http_response_code(400);
        echo json_encode(['error' => 'Email já cadastrado']);
        exit;
    }

    // Gerar código de verificação
    $codigo = sprintf("%06d", rand(0, 999999));

    // Tentar enviar email
    $emailEnviado = false;
    if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        try {
            $mail = new PHPMailer(true);
            
            // Configurações do servidor SMTP
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'sistemaacc2025@gmail.com';
            $mail->Password   = 'ehgg wzxq bsxt blab';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // Remetente e destinatário
            $mail->setFrom('sistemaacc2025@gmail.com', 'SACC UFOPA');
            $mail->addAddress($data['email']);

            // Conteúdo do e-mail
            $mail->isHTML(true);
            $mail->Subject = 'Código de Verificação - SACC UFOPA';
            $mail->Body    = "
            <h2>Confirmação de Cadastro - SACC UFOPA</h2>
            <p>Olá <strong>{$data['nome']}</strong>,</p>
            <p>Seu código de verificação para completar o cadastro é:</p>
            <h1 style='color: #0969DA; font-size: 32px; text-align: center; letter-spacing: 5px;'>$codigo</h1>
            <p>Este código é válido por 10 minutos.</p>
            <p>Se você não solicitou este cadastro, ignore este email.</p>
            <hr>
            <p><small>Sistema de Acompanhamento e Controle de ACC - UFOPA</small></p>
            ";

            $mail->send();
            $emailEnviado = true;
            
        } catch (Exception $e) {
            $emailEnviado = false;
        }
    }

    // Resposta
    echo json_encode([
        'success' => true,
        'message' => 'Cadastro iniciado com sucesso',
        'codigo' => $codigo,
        'email' => $data['email'],
        'email_enviado' => $emailEnviado
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno do servidor']);
}
?>